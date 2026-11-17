<?php
// calculos.php - Relatórios: vendas, lucro, desperdício, curva ABC e observações
session_start();
require_once '../conexao.php'; // fornece $pdo (PDO)
require_once '../estoque/helpers.php'; // converter_unidade()

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../pag_login/index.php");
    exit();
}

$id_gestor = (int)$_SESSION['usuario_id'];

// Confere se o SQL de alterações já foi rodado
$col = $pdo->query("SHOW COLUMNS FROM tb_desperdicio LIKE 'qtd_desperdicada'")->fetch();
if (!$col) {
    die("Falta rodar o arquivo sql/alteracoes_calculos.sql no phpMyAdmin (aba SQL).");
}

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

function h($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function moeda($v): string {
    $v = (float)$v;
    return ($v < 0 ? '-' : '') . 'R$ ' . number_format(abs($v), 2, ',', '.');
}
function plural(int $n, string $sing, string $plur): string { return $n . ' ' . ($n === 1 ? $sing : $plur); }
function pct($v): string { return number_format((float)$v, 1, ',', '.') . '%'; }
function qtd_fmt($v): string { return rtrim(rtrim(number_format((float)$v, 3, ',', '.'), '0'), ','); }
/** Corta um texto UTF-8 em N caracteres (sem depender da extensão mbstring). */
function cortar(string $s, int $n): string {
    return preg_match('/^.{0,' . $n . '}/us', $s, $m) ? $m[0] : substr($s, 0, $n);
}
function var_pct(float $atual, float $anterior): ?float {
    return $anterior > 0 ? (($atual - $anterior) / $anterior) * 100 : null;
}

$MESES = [1 => 'janeiro', 'fevereiro', 'março', 'abril', 'maio', 'junho', 'julho', 'agosto', 'setembro', 'outubro', 'novembro', 'dezembro'];
$DIAS_SEMANA = ['domingo', 'segunda-feira', 'terça-feira', 'quarta-feira', 'quinta-feira', 'sexta-feira', 'sábado'];

// ---------- Mês selecionado ----------
$mes = $_GET['mes'] ?? '';
if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $mes)) {
    $mes = date('Y-m');
}
$inicio = new DateTime($mes . '-01');
$fim    = (clone $inicio)->modify('last day of this month');
$anterior = (clone $inicio)->modify('-1 month');
$proximo  = (clone $inicio)->modify('+1 month');
$ini_sql = $inicio->format('Y-m-d') . ' 00:00:00';
$fim_sql = $fim->format('Y-m-d') . ' 23:59:59';
$n_dias  = (int)$fim->format('j');
$hoje    = new DateTime('today');

if ($inicio > $hoje) {
    $dias_considerados = 0; // mês futuro
} elseif ($hoje <= $fim) {
    $dias_considerados = (int)$hoje->format('j'); // mês atual: só até hoje
} else {
    $dias_considerados = $n_dias; // mês passado
}
$nome_mes = $MESES[(int)$inicio->format('n')];
$titulo_mes = ucfirst($nome_mes) . ' de ' . $inicio->format('Y');
$mes_frase = $nome_mes . ' de ' . $inicio->format('Y');

// ---------- Registrar desperdício ----------
$flash = $_SESSION['flash_calc'] ?? null;
unset($_SESSION['flash_calc']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'registrar_desperdicio') {
    $erro = null;
    if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) {
        $erro = 'Sessão inválida. Recarregue a página e tente de novo.';
    }

    $id_ing = intval($_POST['id_ingrediente'] ?? 0);
    $qtd    = floatval(str_replace(',', '.', $_POST['quantidade'] ?? '0'));
    $motivo = trim(cortar((string)($_POST['motivo'] ?? ''), 200));
    if ($motivo === '') $motivo = 'Não informado';

    if (!$erro && $qtd <= 0) {
        $erro = 'Informe uma quantidade maior que zero.';
    }

    if (!$erro) {
        try {
            $pdo->beginTransaction();
            $st = $pdo->prepare("SELECT nome_ingredientes FROM tb_estoque WHERE id_ingrediente = :i AND id_gestor = :g FOR UPDATE");
            $st->execute([':i' => $id_ing, ':g' => $id_gestor]);
            $ing = $st->fetch(PDO::FETCH_ASSOC);

            if (!$ing) {
                $pdo->rollBack();
                $erro = 'Ingrediente inválido.';
            } else {
                $pdo->prepare("INSERT INTO tb_desperdicio (id_ingrediente, qtd_desperdicada, motivo) VALUES (:i, :q, :m)")
                    ->execute([':i' => $id_ing, ':q' => $qtd, ':m' => $motivo]);
                // o que foi desperdiçado sai do estoque (sem deixar negativo)
                $pdo->prepare("UPDATE tb_estoque SET quantidade_atual = GREATEST(0, quantidade_atual - :q) WHERE id_ingrediente = :i AND id_gestor = :g")
                    ->execute([':q' => $qtd, ':i' => $id_ing, ':g' => $id_gestor]);
                $pdo->commit();
            }
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $erro = 'Não foi possível registrar o desperdício.';
        }
    }

    $_SESSION['flash_calc'] = $erro
        ? ['tipo' => 'erro', 'msg' => $erro]
        : ['tipo' => 'ok', 'msg' => 'Desperdício registrado e descontado do estoque.'];
    header("Location: calculos.php?mes=" . urlencode($mes) . "#desperdicio");
    exit();
}

// ---------- Resumo de um mês (usado para comparar com o anterior) ----------
function resumo_mes(PDO $pdo, int $g, string $ini, string $fim): array {
    $st = $pdo->prepare("SELECT COUNT(*) AS n, COALESCE(SUM(valor_total), 0) AS r
                         FROM tb_pedidos
                         WHERE id_gestor = :g AND status = 'fechado' AND data_fechamento BETWEEN :i AND :f");
    $st->execute([':g' => $g, ':i' => $ini, ':f' => $fim]);
    $r = $st->fetch(PDO::FETCH_ASSOC);
    return ['pedidos' => (int)$r['n'], 'receita' => (float)$r['r']];
}

$mes_ant = resumo_mes(
    $pdo, $id_gestor,
    $anterior->format('Y-m-d') . ' 00:00:00',
    (clone $anterior)->modify('last day of this month')->format('Y-m-d') . ' 23:59:59'
);

// ---------- Pedidos fechados por dia ----------
$st = $pdo->prepare("SELECT DATE(data_fechamento) AS dia, COUNT(*) AS pedidos, COALESCE(SUM(valor_total), 0) AS receita
                     FROM tb_pedidos
                     WHERE id_gestor = :g AND status = 'fechado' AND data_fechamento BETWEEN :i AND :f
                     GROUP BY DATE(data_fechamento)");
$st->execute([':g' => $id_gestor, ':i' => $ini_sql, ':f' => $fim_sql]);
$por_dia = [];
foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $r) {
    $por_dia[$r['dia']] = ['pedidos' => (int)$r['pedidos'], 'receita' => (float)$r['receita']];
}

// ---------- Movimento por hora (hora em que a comanda foi aberta) ----------
$st = $pdo->prepare("SELECT HOUR(data_abertura) AS hora, COUNT(*) AS n
                     FROM tb_pedidos
                     WHERE id_gestor = :g AND status = 'fechado' AND data_abertura IS NOT NULL
                       AND data_fechamento BETWEEN :i AND :f
                     GROUP BY HOUR(data_abertura)");
$st->execute([':g' => $id_gestor, ':i' => $ini_sql, ':f' => $fim_sql]);
$por_hora = array_fill(0, 24, 0);
foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $r) {
    $por_hora[(int)$r['hora']] = (int)$r['n'];
}

// ---------- Pratos cadastrados e custo de cada um (pela ficha técnica) ----------
$st = $pdo->prepare("SELECT id_prato, nome_prato, preco_venda FROM tb_pratos WHERE id_gestor = :g ORDER BY nome_prato ASC");
$st->execute([':g' => $id_gestor]);
$pratos = [];
foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $p) {
    $pratos[(int)$p['id_prato']] = ['nome' => $p['nome_prato'], 'preco' => (float)$p['preco_venda']];
}

$custo_prato = [];
$tem_ficha   = [];
$st = $pdo->prepare("SELECT f.id_prato, f.quantidade_necessaria, f.unidade, e.unidade_medida, e.custo_unitario
                     FROM tb_item_ficha_tecnica f
                     INNER JOIN tb_pratos p  ON p.id_prato = f.id_prato AND p.id_gestor = :g1
                     INNER JOIN tb_estoque e ON e.id_ingrediente = f.id_ingrediente AND e.id_gestor = :g2");
$st->execute([':g1' => $id_gestor, ':g2' => $id_gestor]);
foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $f) {
    $id = (int)$f['id_prato'];
    $qtd_no_estoque = converter_unidade((float)$f['quantidade_necessaria'], (string)$f['unidade'], (string)($f['unidade_medida'] ?: 'un'));
    $custo_prato[$id] = ($custo_prato[$id] ?? 0) + $qtd_no_estoque * (float)$f['custo_unitario'];
    $tem_ficha[$id] = true;
}

// ---------- Itens vendidos por dia e por prato ----------
$st = $pdo->prepare("SELECT DATE(p.data_fechamento) AS dia, i.id_prato,
                            SUM(i.quantidade_vendida) AS qtd,
                            SUM(i.quantidade_vendida * i.preco_unitario) AS receita
                     FROM tb_itens_pedido i
                     INNER JOIN tb_pedidos p ON p.id_pedido = i.id_pedido
                     WHERE p.id_gestor = :g AND p.status = 'fechado' AND p.data_fechamento BETWEEN :i AND :f
                     GROUP BY DATE(p.data_fechamento), i.id_prato");
$st->execute([':g' => $id_gestor, ':i' => $ini_sql, ':f' => $fim_sql]);
$vendas_dia   = [];
$venda_prato  = [];
$total_itens  = 0;
foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $v) {
    $idp = (int)$v['id_prato'];
    $vendas_dia[$v['dia']][$idp] = ['qtd' => (int)$v['qtd'], 'receita' => (float)$v['receita']];
    $venda_prato[$idp]['qtd']     = ($venda_prato[$idp]['qtd'] ?? 0) + (int)$v['qtd'];
    $venda_prato[$idp]['receita'] = ($venda_prato[$idp]['receita'] ?? 0) + (float)$v['receita'];
    $total_itens += (int)$v['qtd'];
}

// ---------- Desperdício do mês ----------
$st = $pdo->prepare("SELECT e.id_ingrediente, e.nome_ingredientes, e.unidade_medida, e.custo_unitario,
                            SUM(d.qtd_desperdicada) AS qtd
                     FROM tb_desperdicio d
                     INNER JOIN tb_estoque e ON e.id_ingrediente = d.id_ingrediente
                     WHERE e.id_gestor = :g AND d.data_registro BETWEEN :i AND :f
                     GROUP BY e.id_ingrediente, e.nome_ingredientes, e.unidade_medida, e.custo_unitario
                     ORDER BY SUM(d.qtd_desperdicada * e.custo_unitario) DESC");
$st->execute([':g' => $id_gestor, ':i' => $ini_sql, ':f' => $fim_sql]);
$desperdicio = [];
$total_desperdicio = 0.0;
foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $d) {
    $custo = (float)$d['qtd'] * (float)$d['custo_unitario'];
    $desperdicio[] = [
        'nome' => $d['nome_ingredientes'], 'unidade' => $d['unidade_medida'] ?: 'un',
        'qtd' => (float)$d['qtd'], 'custo' => $custo,
    ];
    $total_desperdicio += $custo;
}

$st = $pdo->prepare("SELECT d.data_registro, d.qtd_desperdicada, d.motivo, e.nome_ingredientes, e.unidade_medida, e.custo_unitario
                     FROM tb_desperdicio d
                     INNER JOIN tb_estoque e ON e.id_ingrediente = d.id_ingrediente
                     WHERE e.id_gestor = :g AND d.data_registro BETWEEN :i AND :f
                     ORDER BY d.data_registro DESC LIMIT 20");
$st->execute([':g' => $id_gestor, ':i' => $ini_sql, ':f' => $fim_sql]);
$registros_desperdicio = $st->fetchAll(PDO::FETCH_ASSOC);

$st = $pdo->prepare("SELECT id_ingrediente, nome_ingredientes, unidade_medida FROM tb_estoque WHERE id_gestor = :g ORDER BY nome_ingredientes ASC");
$st->execute([':g' => $id_gestor]);
$ingredientes = $st->fetchAll(PDO::FETCH_ASSOC);

// ---------- Tabela diária ----------
$dias = [];
$tot_pedidos = 0; $tot_receita = 0.0; $tot_custo = 0.0;
$sem_ped = array_fill(0, 7, 0);  // pedidos por dia da semana
$sem_cont = array_fill(0, 7, 0); // quantas vezes aquele dia da semana ocorreu no período
$dias_com_mov = 0; $max_pedidos = 0; $min_pedidos = PHP_INT_MAX;

for ($d = 1; $d <= $dias_considerados; $d++) {
    $data = sprintf('%s-%02d', $mes, $d);
    $ped  = $por_dia[$data]['pedidos'] ?? 0;
    $rec  = $por_dia[$data]['receita'] ?? 0.0;
    $custo = 0.0;
    $top = null;
    foreach ($vendas_dia[$data] ?? [] as $idp => $v) {
        $custo += $v['qtd'] * ($custo_prato[$idp] ?? 0);
        if ($top === null || $v['qtd'] > $top['qtd']) {
            $top = ['nome' => $pratos[$idp]['nome'] ?? 'Prato removido', 'qtd' => $v['qtd']];
        }
    }
    $w = (int)date('w', strtotime($data));
    $sem_ped[$w] += $ped;
    $sem_cont[$w]++;
    if ($ped > 0) {
        $dias_com_mov++;
        $max_pedidos = max($max_pedidos, $ped);
        $min_pedidos = min($min_pedidos, $ped);
    }
    $tot_pedidos += $ped; $tot_receita += $rec; $tot_custo += $custo;
    $dias[] = [
        'data' => $data, 'dia' => $d, 'semana' => $DIAS_SEMANA[$w], 'w' => $w,
        'pedidos' => $ped, 'receita' => $rec, 'custo' => $custo, 'lucro' => $rec - $custo,
        'ticket' => $ped > 0 ? $rec / $ped : 0, 'top' => $top,
    ];
}
if ($min_pedidos === PHP_INT_MAX) $min_pedidos = 0;

$media_dia = $dias_com_mov > 0 ? $tot_pedidos / $dias_com_mov : 0;

// Observação de cada dia
foreach ($dias as &$dd) {
    if ($dd['pedidos'] === 0) {
        $dd['obs'] = 'Sem pedidos fechados neste dia.';
        continue;
    }
    if ($dias_com_mov === 1) {
        $obs = 'Único dia com movimento no mês.';
    } elseif ($dd['pedidos'] === $max_pedidos) {
        $obs = 'Dia mais movimentado do mês (' . plural($dd['pedidos'], 'pedido', 'pedidos') . ').';
    } else {
        $dif = ($dd['pedidos'] - $media_dia) / $media_dia * 100;
        if ($dif >= 20)       $obs = 'Movimento acima da média (+' . number_format($dif, 0, ',', '.') . '%).';
        elseif ($dif <= -20)  $obs = 'Movimento abaixo da média (' . number_format($dif, 0, ',', '.') . '%).';
        else                  $obs = 'Movimento dentro da média.';
    }
    if ($dd['top']) {
        $obs .= ' Prato mais vendido: ' . $dd['top']['nome'] . ' (' . $dd['top']['qtd'] . ').';
    }
    if ($dd['lucro'] < 0) {
        $obs .= ' Atenção: o custo dos ingredientes superou a receita.';
    }
    $dd['obs'] = $obs;
}
unset($dd);

// ---------- Totais e lucro ----------
$lucro_bruto   = $tot_receita - $tot_custo;
$lucro_liquido = $lucro_bruto - $total_desperdicio;
$margem        = $tot_receita > 0 ? $lucro_liquido / $tot_receita * 100 : null;
$ticket_medio  = $tot_pedidos > 0 ? $tot_receita / $tot_pedidos : 0;

// ---------- Curva ABC (pratos cadastrados x vendas do mês) ----------
$abc = [];
foreach ($pratos as $idp => $p) {
    $abc[] = [
        'id' => $idp, 'nome' => $p['nome'],
        'qtd' => $venda_prato[$idp]['qtd'] ?? 0,
        'receita' => $venda_prato[$idp]['receita'] ?? 0.0,
    ];
}
usort($abc, function ($a, $b) {
    return [$b['receita'], $b['qtd'], $a['nome']] <=> [$a['receita'], $a['qtd'], $b['nome']];
});
$total_abc = array_sum(array_column($abc, 'receita'));
$acum = 0.0;
foreach ($abc as &$item) {
    if ($total_abc > 0 && $item['receita'] > 0) {
        $item['perc'] = $item['receita'] / $total_abc * 100;
        $antes = $acum;
        $acum += $item['perc'];
        $item['acum'] = $acum;
        $item['classe'] = $antes < 80 ? 'A' : ($antes < 95 ? 'B' : 'C');
    } else {
        $item['perc'] = 0; $item['acum'] = $total_abc > 0 ? 100 : 0; $item['classe'] = 'C';
    }
}
unset($item);
$qtd_classe = ['A' => 0, 'B' => 0, 'C' => 0];
$sem_venda  = [];
foreach ($abc as $item) {
    $qtd_classe[$item['classe']]++;
    if ($item['qtd'] === 0) $sem_venda[] = $item['nome'];
}

// ---------- Observações mensais ----------
$obs_mensais = [];
if ($tot_pedidos === 0) {
    $obs_mensais[] = $dias_considerados === 0
        ? 'Este mês ainda não começou, então não há movimentação para analisar.'
        : 'Não há pedidos fechados em ' . $mes_frase . '. As observações aparecem assim que houver comandas com baixa dada.';
} else {
    $obs_mensais[] = 'Em ' . $mes_frase . ' ' . ($tot_pedidos === 1 ? 'foi fechado ' : 'foram fechados ') . plural($tot_pedidos, 'pedido', 'pedidos')
        . ' em ' . plural($dias_com_mov, 'dia', 'dias') . ' com movimento (média de ' . number_format($media_dia, 1, ',', '.')
        . ' pedidos por dia de movimento), com receita de ' . moeda($tot_receita) . ' e ticket médio de ' . moeda($ticket_medio)
        . ' por pedido. ' . ($total_itens === 1 ? 'Foi vendido 1 item.' : 'Foram vendidos ' . $total_itens . ' itens.');

    $v_ped = var_pct((float)$tot_pedidos, (float)$mes_ant['pedidos']);
    $v_rec = var_pct($tot_receita, $mes_ant['receita']);
    $nome_ant = $MESES[(int)$anterior->format('n')];
    if ($v_ped !== null && $v_rec !== null) {
        $obs_mensais[] = 'Em relação a ' . $nome_ant . ', o número de pedidos ' . ($v_ped >= 0 ? 'subiu ' : 'caiu ')
            . pct(abs($v_ped)) . ' e a receita ' . ($v_rec >= 0 ? 'subiu ' : 'caiu ') . pct(abs($v_rec))
            . ($dias_considerados < $n_dias ? ' (atenção: o mês atual ainda não terminou, então a comparação é parcial).' : '.');
    } else {
        $obs_mensais[] = 'Não há pedidos fechados em ' . $nome_ant . ' para comparação.';
    }

    // dia da semana mais forte e mais fraco
    $media_semana = [];
    foreach ($sem_cont as $w => $c) {
        if ($c > 0) $media_semana[$w] = $sem_ped[$w] / $c;
    }
    if (count($media_semana) >= 2) {
        $w_max = array_keys($media_semana, max($media_semana))[0];
        $w_min = array_keys($media_semana, min($media_semana))[0];
        if ($w_max !== $w_min) {
            $obs_mensais[] = 'Por dia da semana, ' . $DIAS_SEMANA[$w_max] . ' é o mais movimentado (média de '
                . number_format($media_semana[$w_max], 1, ',', '.') . ' pedidos) e ' . $DIAS_SEMANA[$w_min]
                . ' o mais fraco (média de ' . number_format($media_semana[$w_min], 1, ',', '.') . ').';
        }
    }

    // horário de pico
    $pico = max($por_hora);
    if ($pico > 0) {
        $h_pico = array_search($pico, $por_hora, true);
        $obs_mensais[] = 'O horário de maior movimento é entre ' . $h_pico . 'h e ' . ($h_pico + 1) . 'h, com ' . plural($pico, 'comanda aberta', 'comandas abertas')
            . ' nessa faixa ao longo do mês.';
    }

    // melhor e pior dia
    if ($dias_com_mov >= 2) {
        $melhor = $pior = null;
        foreach ($dias as $d) {
            if ($d['pedidos'] === $max_pedidos && !$melhor) $melhor = $d;
            if ($d['pedidos'] === $min_pedidos && $d['pedidos'] > 0 && !$pior) $pior = $d;
        }
        if ($melhor && $pior && $melhor['data'] !== $pior['data']) {
            $obs_mensais[] = 'Dia mais movimentado: ' . date('d/m', strtotime($melhor['data'])) . ' (' . plural($melhor['pedidos'], 'pedido', 'pedidos')
                . '). Dia mais fraco com movimento: ' . date('d/m', strtotime($pior['data'])) . ' (' . plural($pior['pedidos'], 'pedido', 'pedidos') . ').';
        }
    }

    // dias sem movimento
    $parados = [];
    foreach ($dias as $d) { if ($d['pedidos'] === 0) $parados[] = date('d/m', strtotime($d['data'])); }
    if ($parados) {
        $obs_mensais[] = plural(count($parados), 'dia', 'dias') . ' sem pedidos fechados: '
            . implode(', ', array_slice($parados, 0, 8)) . (count($parados) > 8 ? ' e outros.' : '.');
    }

    // curva ABC
    if ($total_abc > 0) {
        $obs_mensais[] = 'Curva ABC: ' . $qtd_classe['A'] . ' ' . ($qtd_classe['A'] === 1 ? 'prato (classe A) concentra' : 'pratos (classe A) concentram')
            . ' cerca de 80% da receita; ' . $qtd_classe['B'] . ' na classe B e ' . $qtd_classe['C'] . ' na classe C. '
            . 'O prato que mais faturou foi ' . $abc[0]['nome'] . ' (' . pct($abc[0]['perc']) . ' da receita).';
    }
    if ($sem_venda) {
        $obs_mensais[] = count($sem_venda) . ' ' . (count($sem_venda) === 1 ? 'prato cadastrado não teve' : 'pratos cadastrados não tiveram')
            . ' nenhuma venda no mês: ' . implode(', ', array_slice($sem_venda, 0, 6)) . (count($sem_venda) > 6 ? ' e outros' : '')
            . '. Vale avaliar divulgação, preço ou a retirada do cardápio.';
    }

    // financeiro
    $txt_fin = 'O custo dos ingredientes dos pratos vendidos foi de ' . moeda($tot_custo) . ', resultando em lucro bruto de '
        . moeda($lucro_bruto) . ' (' . pct($tot_receita > 0 ? $lucro_bruto / $tot_receita * 100 : 0) . ' da receita).';
    if ($total_desperdicio > 0) {
        $txt_fin .= ' Descontado o desperdício de ' . moeda($total_desperdicio) . ', o lucro líquido estimado é de ' . moeda($lucro_liquido)
            . ' (margem de ' . pct($margem) . ').';
    }
    $obs_mensais[] = $txt_fin;

    // desperdício
    if ($total_desperdicio > 0 && $lucro_bruto > 0) {
        $peso = $total_desperdicio / $lucro_bruto * 100;
        $obs_mensais[] = 'O desperdício consumiu ' . pct($peso) . ' do lucro bruto. O ingrediente que mais pesou foi '
            . $desperdicio[0]['nome'] . ' (' . moeda($desperdicio[0]['custo']) . ')'
            . ($peso >= 5 ? ' — acima de 5% já merece atenção: revise porções, armazenamento e compras.' : '.');
    }
}

// pratos vendidos sem ficha técnica -> custo subestimado
$vendidos_sem_ficha = [];
foreach ($venda_prato as $idp => $v) {
    if (isset($pratos[$idp]) && empty($tem_ficha[$idp])) $vendidos_sem_ficha[] = $pratos[$idp]['nome'];
}

// ---------- Dados para os gráficos ----------
$dados = [
    'dias' => [
        'labels'  => array_map(fn($d) => str_pad((string)$d['dia'], 2, '0', STR_PAD_LEFT), $dias),
        'pedidos' => array_column($dias, 'pedidos'),
        'receita' => array_map(fn($d) => round($d['receita'], 2), $dias),
        'lucro'   => array_map(fn($d) => round($d['lucro'], 2), $dias),
    ],
    'horas' => ['labels' => array_map(fn($x) => $x . 'h', range(0, 23)), 'valores' => array_values($por_hora)],
    'semana' => [
        'labels'  => array_map(fn($n) => ucfirst(cortar($n, 3)), $DIAS_SEMANA),
        'valores' => array_map(fn($w) => $sem_cont[$w] > 0 ? round($sem_ped[$w] / $sem_cont[$w], 2) : 0, range(0, 6)),
    ],
    'abc' => [
        'labels'  => array_column($abc, 'nome'),
        'receita' => array_map(fn($i) => round($i['receita'], 2), $abc),
        'acum'    => array_map(fn($i) => round($i['acum'], 1), $abc),
        'classes' => array_column($abc, 'classe'),
    ],
    'desperdicio' => [
        'labels' => array_column($desperdicio, 'nome'),
        'custo'  => array_map(fn($d) => round($d['custo'], 2), $desperdicio),
    ],
];
$json_dados = json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RestControl - Cálculos e Relatórios</title>
    <link rel="stylesheet" href="calculos.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <div class="bg-overlay"></div>

    <div class="dashboard-wrapper">

        <header class="top-bar">
            <div class="brand">
                <img src="../pag_login/logo.png" alt="RestControl" class="top-logo">
                <span>RestControl</span>
            </div>
            <div class="top-actions">
                <a href="../home/home.php" class="top-link"><i class="fa-solid fa-house"></i> Voltar ao Painel</a>
            </div>
        </header>

        <main class="main-content">

            <div class="welcome-text">
                <h1><i class="fa-solid fa-calculator"></i> Cálculos e Relatórios</h1>
                <p>Vendas, lucro, desperdício, curva ABC e observações do movimento do restaurante.</p>
            </div>

            <div class="painel">

                <?php if ($flash): ?>
                    <div class="alert <?php echo $flash['tipo'] === 'ok' ? 'alert-ok' : 'alert-erro'; ?>"><?php echo h($flash['msg']); ?></div>
                <?php endif; ?>

                <div class="barra-periodo">
                    <a class="btn-mini" href="?mes=<?php echo $anterior->format('Y-m'); ?>"><i class="fa-solid fa-chevron-left"></i> <?php echo h(ucfirst($MESES[(int)$anterior->format('n')])); ?></a>
                    <form method="GET" class="form-mes" id="formMes">
                        <label for="mes">Mês:</label>
                        <input type="month" id="mes" name="mes" value="<?php echo h($mes); ?>">
                        <button type="submit" class="btn-mini">Ver</button>
                    </form>
                    <a class="btn-mini" href="?mes=<?php echo $proximo->format('Y-m'); ?>"><?php echo h(ucfirst($MESES[(int)$proximo->format('n')])); ?> <i class="fa-solid fa-chevron-right"></i></a>
                    <button type="button" class="btn-mini btn-mini-ok" id="btnImprimir"><i class="fa-solid fa-print"></i> Imprimir / salvar PDF</button>
                </div>

                <h2 class="titulo-periodo"><?php echo h($titulo_mes); ?></h2>

                <!-- ================= RESUMO ================= -->
                <section class="kpis">
                    <div class="kpi"><span class="kpi-label">Receita</span><span class="kpi-valor"><?php echo moeda($tot_receita); ?></span></div>
                    <div class="kpi"><span class="kpi-label">Custo dos ingredientes</span><span class="kpi-valor"><?php echo moeda($tot_custo); ?></span></div>
                    <div class="kpi"><span class="kpi-label">Lucro bruto</span><span class="kpi-valor <?php echo $lucro_bruto < 0 ? 'neg' : 'pos'; ?>"><?php echo moeda($lucro_bruto); ?></span></div>
                    <div class="kpi"><span class="kpi-label">Desperdício</span><span class="kpi-valor neg"><?php echo moeda($total_desperdicio); ?></span></div>
                    <div class="kpi"><span class="kpi-label">Lucro líquido estimado</span><span class="kpi-valor <?php echo $lucro_liquido < 0 ? 'neg' : 'pos'; ?>"><?php echo moeda($lucro_liquido); ?></span>
                        <small><?php echo $margem !== null ? 'Margem ' . pct($margem) : 'Sem vendas'; ?></small></div>
                    <div class="kpi"><span class="kpi-label">Pedidos fechados</span><span class="kpi-valor"><?php echo $tot_pedidos; ?></span>
                        <small>Ticket médio <?php echo moeda($ticket_medio); ?></small></div>
                </section>

                <?php if ($vendidos_sem_ficha): ?>
                    <div class="alert alert-aviso">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        Estes pratos foram vendidos mas não têm ficha técnica, então o custo deles está zerado e o lucro aparece maior que o real:
                        <strong><?php echo h(implode(', ', $vendidos_sem_ficha)); ?></strong>.
                        Preencha a <a href="../ficha_tecnica/ficha.php">Ficha Técnica</a> para um cálculo correto.
                    </div>
                <?php endif; ?>

                <!-- ================= OBSERVAÇÕES MENSAIS ================= -->
                <section class="card-box">
                    <h2><i class="fa-solid fa-comment-dots"></i> Observações do mês</h2>
                    <ul class="lista-obs">
                        <?php foreach ($obs_mensais as $o): ?>
                            <li><?php echo h($o); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </section>

                <!-- ================= GRÁFICOS ================= -->
                <section class="graficos">
                    <div class="card-box grafico-card">
                        <h3>Receita e lucro bruto por dia</h3>
                        <div class="grafico-area"><canvas id="chartDias"></canvas></div>
                    </div>
                    <div class="card-box grafico-card">
                        <h3>Pedidos fechados por dia</h3>
                        <div class="grafico-area"><canvas id="chartPedidos"></canvas></div>
                    </div>
                    <div class="card-box grafico-card">
                        <h3>Movimento por horário (comandas abertas)</h3>
                        <div class="grafico-area"><canvas id="chartHoras"></canvas></div>
                    </div>
                    <div class="card-box grafico-card">
                        <h3>Média de pedidos por dia da semana</h3>
                        <div class="grafico-area"><canvas id="chartSemana"></canvas></div>
                    </div>
                    <div class="card-box grafico-card grafico-largo">
                        <h3>Curva ABC dos pratos (receita e % acumulado)</h3>
                        <div class="grafico-area"><canvas id="chartAbc"></canvas></div>
                    </div>
                    <div class="card-box grafico-card grafico-largo">
                        <h3>Desperdício por ingrediente (R$)</h3>
                        <div class="grafico-area"><canvas id="chartDesperdicio"></canvas></div>
                    </div>
                </section>

                <!-- ================= CURVA ABC ================= -->
                <section class="card-box">
                    <div class="cabecalho-tabela">
                        <h2><i class="fa-solid fa-ranking-star"></i> Curva ABC</h2>
                        <button type="button" class="btn-mini" data-csv="tabelaAbc" data-nome="curva-abc-<?php echo h($mes); ?>.csv"><i class="fa-solid fa-file-csv"></i> Baixar CSV</button>
                    </div>
                    <p class="subtitle">Pratos cadastrados ordenados pela receita do mês. <strong>A</strong>: até 80% da receita acumulada · <strong>B</strong>: até 95% · <strong>C</strong>: o restante (inclui pratos sem vendas).</p>
                    <?php if (!$abc): ?>
                        <p class="vazio">Nenhum prato cadastrado. Cadastre em <a href="../pratos/pratos.php">Pratos</a>.</p>
                    <?php else: ?>
                        <div class="tabela-scroll">
                        <table class="tabela" id="tabelaAbc">
                            <thead><tr><th>#</th><th>Prato</th><th>Qtd. vendida</th><th>Receita</th><th>% da receita</th><th>% acumulado</th><th>Classe</th></tr></thead>
                            <tbody>
                            <?php foreach ($abc as $i => $item): ?>
                                <tr>
                                    <td><?php echo $i + 1; ?></td>
                                    <td><?php echo h($item['nome']); ?></td>
                                    <td><?php echo $item['qtd']; ?></td>
                                    <td><?php echo moeda($item['receita']); ?></td>
                                    <td><?php echo pct($item['perc']); ?></td>
                                    <td><?php echo $item['qtd'] > 0 ? pct($item['acum']) : '-'; ?></td>
                                    <td><span class="classe classe-<?php echo strtolower($item['classe']); ?>"><?php echo $item['classe']; ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                        </div>
                    <?php endif; ?>
                </section>

                <!-- ================= DIÁRIO ================= -->
                <section class="card-box">
                    <div class="cabecalho-tabela">
                        <h2><i class="fa-solid fa-calendar-day"></i> Movimento e observações diárias</h2>
                        <button type="button" class="btn-mini" data-csv="tabelaDias" data-nome="movimento-diario-<?php echo h($mes); ?>.csv"><i class="fa-solid fa-file-csv"></i> Baixar CSV</button>
                    </div>
                    <?php if (!$dias): ?>
                        <p class="vazio">Este mês ainda não começou.</p>
                    <?php else: ?>
                        <div class="tabela-scroll">
                        <table class="tabela" id="tabelaDias">
                            <thead><tr><th>Dia</th><th>Pedidos</th><th>Receita</th><th>Custo</th><th>Lucro bruto</th><th>Ticket médio</th><th>Observação</th></tr></thead>
                            <tbody>
                            <?php foreach (array_reverse($dias) as $d): ?>
                                <tr class="<?php echo $d['pedidos'] === 0 ? 'linha-vazia' : ''; ?>">
                                    <td><?php echo date('d/m', strtotime($d['data'])); ?> <small><?php echo h(cortar($d['semana'], 3)); ?></small></td>
                                    <td><?php echo $d['pedidos']; ?></td>
                                    <td><?php echo moeda($d['receita']); ?></td>
                                    <td><?php echo moeda($d['custo']); ?></td>
                                    <td class="<?php echo $d['lucro'] < 0 ? 'neg' : ''; ?>"><?php echo moeda($d['lucro']); ?></td>
                                    <td><?php echo $d['pedidos'] > 0 ? moeda($d['ticket']) : '-'; ?></td>
                                    <td class="col-obs"><?php echo h($d['obs']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                        </div>
                    <?php endif; ?>
                </section>

                <!-- ================= DESPERDÍCIO ================= -->
                <section class="card-box" id="desperdicio">
                    <h2><i class="fa-solid fa-trash-can-arrow-up"></i> Desperdício</h2>
                    <p class="subtitle">Registre o que foi jogado fora, estragou ou caiu. A quantidade sai do estoque e o custo entra no cálculo do lucro líquido.</p>

                    <?php if (!$ingredientes): ?>
                        <p class="vazio">Cadastre ingredientes em <a href="../estoque/estoque.php">Estoque</a> para registrar desperdícios.</p>
                    <?php else: ?>
                        <form method="POST" action="calculos.php?mes=<?php echo h($mes); ?>" class="form-desperdicio">
                            <input type="hidden" name="csrf" value="<?php echo h($_SESSION['csrf']); ?>">
                            <input type="hidden" name="acao" value="registrar_desperdicio">
                            <div class="input-group">
                                <label for="id_ingrediente">Ingrediente</label>
                                <select id="id_ingrediente" name="id_ingrediente" required>
                                    <option value="" disabled selected>Selecione</option>
                                    <?php foreach ($ingredientes as $ing): ?>
                                        <option value="<?php echo (int)$ing['id_ingrediente']; ?>"><?php echo h($ing['nome_ingredientes']); ?> (<?php echo h($ing['unidade_medida'] ?: 'un'); ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="input-group">
                                <label for="quantidade">Quantidade (na unidade do estoque)</label>
                                <input type="text" id="quantidade" name="quantidade" placeholder="Ex: 250" required>
                            </div>
                            <div class="input-group input-motivo">
                                <label for="motivo">Motivo</label>
                                <input type="text" id="motivo" name="motivo" maxlength="200" placeholder="Ex: Vencido, caiu no chão, sobra do preparo">
                            </div>
                            <button type="submit" class="btn-mini btn-mini-ok"><i class="fa-solid fa-plus"></i> Registrar</button>
                        </form>
                    <?php endif; ?>

                    <?php if ($desperdicio): ?>
                        <h3 class="sub-titulo">Resumo do mês</h3>
                        <div class="tabela-scroll">
                        <table class="tabela">
                            <thead><tr><th>Ingrediente</th><th>Quantidade</th><th>Custo</th></tr></thead>
                            <tbody>
                            <?php foreach ($desperdicio as $d): ?>
                                <tr><td><?php echo h($d['nome']); ?></td><td><?php echo qtd_fmt($d['qtd']) . ' ' . h($d['unidade']); ?></td><td><?php echo moeda($d['custo']); ?></td></tr>
                            <?php endforeach; ?>
                            <tr class="linha-total"><td colspan="2">Total</td><td><?php echo moeda($total_desperdicio); ?></td></tr>
                            </tbody>
                        </table>
                        </div>

                        <h3 class="sub-titulo">Últimos registros</h3>
                        <div class="tabela-scroll">
                        <table class="tabela">
                            <thead><tr><th>Data</th><th>Ingrediente</th><th>Quantidade</th><th>Custo</th><th>Motivo</th></tr></thead>
                            <tbody>
                            <?php foreach ($registros_desperdicio as $r): ?>
                                <tr>
                                    <td><?php echo date('d/m H:i', strtotime($r['data_registro'])); ?></td>
                                    <td><?php echo h($r['nome_ingredientes']); ?></td>
                                    <td><?php echo qtd_fmt($r['qtd_desperdicada']) . ' ' . h($r['unidade_medida'] ?: 'un'); ?></td>
                                    <td><?php echo moeda((float)$r['qtd_desperdicada'] * (float)$r['custo_unitario']); ?></td>
                                    <td><?php echo h($r['motivo']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                        </div>
                    <?php else: ?>
                        <p class="vazio">Nenhum desperdício registrado neste mês.</p>
                    <?php endif; ?>
                </section>

                <p class="nota-calculo">
                    Como é calculado: <strong>receita</strong> = pedidos com baixa dada no mês; <strong>custo</strong> = ingredientes da ficha técnica de cada prato vendido × custo unitário do estoque
                    (o custo unitário deve ser o preço por <em>unidade de medida</em> do ingrediente, ex.: R$ por kg se o estoque está em kg);
                    <strong>lucro líquido</strong> = receita − custo − desperdício.
                </p>

            </div>
        </main>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
    <script>window.DADOS_CALCULOS = <?php echo $json_dados; ?>;</script>
    <script src="calculos.js"></script>
</body>
</html>

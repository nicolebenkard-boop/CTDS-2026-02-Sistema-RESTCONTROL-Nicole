<?php
// estoque/helpers.php - funções compartilhadas entre Pedidos (desconto de estoque) e Cálculos (custos)

/**
 * Converte uma quantidade de uma unidade para outra (ex: da unidade usada na
 * ficha técnica para a unidade em que o ingrediente é controlado no estoque).
 * Suporta g<->kg, ml<->l e un<->dz. Unidades incompatíveis/desconhecidas
 * são tratadas como 1:1 (melhor esforço, para não travar o cálculo).
 */
if (!function_exists('converter_unidade')) {
    function converter_unidade(float $quantidade, string $unidade_origem, string $unidade_destino): float {
        $unidade_origem  = strtolower(trim($unidade_origem));
        $unidade_destino = strtolower(trim($unidade_destino));

        if ($unidade_origem === $unidade_destino) {
            return $quantidade;
        }

        $fatores_base = ['g' => 1, 'kg' => 1000, 'ml' => 1, 'l' => 1000];
        if (isset($fatores_base[$unidade_origem]) && isset($fatores_base[$unidade_destino])) {
            // só faz sentido converter dentro da mesma "família" (massa ou volume)
            $familia_origem  = in_array($unidade_origem, ['g', 'kg'], true) ? 'massa' : 'volume';
            $familia_destino = in_array($unidade_destino, ['g', 'kg'], true) ? 'massa' : 'volume';
            if ($familia_origem === $familia_destino) {
                $em_base = $quantidade * $fatores_base[$unidade_origem];
                return $em_base / $fatores_base[$unidade_destino];
            }
        }

        if ($unidade_origem === 'dz' && $unidade_destino === 'un') return $quantidade * 12;
        if ($unidade_origem === 'un' && $unidade_destino === 'dz') return $quantidade / 12;

        return $quantidade;
    }
}


/* ------------------------------------------------------------------
 * Estoque mínimo e máximo automáticos (baseados no mês anterior)
 *
 *   Estoque mínimo = consumo médio diário × tempo de reposição (dias)
 *   Estoque máximo = estoque mínimo + lote de reposição
 *
 * Consumo do mês anterior = ingredientes usados nos pratos vendidos
 * (pedidos fechados, pela ficha técnica) + desperdícios registrados.
 * ------------------------------------------------------------------ */

/** Confere se o SQL das colunas de reposição já foi rodado. */
if (!function_exists('estoque_tem_colunas_reposicao')) {
    function estoque_tem_colunas_reposicao(PDO $pdo): bool {
        static $ok = null;
        if ($ok === null) {
            $cols = $pdo->query("SHOW COLUMNS FROM tb_estoque WHERE Field IN ('tempo_reposicao_dias', 'lote_reposicao')")->fetchAll();
            $ok = count($cols) === 2;
        }
        return $ok;
    }
}

/** Início, fim, nº de dias e nome (em português) do mês anterior ao atual. */
if (!function_exists('periodo_mes_anterior')) {
    function periodo_mes_anterior(): array {
        $meses = [1 => 'janeiro', 'fevereiro', 'março', 'abril', 'maio', 'junho',
                  'julho', 'agosto', 'setembro', 'outubro', 'novembro', 'dezembro'];
        $ini = new DateTime('first day of last month 00:00:00');
        $fim = new DateTime('last day of last month 23:59:59');
        return [
            'ini'    => $ini->format('Y-m-d H:i:s'),
            'fim'    => $fim->format('Y-m-d H:i:s'),
            'dias'   => (int)$ini->format('t'),
            'rotulo' => $meses[(int)$ini->format('n')] . ' de ' . $ini->format('Y'),
        ];
    }
}

/** Arredonda para cima, sem casas em g/ml/un e com 1 casa em kg/l/dz. */
if (!function_exists('arredondar_estoque')) {
    function arredondar_estoque(float $valor, string $unidade): float {
        if (in_array(strtolower($unidade), ['kg', 'l', 'dz'], true)) {
            return ceil(round($valor * 10, 6)) / 10;
        }
        return ceil(round($valor, 6));
    }
}

/**
 * Recalcula quantidade_minima e quantidade_maxima de todos os ingredientes do
 * gestor, com base no consumo do mês anterior. Devolve, por ingrediente, o
 * consumo total, o consumo médio diário e se havia histórico no período.
 * Sem consumo no mês anterior, o mínimo atual é mantido (só o máximo acompanha
 * o lote: máximo = mínimo + lote).
 */
if (!function_exists('atualizar_minimo_maximo')) {
    function atualizar_minimo_maximo(PDO $pdo, int $id_gestor): array {
        $periodo = periodo_mes_anterior();

        $stmt = $pdo->prepare("SELECT id_ingrediente, unidade_medida, quantidade_minima, tempo_reposicao_dias, lote_reposicao
                               FROM tb_estoque WHERE id_gestor = :g");
        $stmt->execute([':g' => $id_gestor]);
        $ingredientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $consumo = [];

        // 1) Vendas do mês anterior, convertidas para a unidade do estoque
        $stmt = $pdo->prepare("
            SELECT f.id_ingrediente, f.quantidade_necessaria, f.unidade, e.unidade_medida,
                   SUM(i.quantidade_vendida) AS qtd_vendida
            FROM tb_itens_pedido i
            INNER JOIN tb_pedidos p ON p.id_pedido = i.id_pedido
            INNER JOIN tb_item_ficha_tecnica f ON f.id_prato = i.id_prato
            INNER JOIN tb_estoque e ON e.id_ingrediente = f.id_ingrediente AND e.id_gestor = :g1
            WHERE p.id_gestor = :g2 AND p.status = 'fechado'
              AND p.data_fechamento BETWEEN :ini AND :fim
            GROUP BY f.id_ingrediente, f.quantidade_necessaria, f.unidade, e.unidade_medida
        ");
        $stmt->execute([':g1' => $id_gestor, ':g2' => $id_gestor, ':ini' => $periodo['ini'], ':fim' => $periodo['fim']]);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $por_prato = converter_unidade((float)$r['quantidade_necessaria'], (string)$r['unidade'], (string)($r['unidade_medida'] ?: 'un'));
            $id = (int)$r['id_ingrediente'];
            $consumo[$id] = ($consumo[$id] ?? 0) + $por_prato * (float)$r['qtd_vendida'];
        }

        // 2) Desperdício do mês anterior (já está na unidade do estoque)
        $stmt = $pdo->prepare("
            SELECT d.id_ingrediente, SUM(d.qtd_desperdicada) AS qtd
            FROM tb_desperdicio d
            INNER JOIN tb_estoque e ON e.id_ingrediente = d.id_ingrediente
            WHERE e.id_gestor = :g AND d.data_registro BETWEEN :ini AND :fim
            GROUP BY d.id_ingrediente
        ");
        $stmt->execute([':g' => $id_gestor, ':ini' => $periodo['ini'], ':fim' => $periodo['fim']]);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $id = (int)$r['id_ingrediente'];
            $consumo[$id] = ($consumo[$id] ?? 0) + (float)$r['qtd'];
        }

        // 3) Aplica a fórmula em cada ingrediente
        $upd = $pdo->prepare("UPDATE tb_estoque SET quantidade_minima = :min, quantidade_maxima = :max
                              WHERE id_ingrediente = :id AND id_gestor = :g");
        $info = [];
        foreach ($ingredientes as $ing) {
            $id      = (int)$ing['id_ingrediente'];
            $unidade = (string)($ing['unidade_medida'] ?: 'un');
            $total   = $consumo[$id] ?? 0.0;
            $diario  = $total / $periodo['dias'];
            $tem_historico = $total > 0;

            if ($tem_historico) {
                $minimo = arredondar_estoque($diario * max(1, (int)$ing['tempo_reposicao_dias']), $unidade);
            } else {
                $minimo = (float)$ing['quantidade_minima'];
            }
            $maximo = $minimo + (float)$ing['lote_reposicao'];

            $upd->execute([':min' => $minimo, ':max' => $maximo, ':id' => $id, ':g' => $id_gestor]);

            $info[$id] = ['consumo_total' => $total, 'consumo_diario' => $diario, 'tem_historico' => $tem_historico];
        }

        return ['periodo' => $periodo, 'ingredientes' => $info];
    }
}

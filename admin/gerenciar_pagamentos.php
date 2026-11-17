<?php
session_start();

if (!isset($_SESSION['admin_logado']) || $_SESSION['admin_logado'] !== true) {
    header("Location: login.php");
    exit;
}

require_once '../conexao.php';

// Logout
if (isset($_GET['sair'])) {
    unset($_SESSION['admin_logado']);
    header("Location: login.php");
    exit;
}

// Confere se o SQL de atualização já foi rodado (coluna data_liberacao)
$col = $pdo->query("SHOW COLUMNS FROM tb_gestor LIKE 'data_liberacao'")->fetch();
if (!$col) {
    die("Falta rodar o arquivo sql/atualizar_banco_completo.sql (ou sql/alteracoes_perfil_admin.sql) no phpMyAdmin, aba SQL.");
}

if (empty($_SESSION['admin_csrf'])) {
    $_SESSION['admin_csrf'] = bin2hex(random_bytes(32));
}

const MESES_PERMITIDOS = [1, 3, 6, 12];

function h($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function data_br(?string $d): string { return $d ? date('d/m/Y', strtotime($d)) : '—'; }
function plural_meses(int $n): string { return $n . ($n === 1 ? ' mês' : ' meses'); }

function formatar_telefone(string $v): string {
    $d = preg_replace('/\D/', '', $v);
    if (strlen($d) === 11) return preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $d);
    if (strlen($d) === 10) return preg_replace('/(\d{2})(\d{4})(\d{4})/', '($1) $2-$3', $d);
    return $v;
}

/**
 * Libera/renova a assinatura somando N meses de calendário.
 * Se ainda está dentro do prazo, soma ao vencimento atual; se estava vencida ou
 * nunca ativada, conta a partir de hoje. Registra a data da primeira liberação.
 */
function renovar_gestor(PDO $pdo, int $id_gestor, int $meses): void {
    $meses = (int)$meses; // já validado contra MESES_PERMITIDOS / vindo do banco
    $pdo->prepare("UPDATE tb_gestor
                   SET status = 'ativo',
                       data_vencimento = DATE_ADD(
                           IF(data_vencimento IS NOT NULL AND data_vencimento >= CURDATE(), data_vencimento, CURDATE()),
                           INTERVAL $meses MONTH
                       ),
                       data_liberacao = COALESCE(data_liberacao, CURDATE()),
                       assinatura_cancelada = 0,
                       data_cancelamento = NULL
                   WHERE id_gestor = :id")
        ->execute([':id' => $id_gestor]);
}

function aviso(string $tipo, string $msg): void {
    $_SESSION['admin_flash'] = ['tipo' => $tipo, 'msg' => $msg];
}

$filtro = $_GET['filtro'] ?? 'todos';
if (!in_array($filtro, ['todos', 'vencendo', 'vencidos', 'pendentes', 'ativos'], true)) $filtro = 'todos';
$ordem_venc = (($_GET['ordem'] ?? '') === 'vencimento');
$volta = 'gerenciar_pagamentos.php' . ($filtro !== 'todos' || $ordem_venc
    ? '?' . http_build_query(array_filter(['filtro' => $filtro !== 'todos' ? $filtro : null, 'ordem' => $ordem_venc ? 'vencimento' : null])) : '');

// ---------- Ação: renovar / ativar por 1, 3, 6 ou 12 meses ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'renovar') {
    $id    = (int)($_POST['id_gestor'] ?? 0);
    $meses = (int)($_POST['meses'] ?? 0);

    if (!hash_equals($_SESSION['admin_csrf'], $_POST['csrf'] ?? '')) {
        aviso('erro', 'Sessão expirada. Recarregue a página e tente de novo.');
    } elseif (!in_array($meses, MESES_PERMITIDOS, true)) {
        aviso('erro', 'Período inválido.');
    } else {
        $st = $pdo->prepare("SELECT nome_gestor FROM tb_gestor WHERE id_gestor = :id");
        $st->execute([':id' => $id]);
        $nome = $st->fetchColumn();

        if ($nome === false) {
            aviso('erro', 'Usuário não encontrado.');
        } else {
            renovar_gestor($pdo, $id, $meses);
            $st = $pdo->prepare("SELECT data_vencimento FROM tb_gestor WHERE id_gestor = :id");
            $st->execute([':id' => $id]);
            aviso('ok', 'Assinatura de ' . $nome . ' renovada por ' . plural_meses($meses) . '. Novo vencimento: ' . data_br($st->fetchColumn()) . '.');
        }
    }
    header("Location: $volta");
    exit;
}

// ---------- Ação: confirmar pagamento adiantado (gestor pediu X meses) ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar_pagamento'])) {
    $id_pag = (int)$_POST['confirmar_pagamento'];

    if (!hash_equals($_SESSION['admin_csrf'], $_POST['csrf'] ?? '')) {
        aviso('erro', 'Sessão expirada. Recarregue a página e tente de novo.');
    } else {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("SELECT id_gestor, meses FROM tb_pagamentos WHERE id_pagamento = :p AND status = 'aguardando' FOR UPDATE");
            $stmt->execute([':p' => $id_pag]);
            $pag = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($pag) {
                renovar_gestor($pdo, (int)$pag['id_gestor'], (int)$pag['meses']);
                $pdo->prepare("UPDATE tb_pagamentos SET status = 'confirmado', data_confirmacao = NOW() WHERE id_pagamento = :p")
                    ->execute([':p' => $id_pag]);
                aviso('ok', 'Pagamento #' . $id_pag . ' confirmado: +' . plural_meses((int)$pag['meses']) . '.');
            }
            $pdo->commit();
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            aviso('erro', 'Não foi possível confirmar o pagamento.');
        }
    }
    header("Location: $volta");
    exit;
}

$flash = $_SESSION['admin_flash'] ?? null;
unset($_SESSION['admin_flash']);

// ---------- Dados ----------
$pagamentos_pendentes = $pdo->query("SELECT p.*, g.nome_gestor, g.restaurante
                     FROM tb_pagamentos p
                     INNER JOIN tb_gestor g ON g.id_gestor = p.id_gestor
                     WHERE p.status = 'aguardando'
                     ORDER BY p.data_solicitacao ASC")->fetchAll(PDO::FETCH_ASSOC);

$todos = $pdo->query("SELECT * FROM tb_gestor ORDER BY id_gestor DESC")->fetchAll(PDO::FETCH_ASSOC);
$hoje  = new DateTime('today');

$cont = ['todos' => 0, 'ativos' => 0, 'vencendo' => 0, 'vencidos' => 0, 'pendentes' => 0];
$linhas = [];
foreach ($todos as $g) {
    $venc = $g['data_vencimento'] ? new DateTime($g['data_vencimento']) : null;
    $dias = $venc ? (int)$hoje->diff($venc)->format('%r%a') : null;

    if ($g['status'] === 'pendente' || !$venc) {
        $situacao = 'pendente';
    } elseif ($dias < 0) {
        $situacao = 'vencido';
    } elseif ($dias <= 7) {
        $situacao = 'vencendo';
    } else {
        $situacao = 'ativo';
    }

    $cont['todos']++;
    if ($situacao === 'pendente') $cont['pendentes']++;
    elseif ($situacao === 'vencido') $cont['vencidos']++;
    elseif ($situacao === 'vencendo') { $cont['vencendo']++; $cont['ativos']++; }
    else $cont['ativos']++;

    $g['_dias'] = $dias;
    $g['_situacao'] = $situacao;
    $linhas[] = $g;
}

$linhas = array_values(array_filter($linhas, function ($g) use ($filtro) {
    switch ($filtro) {
        case 'vencendo':  return $g['_situacao'] === 'vencendo';
        case 'vencidos':  return $g['_situacao'] === 'vencido';
        case 'pendentes': return $g['_situacao'] === 'pendente';
        case 'ativos':    return in_array($g['_situacao'], ['ativo', 'vencendo'], true);
        default:          return true;
    }
}));

if ($ordem_venc) {
    usort($linhas, function ($a, $b) {
        if ($a['_dias'] === null && $b['_dias'] === null) return 0;
        if ($a['_dias'] === null) return 1;   // sem vencimento vai para o fim
        if ($b['_dias'] === null) return -1;
        return $a['_dias'] <=> $b['_dias'];
    });
}

function texto_prazo(?int $d): string {
    if ($d === null) return 'nunca ativado';
    if ($d > 1)  return "em {$d} dias";
    if ($d === 1) return 'amanhã';
    if ($d === 0) return 'vence hoje';
    $n = abs($d);
    return "há {$n} " . ($n === 1 ? 'dia' : 'dias');
}

function url_filtro(string $f, bool $ordem): string {
    $q = array_filter(['filtro' => $f !== 'todos' ? $f : null, 'ordem' => $ordem ? 'vencimento' : null]);
    return 'gerenciar_pagamentos.php' . ($q ? '?' . http_build_query($q) : '');
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Admin - Gerenciador de Assinaturas</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #121212; color: #fff; padding: 20px; margin: 0; }
        .top-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px; }
        .btn-sair { background: #dc3545; color: #fff; padding: 8px 15px; text-decoration: none; border-radius: 4px; font-weight: bold; }
        .tabela-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; background: #1e1e1e; }
        th, td { border: 1px solid #444; padding: 12px; text-align: left; vertical-align: middle; }
        th { background: #8B0000; }
        .btn-renovar { background: #28a745; color: #fff; text-decoration: none; padding: 8px 12px; border-radius: 4px; font-weight: bold; display: inline-block; border: 0; cursor: pointer; font-size: 0.9rem; }
        .btn-renovar:hover { background: #218838; }
        .btn-info { background: #2d2d2d; color: #fff; border: 1px solid #666; padding: 8px 12px; border-radius: 4px; cursor: pointer; font-weight: bold; font-size: 0.9rem; }
        .btn-info:hover { background: #3a3a3a; }
        .badge { padding: 4px 8px; border-radius: 4px; font-weight: bold; font-size: 0.85rem; display: inline-block; }
        .bg-pendente { background: #ffc107; color: #000; }
        .bg-ativo { background: #28a745; color: #fff; }
        .bg-vencendo { background: #fd7e14; color: #fff; }
        .bg-vencido { background: #dc3545; color: #fff; }
        .bg-cancelado { background: #6c757d; color: #fff; margin-left: 4px; }
        h3 { margin: 0 0 10px; }
        .bloco { margin-bottom: 30px; }
        .vazio { color: #888; }
        .prazo { font-size: 0.85rem; display: block; margin-top: 3px; }
        .prazo-ok { color: #6fdc8c; } .prazo-alerta { color: #ffb347; } .prazo-ruim { color: #ff6b6b; } .prazo-neutro { color: #aaa; }
        .form-renovar { display: flex; gap: 6px; margin: 0; align-items: center; }
        .form-renovar select { background: #262626; color: #fff; border: 1px solid #555; border-radius: 4px; padding: 7px; }

        .alerta { padding: 12px 16px; border-radius: 6px; margin-bottom: 18px; font-weight: bold; }
        .alerta-ok { background: rgba(40,167,69,0.2); border: 1px solid #28a745; color: #8be0a0; }
        .alerta-erro { background: rgba(220,53,69,0.2); border: 1px solid #dc3545; color: #ff9aa5; }

        .resumo { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 12px; margin-bottom: 22px; }
        .resumo a { background: #1e1e1e; border: 1px solid #444; border-radius: 8px; padding: 14px; text-decoration: none; color: #fff; display: block; }
        .resumo a:hover, .resumo a.ativo { border-color: #D4AF37; }
        .resumo strong { display: block; font-size: 1.8rem; }
        .resumo span { color: #aaa; font-size: 0.85rem; }
        .barra-filtro { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; margin-bottom: 12px; }
        .barra-filtro a { color: #D4AF37; font-size: 0.9rem; }

        dialog { background: #1e1e1e; color: #fff; border: 1px solid #8B0000; border-radius: 8px; padding: 0; width: min(460px, 92vw); }
        dialog::backdrop { background: rgba(0,0,0,0.7); }
        .modal-topo { background: #8B0000; padding: 14px 18px; display: flex; justify-content: space-between; align-items: center; }
        .modal-topo h3 { margin: 0; }
        .modal-fechar { background: none; border: 0; color: #fff; font-size: 1.6rem; cursor: pointer; line-height: 1; }
        .modal-corpo { padding: 18px; }
        .modal-corpo dl { margin: 0; }
        .modal-corpo .linha { display: flex; justify-content: space-between; gap: 14px; padding: 9px 0; border-bottom: 1px solid #333; }
        .modal-corpo .linha:last-child { border-bottom: 0; }
        .modal-corpo dt { color: #aaa; font-size: 0.88rem; }
        .modal-corpo dd { margin: 0; font-weight: bold; text-align: right; word-break: break-word; }
        .modal-corpo a { color: #D4AF37; }
        .modal-sub { color: #D4AF37; font-size: 0.9rem; margin: 0 0 12px; }
    </style>
</head>
<body>
    <div class="top-bar">
        <h2>Gerenciador de Assinaturas Mensais</h2>
        <a href="?sair=1" class="btn-sair">Sair do Painel</a>
    </div>

    <?php if ($flash): ?>
        <div class="alerta <?php echo $flash['tipo'] === 'ok' ? 'alerta-ok' : 'alerta-erro'; ?>"><?php echo h($flash['msg']); ?></div>
    <?php endif; ?>

    <div class="bloco">
        <h3>Pagamentos adiantados aguardando confirmação</h3>
        <?php if ($pagamentos_pendentes): ?>
        <div class="tabela-wrap">
        <table>
            <tr>
                <th>Pedido</th>
                <th>Nome / Restaurante</th>
                <th>Período</th>
                <th>Valor esperado (Pix)</th>
                <th>Solicitado em</th>
                <th>Ação</th>
            </tr>
            <?php foreach ($pagamentos_pendentes as $pg): ?>
            <tr>
                <td>#<?php echo (int)$pg['id_pagamento']; ?></td>
                <td>
                    <strong><?php echo h($pg['nome_gestor']); ?></strong><br>
                    <small style="color:#D4AF37;"><?php echo h($pg['restaurante']); ?></small>
                </td>
                <td><?php echo plural_meses((int)$pg['meses']); ?></td>
                <td>R$ <?php echo number_format($pg['valor'], 2, ',', '.'); ?></td>
                <td><?php echo date('d/m/Y H:i', strtotime($pg['data_solicitacao'])); ?></td>
                <td>
                    <form method="POST" style="margin:0;"
                          onsubmit="return confirm('Confirmou o recebimento de R$ <?php echo number_format($pg['valor'], 2, ',', '.'); ?> no seu banco?');">
                        <input type="hidden" name="csrf" value="<?php echo h($_SESSION['admin_csrf']); ?>">
                        <input type="hidden" name="confirmar_pagamento" value="<?php echo (int)$pg['id_pagamento']; ?>">
                        <button type="submit" class="btn-renovar">Confirmar recebimento</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        </div>
        <?php else: ?>
            <p class="vazio">Nenhum pagamento adiantado aguardando.</p>
        <?php endif; ?>
    </div>

    <h3>Acompanhamento das assinaturas</h3>

    <div class="resumo">
        <a href="<?php echo h(url_filtro('todos', $ordem_venc)); ?>" class="<?php echo $filtro === 'todos' ? 'ativo' : ''; ?>"><strong><?php echo $cont['todos']; ?></strong><span>Todos os usuários</span></a>
        <a href="<?php echo h(url_filtro('ativos', $ordem_venc)); ?>" class="<?php echo $filtro === 'ativos' ? 'ativo' : ''; ?>"><strong><?php echo $cont['ativos']; ?></strong><span>Ativos</span></a>
        <a href="<?php echo h(url_filtro('vencendo', true)); ?>" class="<?php echo $filtro === 'vencendo' ? 'ativo' : ''; ?>"><strong style="color:#ffb347;"><?php echo $cont['vencendo']; ?></strong><span>Vencem em até 7 dias</span></a>
        <a href="<?php echo h(url_filtro('vencidos', true)); ?>" class="<?php echo $filtro === 'vencidos' ? 'ativo' : ''; ?>"><strong style="color:#ff6b6b;"><?php echo $cont['vencidos']; ?></strong><span>Vencidos</span></a>
        <a href="<?php echo h(url_filtro('pendentes', $ordem_venc)); ?>" class="<?php echo $filtro === 'pendentes' ? 'ativo' : ''; ?>"><strong style="color:#ffc107;"><?php echo $cont['pendentes']; ?></strong><span>Pendentes (nunca ativados)</span></a>
    </div>

    <div class="barra-filtro">
        <?php if ($ordem_venc): ?>
            <span>Ordenado por vencimento (mais próximo primeiro).</span> <a href="<?php echo h(url_filtro($filtro, false)); ?>">Voltar à ordem normal</a>
        <?php else: ?>
            <a href="<?php echo h(url_filtro($filtro, true)); ?>">Ordenar por vencimento</a>
        <?php endif; ?>
    </div>

    <div class="tabela-wrap">
    <table>
        <tr>
            <th>ID</th>
            <th>Nome / Restaurante</th>
            <th>Vencimento</th>
            <th>Status Assinatura</th>
            <th>Renovar assinatura</th>
            <th>Detalhes</th>
        </tr>
        <?php if (!$linhas): ?>
            <tr><td colspan="6" class="vazio">Nenhum usuário neste filtro.</td></tr>
        <?php endif; ?>
        <?php foreach ($linhas as $g):
            $sit  = $g['_situacao'];
            $dias = $g['_dias'];
            $classe_prazo = $sit === 'vencido' ? 'prazo-ruim' : ($sit === 'vencendo' ? 'prazo-alerta' : ($sit === 'ativo' ? 'prazo-ok' : 'prazo-neutro'));
            $prazo = texto_prazo($dias);
            $tel_digitos = preg_replace('/\D/', '', $g['telefone']);
        ?>
        <tr>
            <td><?php echo (int)$g['id_gestor']; ?></td>
            <td>
                <strong><?php echo h($g['nome_gestor']); ?></strong><br>
                <small style="color:#D4AF37;"><?php echo h($g['restaurante']); ?></small>
            </td>
            <td>
                <?php if ($g['data_vencimento']): ?>
                    <?php echo data_br($g['data_vencimento']); ?>
                    <span class="prazo <?php echo $classe_prazo; ?>"><?php echo h($prazo); ?></span>
                <?php else: ?>
                    <em>Nunca ativado</em>
                <?php endif; ?>
            </td>
            <td>
                <?php if ($sit === 'pendente'): ?>
                    <span class="badge bg-pendente">PENDENTE</span>
                <?php elseif ($sit === 'vencido'): ?>
                    <span class="badge bg-vencido">VENCIDO</span>
                <?php elseif ($sit === 'vencendo'): ?>
                    <span class="badge bg-vencendo">VENCE EM BREVE</span>
                <?php else: ?>
                    <span class="badge bg-ativo">ATIVO</span>
                <?php endif; ?>
                <?php if (!empty($g['assinatura_cancelada'])): ?>
                    <span class="badge bg-cancelado" title="O gestor cancelou a renovação">NÃO VAI RENOVAR</span>
                <?php endif; ?>
            </td>
            <td>
                <form method="POST" action="<?php echo h($volta); ?>" class="form-renovar"
                      onsubmit="var s=this.meses; return confirm('Renovar a assinatura de <?php echo h(addslashes($g['nome_gestor'])); ?> por ' + s.options[s.selectedIndex].text + '?\n\nConfirme que recebeu o Pix antes.');">
                    <input type="hidden" name="csrf" value="<?php echo h($_SESSION['admin_csrf']); ?>">
                    <input type="hidden" name="acao" value="renovar">
                    <input type="hidden" name="id_gestor" value="<?php echo (int)$g['id_gestor']; ?>">
                    <select name="meses" aria-label="Período">
                        <?php foreach (MESES_PERMITIDOS as $m): ?>
                            <option value="<?php echo $m; ?>"><?php echo plural_meses($m); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn-renovar"><?php echo $sit === 'pendente' ? 'Ativar' : 'Renovar'; ?></button>
                </form>
            </td>
            <td>
                <button type="button" class="btn-info"
                    data-nome="<?php echo h($g['nome_gestor']); ?>"
                    data-restaurante="<?php echo h($g['restaurante']); ?>"
                    data-email="<?php echo h($g['e_mail']); ?>"
                    data-telefone="<?php echo h(formatar_telefone($g['telefone'])); ?>"
                    data-tel="<?php echo h($tel_digitos); ?>"
                    data-liberacao="<?php echo h($g['data_liberacao'] ? data_br($g['data_liberacao']) : 'Ainda não liberado'); ?>"
                    data-vencimento="<?php echo h(data_br($g['data_vencimento'])); ?>"
                    data-prazo="<?php echo h($g['data_vencimento'] ? $prazo : '—'); ?>"
                    data-cpf="<?php echo h($g['CPF']); ?>"
                    data-cnpj="<?php echo h($g['CNPJ']); ?>">Saiba mais</button>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    </div>

    <dialog id="modalInfo">
        <div class="modal-topo">
            <h3 id="mNome">Usuário</h3>
            <button type="button" class="modal-fechar" id="mFechar" aria-label="Fechar">&times;</button>
        </div>
        <div class="modal-corpo">
            <p class="modal-sub" id="mRestaurante"></p>
            <dl>
                <div class="linha"><dt>E-mail</dt><dd><a id="mEmail" href="#"></a></dd></div>
                <div class="linha"><dt>Telefone</dt><dd><a id="mTel" href="#"></a></dd></div>
                <div class="linha"><dt>WhatsApp</dt><dd><a id="mZap" href="#" target="_blank" rel="noopener noreferrer">Abrir conversa</a></dd></div>
                <div class="linha"><dt>Assinatura liberada em</dt><dd id="mLiberacao"></dd></div>
                <div class="linha"><dt>Vencimento</dt><dd id="mVencimento"></dd></div>
                <div class="linha"><dt>Prazo</dt><dd id="mPrazo"></dd></div>
                <div class="linha"><dt>CPF</dt><dd id="mCpf"></dd></div>
                <div class="linha"><dt>CNPJ</dt><dd id="mCnpj"></dd></div>
            </dl>
        </div>
    </dialog>

    <script>
        (function () {
            var modal = document.getElementById('modalInfo');
            function $(id) { return document.getElementById(id); }

            document.querySelectorAll('.btn-info').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var d = btn.dataset;
                    $('mNome').textContent = d.nome;
                    $('mRestaurante').textContent = d.restaurante;
                    $('mEmail').textContent = d.email;
                    $('mEmail').href = 'mailto:' + d.email;
                    $('mTel').textContent = d.telefone || '—';
                    $('mTel').href = d.tel ? 'tel:' + d.tel : '#';
                    var zapDigitos = d.tel && d.tel.length <= 11 ? '55' + d.tel : d.tel;
                    $('mZap').href = zapDigitos ? 'https://wa.me/' + zapDigitos : '#';
                    $('mLiberacao').textContent = d.liberacao;
                    $('mVencimento').textContent = d.vencimento;
                    $('mPrazo').textContent = d.prazo;
                    $('mCpf').textContent = d.cpf || '—';
                    $('mCnpj').textContent = d.cnpj || '—';
                    modal.showModal();
                });
            });

            $('mFechar').addEventListener('click', function () { modal.close(); });
            modal.addEventListener('click', function (e) { if (e.target === modal) modal.close(); }); // clique fora fecha
        })();
    </script>
</body>
</html>

<?php
session_start();
require_once '../conexao.php';

// Bloqueia acesso de quem não está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../pag_login/index.php");
    exit();
}

$id_gestor = (int)$_SESSION['usuario_id'];

// ---------- Configurações do plano (mude aqui se o preço/Pix mudar) ----------
const VALOR_MENSAL     = 10.00;
// Pix copia e cola base (R$ 10,00). O valor e o CRC são recalculados por pix_payload() conforme os meses escolhidos.
const PIX_COPIA_COLA   = '00020101021126730014br.gov.bcb.pix0136e514053d-84c9-4400-ab7c-76d35cc86ea00211RESTCONTROL520400005303986540510.005802BR5916NICOLE N BENKARD6008OURINHOS62070503***63041BF3';
const MESES_ADIANTADOS = [1, 3, 6, 12];
const CONTATO_TERMOS   = 'nicole.benkard@aluno.senai.br'; // e-mail mostrado nos Termos e Condições

// Confere se o SQL de alterações já foi rodado
$col = $pdo->query("SHOW COLUMNS FROM tb_gestor LIKE 'assinatura_cancelada'")->fetch();
$tab = $pdo->query("SHOW TABLES LIKE 'tb_pagamentos'")->fetch();
if (!$col || !$tab) {
    die("Falta rodar o arquivo sql/alteracoes_configuracoes_fornecedores.sql no phpMyAdmin (aba SQL).");
}

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

function h($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

// Gera o Pix copia e cola com o valor do pedido (troca o campo 54 e recalcula o CRC16)
function pix_payload($valor) {
    $base = substr(PIX_COPIA_COLA, 0, -4);
    $v = number_format((float)$valor, 2, '.', '');
    $campo = '54' . str_pad((string)strlen($v), 2, '0', STR_PAD_LEFT) . $v;
    $sem_crc = preg_replace('/54\d{2}\d+\.\d{2}(?=5802BR)/', $campo, $base);
    $crc = 0xFFFF;
    foreach (str_split($sem_crc) as $ch) {
        $crc ^= ord($ch) << 8;
        for ($i = 0; $i < 8; $i++) {
            $crc = ($crc & 0x8000) ? ((($crc << 1) ^ 0x1021) & 0xFFFF) : (($crc << 1) & 0xFFFF);
        }
    }
    return $sem_crc . strtoupper(str_pad(dechex($crc), 4, '0', STR_PAD_LEFT));
}

function moeda($v) { return 'R$ ' . number_format((float)$v, 2, ',', '.'); }

function voltar($aba, $tipo = null, $msg = null) {
    if ($msg !== null) {
        $_SESSION['flash'] = ['tipo' => $tipo, 'msg' => $msg];
    }
    header("Location: configuracoes.php?aba=" . urlencode($aba));
    exit();
}

$abas_validas = ['seguranca', 'assinatura', 'ajuda'];
$aba = $_GET['aba'] ?? 'seguranca';
if (!in_array($aba, $abas_validas, true)) $aba = 'seguranca';

// =====================================================================
// AÇÕES (todas por POST + token CSRF)
// =====================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) {
        voltar($aba, 'erro', 'Sessão expirada. Tente novamente.');
    }

    $acao = $_POST['acao'] ?? '';

    // ---------- Mudar senha ----------
    if ($acao === 'alterar_senha') {
        $atual = $_POST['senha_atual'] ?? '';
        $nova  = $_POST['nova_senha'] ?? '';
        $conf  = $_POST['confirmar_senha'] ?? '';

        $stmt = $pdo->prepare("SELECT senha FROM tb_gestor WHERE id_gestor = :id");
        $stmt->execute([':id' => $id_gestor]);
        $hash = $stmt->fetchColumn();

        if (!$hash || !password_verify($atual, $hash)) {
            voltar('seguranca', 'erro', 'A senha atual está incorreta.');
        }
        if (strlen($nova) < 8) {
            voltar('seguranca', 'erro', 'A nova senha deve ter pelo menos 8 caracteres.');
        }
        if ($nova !== $conf) {
            voltar('seguranca', 'erro', 'A confirmação não é igual à nova senha.');
        }
        if ($nova === $atual) {
            voltar('seguranca', 'erro', 'A nova senha precisa ser diferente da atual.');
        }

        $pdo->prepare("UPDATE tb_gestor SET senha = :s WHERE id_gestor = :id")
            ->execute([':s' => password_hash($nova, PASSWORD_DEFAULT), ':id' => $id_gestor]);

        session_regenerate_id(true);
        voltar('seguranca', 'ok', 'Senha alterada com sucesso.');
    }

    // ---------- Pagar adiantado: gerar solicitação ----------
    if ($acao === 'solicitar_pagamento') {
        $meses = (int)($_POST['meses'] ?? 0);
        if (!in_array($meses, MESES_ADIANTADOS, true)) {
            voltar('assinatura', 'erro', 'Escolha um período válido.');
        }

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM tb_pagamentos WHERE id_gestor = :id AND status = 'aguardando'");
        $stmt->execute([':id' => $id_gestor]);
        if ($stmt->fetchColumn() > 0) {
            voltar('assinatura', 'erro', 'Você já tem um pagamento aguardando confirmação.');
        }

        $pdo->prepare("INSERT INTO tb_pagamentos (id_gestor, meses, valor) VALUES (:g, :m, :v)")
            ->execute([':g' => $id_gestor, ':m' => $meses, ':v' => VALOR_MENSAL * $meses]);

        voltar('assinatura', 'ok', 'Pedido criado! Faça o Pix com o valor indicado abaixo.');
    }

    // ---------- Pagar adiantado: desistir da solicitação ----------
    if ($acao === 'cancelar_solicitacao') {
        $pdo->prepare("UPDATE tb_pagamentos SET status = 'cancelado'
                       WHERE id_pagamento = :p AND id_gestor = :g AND status = 'aguardando'")
            ->execute([':p' => (int)($_POST['id_pagamento'] ?? 0), ':g' => $id_gestor]);
        voltar('assinatura', 'ok', 'Solicitação cancelada.');
    }

    // ---------- Cancelar assinatura mensal ----------
    if ($acao === 'cancelar_assinatura') {
        $pdo->prepare("UPDATE tb_gestor SET assinatura_cancelada = 1, data_cancelamento = NOW() WHERE id_gestor = :id")
            ->execute([':id' => $id_gestor]);
        voltar('assinatura', 'ok', 'Assinatura cancelada. Você continua com acesso até o fim do período já pago.');
    }

    // ---------- Desfazer cancelamento ----------
    if ($acao === 'reativar_assinatura') {
        $pdo->prepare("UPDATE tb_gestor SET assinatura_cancelada = 0, data_cancelamento = NULL WHERE id_gestor = :id")
            ->execute([':id' => $id_gestor]);
        voltar('assinatura', 'ok', 'Cancelamento desfeito.');
    }

    // ---------- Apagar conta ----------
    if ($acao === 'apagar_conta') {
        $senha = $_POST['senha_confirma'] ?? '';
        $texto = trim($_POST['texto_confirma'] ?? '');

        $stmt = $pdo->prepare("SELECT senha FROM tb_gestor WHERE id_gestor = :id");
        $stmt->execute([':id' => $id_gestor]);
        $hash = $stmt->fetchColumn();

        if (!$hash || !password_verify($senha, $hash)) {
            voltar('seguranca', 'erro', 'Senha incorreta. Sua conta não foi apagada.');
        }
        if (strtoupper($texto) !== 'EXCLUIR') {
            voltar('seguranca', 'erro', 'Digite EXCLUIR para confirmar. Sua conta não foi apagada.');
        }

        try {
            // Guarda o nome das imagens dos pratos para apagar os arquivos depois
            $stmt = $pdo->prepare("SELECT imagem FROM tb_pratos WHERE id_gestor = :g AND imagem IS NOT NULL AND imagem <> ''");
            $stmt->execute([':g' => $id_gestor]);
            $imagens = $stmt->fetchAll(PDO::FETCH_COLUMN);

            $pdo->beginTransaction();
            $g = [':g' => $id_gestor];

            $pdo->prepare("DELETE ip FROM tb_itens_pedido ip INNER JOIN tb_pedidos p ON p.id_pedido = ip.id_pedido WHERE p.id_gestor = :g")->execute($g);
            $pdo->prepare("DELETE FROM tb_pedidos WHERE id_gestor = :g")->execute($g);
            $pdo->prepare("DELETE FROM tb_mesas WHERE id_gestor = :g")->execute($g);
            $pdo->prepare("DELETE i FROM tb_item_ficha_tecnica i INNER JOIN tb_pratos p ON p.id_prato = i.id_prato WHERE p.id_gestor = :g")->execute($g);
            $pdo->prepare("DELETE d FROM tb_desperdicio d INNER JOIN tb_estoque e ON e.id_ingrediente = d.id_ingrediente WHERE e.id_gestor = :g")->execute($g);
            $pdo->prepare("DELETE FROM tb_pratos WHERE id_gestor = :g")->execute($g);
            $pdo->prepare("DELETE FROM tb_fornecedores WHERE id_gestor = :g")->execute($g); // vínculos com estoque saem em cascata
            $pdo->prepare("DELETE FROM tb_estoque WHERE id_gestor = :g")->execute($g);
            $pdo->prepare("DELETE FROM tb_funcionarios WHERE id_gestor = :g")->execute($g);
            $pdo->prepare("DELETE FROM tb_kanban_tarefas WHERE id_gestor = :g")->execute($g);
            $pdo->prepare("DELETE FROM tb_layout WHERE id_gestor = :g")->execute($g);
            $pdo->prepare("DELETE FROM tb_pagamentos WHERE id_gestor = :g")->execute($g);
            $pdo->prepare("DELETE FROM tb_gestor WHERE id_gestor = :g")->execute($g);

            $pdo->commit();
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            voltar('seguranca', 'erro', 'Não foi possível apagar a conta agora. Nada foi excluído; tente novamente.');
        }

        // Remove os arquivos de imagem dos pratos
        $pasta = realpath(__DIR__ . '/../pratos/uploads');
        if ($pasta) {
            foreach ($imagens as $img) {
                $arquivo = $pasta . DIRECTORY_SEPARATOR . basename($img);
                if (is_file($arquivo)) @unlink($arquivo);
            }
        }

        $_SESSION = [];
        session_destroy();
        echo "<script>alert('Sua conta e todos os dados do restaurante foram apagados.'); window.location.href='../index.php';</script>";
        exit();
    }

    voltar($aba);
}

// =====================================================================
// DADOS PARA A TELA
// =====================================================================
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$stmt = $pdo->prepare("SELECT nome_gestor, e_mail, restaurante, status, data_vencimento, assinatura_cancelada, data_cancelamento
                       FROM tb_gestor WHERE id_gestor = :id");
$stmt->execute([':id' => $id_gestor]);
$gestor = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$gestor) { // conta não existe mais
    session_destroy();
    header("Location: ../pag_login/index.php");
    exit();
}

$hoje = new DateTime('today');
$venc = $gestor['data_vencimento'] ? new DateTime($gestor['data_vencimento']) : null;
$ativa = ($gestor['status'] === 'ativo' && $venc && $venc >= $hoje);
$dias_restantes = $venc ? (int)$hoje->diff($venc)->format('%r%a') : null;
$cancelada = (int)$gestor['assinatura_cancelada'] === 1;

$stmt = $pdo->prepare("SELECT * FROM tb_pagamentos WHERE id_gestor = :g AND status = 'aguardando' ORDER BY id_pagamento DESC LIMIT 1");
$stmt->execute([':g' => $id_gestor]);
$pendente = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT * FROM tb_pagamentos WHERE id_gestor = :g AND status <> 'aguardando' ORDER BY id_pagamento DESC LIMIT 5");
$stmt->execute([':g' => $id_gestor]);
$historico = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RestControl - Configurações</title>
    <link rel="stylesheet" href="configuracoes.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <div class="bg-overlay"></div>

    <header class="top-bar">
        <div class="brand">
            <img src="../pag_login/logo.png" alt="RestControl" class="top-logo">
            <span>RestControl</span>
        </div>
        <div class="top-actions">
            <a href="../home/home.php" class="top-link"><i class="fa-solid fa-house"></i> Voltar ao Painel</a>
        </div>
    </header>

    <div class="page">
        <h1><i class="fa-solid fa-gear"></i> Configurações</h1>

        <nav class="tabs">
            <a href="?aba=seguranca"  class="tab <?php echo $aba === 'seguranca'  ? 'active' : ''; ?>"><i class="fa-solid fa-shield-halved"></i> Privacidade e Segurança</a>
            <a href="?aba=assinatura" class="tab <?php echo $aba === 'assinatura' ? 'active' : ''; ?>"><i class="fa-solid fa-crown"></i> Assinatura</a>
            <a href="?aba=ajuda"      class="tab <?php echo $aba === 'ajuda'      ? 'active' : ''; ?>"><i class="fa-solid fa-circle-question"></i> Ajuda</a>
        </nav>

        <?php if ($flash): ?>
            <div class="alert <?php echo $flash['tipo'] === 'ok' ? 'alert-ok' : 'alert-erro'; ?>"><?php echo h($flash['msg']); ?></div>
        <?php endif; ?>

        <?php if ($aba === 'seguranca'): ?>
        <!-- ================= PRIVACIDADE E SEGURANÇA ================= -->

        <section class="card-box">
            <h2><i class="fa-solid fa-file-contract"></i> Termos e Condições</h2>
            <p class="subtitle">Leia como o RestControl funciona, como tratamos seus dados e quais são as regras de uso.</p>
            <div class="termos-box" tabindex="0">
                <?php include 'termos_conteudo.php'; ?>
            </div>
        </section>

        <section class="card-box">
            <h2><i class="fa-solid fa-key"></i> Mudar senha</h2>
            <p class="subtitle">Conta: <?php echo h($gestor['e_mail']); ?></p>
            <form method="POST" class="form-grid" id="formSenha">
                <input type="hidden" name="csrf" value="<?php echo h($_SESSION['csrf']); ?>">
                <input type="hidden" name="acao" value="alterar_senha">

                <div class="input-group">
                    <label for="senha_atual">Senha atual</label>
                    <input type="password" id="senha_atual" name="senha_atual" autocomplete="current-password" required>
                </div>
                <div class="input-group">
                    <label for="nova_senha">Nova senha (mínimo 8 caracteres)</label>
                    <input type="password" id="nova_senha" name="nova_senha" minlength="8" autocomplete="new-password" required>
                </div>
                <div class="input-group">
                    <label for="confirmar_senha">Confirmar nova senha</label>
                    <input type="password" id="confirmar_senha" name="confirmar_senha" minlength="8" autocomplete="new-password" required>
                    <small class="field-erro" id="erroConfirma" hidden>As senhas não são iguais.</small>
                </div>
                <button type="submit" class="btn btn-primary">Salvar nova senha</button>
            </form>
        </section>

        <section class="card-box danger-box">
            <h2><i class="fa-solid fa-triangle-exclamation"></i> Apagar conta</h2>
            <p class="subtitle">Esta ação é <strong>permanente</strong> e não pode ser desfeita.</p>
            <p class="texto">Serão apagados definitivamente: seus dados de acesso, pratos e fichas técnicas (com as imagens), estoque, fornecedores, funcionários, mesas, pedidos e histórico, tarefas do Kanban e layouts.</p>

            <details class="danger-details">
                <summary>Quero apagar minha conta</summary>
                <form method="POST" class="form-grid" id="formApagar">
                    <input type="hidden" name="csrf" value="<?php echo h($_SESSION['csrf']); ?>">
                    <input type="hidden" name="acao" value="apagar_conta">

                    <div class="input-group">
                        <label for="senha_confirma">Digite sua senha</label>
                        <input type="password" id="senha_confirma" name="senha_confirma" autocomplete="current-password" required>
                    </div>
                    <div class="input-group">
                        <label for="texto_confirma">Digite <strong>EXCLUIR</strong> para confirmar</label>
                        <input type="text" id="texto_confirma" name="texto_confirma" autocomplete="off" required>
                    </div>
                    <button type="submit" class="btn btn-danger" id="btnApagar" disabled>
                        <i class="fa-solid fa-trash-can"></i> Apagar minha conta para sempre
                    </button>
                </form>
            </details>
        </section>

        <?php elseif ($aba === 'assinatura'): ?>
        <!-- ================= ASSINATURA ================= -->

        <section class="card-box">
            <h2><i class="fa-solid fa-crown"></i> Sua assinatura</h2>
            <div class="status-grid">
                <div>
                    <span class="label">Situação</span>
                    <?php if ($ativa): ?>
                        <span class="badge badge-ok">Ativa</span>
                    <?php elseif ($venc): ?>
                        <span class="badge badge-erro">Vencida</span>
                    <?php else: ?>
                        <span class="badge badge-aviso">Pendente</span>
                    <?php endif; ?>
                </div>
                <div>
                    <span class="label">Valor</span>
                    <strong><?php echo moeda(VALOR_MENSAL); ?> / mês</strong>
                </div>
                <div>
                    <span class="label">Vencimento</span>
                    <strong><?php echo $venc ? $venc->format('d/m/Y') : '—'; ?></strong>
                    <?php if ($ativa): ?>
                        <small class="muted">(<?php echo $dias_restantes === 0 ? 'vence hoje' : "faltam {$dias_restantes} dia" . ($dias_restantes > 1 ? 's' : ''); ?>)</small>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($cancelada): ?>
                <div class="alert alert-aviso">
                    Renovação cancelada em <?php echo date('d/m/Y', strtotime($gestor['data_cancelamento'])); ?>.
                    <?php if ($ativa): ?>Seu acesso continua até <?php echo $venc->format('d/m/Y'); ?>.<?php endif; ?>
                    <form method="POST" class="inline-form">
                        <input type="hidden" name="csrf" value="<?php echo h($_SESSION['csrf']); ?>">
                        <input type="hidden" name="acao" value="reativar_assinatura">
                        <button type="submit" class="btn btn-secondary btn-sm">Desfazer cancelamento</button>
                    </form>
                </div>
            <?php endif; ?>
        </section>

        <section class="card-box">
            <h2><i class="fa-solid fa-forward"></i> Pagar adiantado</h2>
            <p class="subtitle">Pague vários meses de uma vez e fique tranquilo. Cada mês pago soma 1 mês ao seu vencimento atual.</p>

            <?php if ($pendente): ?>
                <div class="pix-box">
                    <span class="pix-badge">AGUARDANDO PAGAMENTO</span>
                    <p class="pix-resumo"><?php echo (int)$pendente['meses']; ?> <?php echo $pendente['meses'] > 1 ? 'meses' : 'mês'; ?> —
                        <strong><?php echo moeda($pendente['valor']); ?></strong></p>

                    <?php $pix_codigo = pix_payload($pendente['valor']); ?>
                    <div class="pix-qr">
                        <img src="../assinatura/qr_code_pix.png" alt="QR Code Pix" onerror="this.parentNode.style.display='none'">
                    </div>
                    <p class="pix-label">Ao pagar pelo QR Code, digite o valor de <strong><?php echo moeda($pendente['valor']); ?></strong>.</p>

                    <p class="pix-label">Pix Copia e Cola:</p>
                    <div class="pix-key-wrapper">
                        <input type="text" id="pix-key" value="<?php echo h($pix_codigo); ?>" readonly>
                        <button type="button" class="btn btn-secondary btn-sm" data-copy="#pix-key">Copiar</button>
                    </div>

                    <p class="texto">Depois de pagar, o administrador confirma o recebimento e o seu vencimento é estendido. A liberação é manual, então pode levar um tempo.</p>

                    <form method="POST" class="inline-form">
                        <input type="hidden" name="csrf" value="<?php echo h($_SESSION['csrf']); ?>">
                        <input type="hidden" name="acao" value="cancelar_solicitacao">
                        <input type="hidden" name="id_pagamento" value="<?php echo (int)$pendente['id_pagamento']; ?>">
                        <button type="submit" class="btn btn-secondary btn-sm">Desistir deste pedido</button>
                    </form>
                </div>
            <?php else: ?>
                <form method="POST" class="form-grid">
                    <input type="hidden" name="csrf" value="<?php echo h($_SESSION['csrf']); ?>">
                    <input type="hidden" name="acao" value="solicitar_pagamento">

                    <div class="opcoes-meses">
                        <?php foreach (MESES_ADIANTADOS as $i => $m): ?>
                            <label class="opcao-mes">
                                <input type="radio" name="meses" value="<?php echo $m; ?>" <?php echo $i === 0 ? 'checked' : ''; ?>>
                                <span>
                                    <strong><?php echo $m; ?> <?php echo $m > 1 ? 'meses' : 'mês'; ?></strong>
                                    <small><?php echo moeda(VALOR_MENSAL * $m); ?></small>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <button type="submit" class="btn btn-primary">Gerar Pix</button>
                </form>
            <?php endif; ?>

            <?php if ($historico): ?>
                <h3 class="sub-titulo">Últimos pedidos de pagamento adiantado</h3>
                <table class="tabela">
                    <tr><th>Data</th><th>Período</th><th>Valor</th><th>Situação</th></tr>
                    <?php foreach ($historico as $p): ?>
                        <tr>
                            <td><?php echo date('d/m/Y', strtotime($p['data_solicitacao'])); ?></td>
                            <td><?php echo (int)$p['meses']; ?> <?php echo $p['meses'] > 1 ? 'meses' : 'mês'; ?></td>
                            <td><?php echo moeda($p['valor']); ?></td>
                            <td><?php echo $p['status'] === 'confirmado' ? 'Confirmado' : 'Cancelado'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>
        </section>

        <?php if ($ativa && !$cancelada): ?>
        <section class="card-box danger-box">
            <h2><i class="fa-solid fa-ban"></i> Cancelar assinatura mensal</h2>
            <p class="texto">O pagamento é feito por Pix, sem cobrança automática. Ao cancelar, você registra que não vai renovar: seu acesso continua até <strong><?php echo $venc->format('d/m/Y'); ?></strong> e seus dados <strong>não</strong> são apagados. Depois do vencimento o acesso é bloqueado até você pagar de novo.</p>
            <form method="POST" onsubmit="return confirm('Cancelar a renovação da assinatura? Você mantém o acesso até o vencimento.');">
                <input type="hidden" name="csrf" value="<?php echo h($_SESSION['csrf']); ?>">
                <input type="hidden" name="acao" value="cancelar_assinatura">
                <button type="submit" class="btn btn-danger">Cancelar assinatura</button>
            </form>
        </section>
        <?php endif; ?>

        <?php else: ?>
        <!-- ================= AJUDA ================= -->

        <section class="card-box">
            <h2><i class="fa-solid fa-circle-question"></i> Central de Ajuda</h2>
            <p class="subtitle">Dúvidas comuns sobre como o RestControl funciona.</p>

            <div class="faq">
                <h3 class="faq-grupo">De onde vêm os valores e cálculos</h3>

                <details>
                    <summary>De onde vem o total de cada comanda (mesa)?</summary>
                    <p>É a soma de todos os itens que o cliente já finalizou na mesa: <strong>preço do prato × quantidade</strong>. Sempre que o cliente finaliza uma nova rodada de itens, o total da comanda é recalculado. Itens que ainda estão na sacola não entram na conta.</p>
                </details>
                <details>
                    <summary>Se eu mudar o preço de um prato, o que acontece com os pedidos antigos?</summary>
                    <p>Nada. O preço do prato é gravado no momento do pedido, então comandas já feitas mantêm o valor original. O novo preço vale apenas para pedidos novos.</p>
                </details>
                <details>
                    <summary>Como o histórico de pedidos calcula o "Total do dia" e o "Total no período"?</summary>
                    <p>Só entram comandas em que você clicou em <strong>Dar baixa</strong>. Elas são agrupadas pelo dia da baixa. O <strong>total do dia</strong> é a soma das comandas daquele dia; o <strong>total no período</strong> é a soma de tudo o que está sendo exibido (todos os dias, ou apenas o dia filtrado).</p>
                </details>
                <details>
                    <summary>Quando um ingrediente aparece com "repor!" no estoque?</summary>
                    <p>Quando a <strong>quantidade atual</strong> é igual ou menor que a <strong>quantidade mínima</strong> que você cadastrou. Isso vale também na ficha do fornecedor, nos ingredientes vinculados a ele.</p>
                </details>
                <details>
                    <summary>Como é calculado o "Progresso Total" do Kanban?</summary>
                    <p>É o número de tarefas na coluna <strong>Concluída</strong> dividido pelo total de tarefas exibidas, em porcentagem. Se você usar algum filtro, o cálculo considera só as tarefas filtradas.</p>
                </details>

                <h3 class="faq-grupo">Uso do sistema</h3>

                <details>
                    <summary>Como meus clientes fazem pedidos pelo celular?</summary>
                    <p>Em <strong>Cardápio Digital</strong> você gera o QR Code do seu restaurante. O cliente escaneia, informa nome, mesa e telefone, escolhe os pratos e finaliza. O pedido aparece em <strong>Pedidos → Mesas em Aberto</strong>. O pagamento acontece fora do sistema.</p>
                </details>
                <details>
                    <summary>Por que uma mesa aparece como "ativa"?</summary>
                    <p>A mesa fica ativa quando um cliente senta e abre a comanda. Ela volta a ficar inativa quando você dá baixa na comanda ou a desativa manualmente em <strong>Mesas</strong>.</p>
                </details>
                <details>
                    <summary>Para que serve a Ficha Técnica?</summary>
                    <p>Ela registra, para cada prato, quais ingredientes do estoque são usados, em que quantidade e o modo de preparo. Só é possível criar a ficha de pratos que já foram cadastrados.</p>
                </details>
                <details>
                    <summary>Como vinculo um fornecedor ao meu estoque?</summary>
                    <p>Ao cadastrar ou editar um fornecedor, marque os ingredientes do estoque que ele fornece. Assim, na ficha do fornecedor você vê a situação atual de cada um. Para aparecerem na lista, o ingrediente precisa estar cadastrado em <strong>Estoque</strong>.</p>
                </details>

                <h3 class="faq-grupo">Conta e assinatura</h3>

                <details>
                    <summary>Como funciona a assinatura?</summary>
                    <p>O plano custa <?php echo moeda(VALOR_MENSAL); ?> por mês, pago por Pix. Depois que o pagamento é confirmado pelo administrador, o vencimento é estendido em 1 mês. Não há cobrança automática: você renova quando quiser. Quando vence, o acesso fica bloqueado até um novo pagamento.</p>
                </details>
                <details>
                    <summary>Posso pagar vários meses de uma vez?</summary>
                    <p>Sim. Em <strong>Assinatura → Pagar adiantado</strong> escolha o período, faça o Pix e aguarde a confirmação. Cada mês pago soma 1 mês ao vencimento atual.</p>
                </details>
                <details>
                    <summary>O que acontece se eu cancelar a assinatura?</summary>
                    <p>Seu acesso continua até o fim do período já pago e seus dados ficam guardados. Você pode desfazer o cancelamento antes do vencimento ou pagar novamente depois.</p>
                </details>
                <details>
                    <summary>Outros restaurantes conseguem ver meus dados?</summary>
                    <p>Não. Cada conta enxerga apenas os dados do próprio restaurante.</p>
                </details>
                <details>
                    <summary>O que acontece se eu apagar minha conta?</summary>
                    <p>Todos os dados do seu restaurante são apagados de forma definitiva e não é possível recuperá-los. Se só quer parar de pagar, use <strong>Cancelar assinatura</strong>.</p>
                </details>
            </div>
        </section>

        <section class="card-box">
            <h2><i class="fa-solid fa-envelope"></i> Fale com a empresa</h2>
            <p class="subtitle">Não encontrou a resposta aqui? Entre em contato com a gente.</p>
            <p class="texto"><a class="link-contato" href="mailto:<?php echo h(CONTATO_TERMOS); ?>"><i class="fa-solid fa-envelope"></i> <?php echo h(CONTATO_TERMOS); ?></a></p>
        </section>
        <?php endif; ?>
    </div>

    <script src="configuracoes.js"></script>
</body>
</html>

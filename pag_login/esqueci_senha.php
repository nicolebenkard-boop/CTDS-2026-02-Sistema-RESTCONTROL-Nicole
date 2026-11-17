<?php
// esqueci_senha.php - pede o e-mail e envia o link para redefinir a senha
session_start();
require_once '../conexao.php';
require_once __DIR__ . '/enviar_email.php';

header('Cache-Control: no-store');

if (empty($_SESSION['csrf_reset'])) {
    $_SESSION['csrf_reset'] = bin2hex(random_bytes(32));
}

function h($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

$enviado   = false;
$erro      = null;
$aviso_dev = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!hash_equals($_SESSION['csrf_reset'], $_POST['csrf'] ?? '')) {
        $erro = 'Sessão expirada. Recarregue a página e tente de novo.';
    } else {
        $email = trim($_POST['email'] ?? '');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 255) {
            $erro = 'Digite um e-mail válido.';
        } else {
            $ip = substr($_SERVER['REMOTE_ADDR'] ?? '', 0, 45);

            // Limites contra abuso: no máximo 10 pedidos por IP e 3 por conta, por hora
            $st = $pdo->prepare("SELECT COUNT(*) FROM tb_recuperacao_senha WHERE ip = :ip AND criado_em > (NOW() - INTERVAL 1 HOUR)");
            $st->execute([':ip' => $ip]);
            $excedeu_ip = (int)$st->fetchColumn() >= 10;

            $st = $pdo->prepare("SELECT id_gestor, nome_gestor, e_mail FROM tb_gestor WHERE e_mail = :e LIMIT 1");
            $st->execute([':e' => $email]);
            $gestor = $st->fetch(PDO::FETCH_ASSOC);

            if ($gestor && !$excedeu_ip) {
                $st = $pdo->prepare("SELECT COUNT(*) FROM tb_recuperacao_senha WHERE id_gestor = :g AND criado_em > (NOW() - INTERVAL 1 HOUR)");
                $st->execute([':g' => $gestor['id_gestor']]);
                $excedeu_conta = (int)$st->fetchColumn() >= 3;

                if (!$excedeu_conta) {
                    // Gera o token: o link leva o token puro; no banco fica só o hash dele
                    $token = bin2hex(random_bytes(32));
                    $hash  = hash('sha256', $token);

                    $pdo->beginTransaction();
                    // links anteriores deixam de valer
                    $pdo->prepare("UPDATE tb_recuperacao_senha SET usado = 1 WHERE id_gestor = :g AND usado = 0")
                        ->execute([':g' => $gestor['id_gestor']]);
                    $pdo->prepare("INSERT INTO tb_recuperacao_senha (id_gestor, token_hash, expira_em, ip)
                                   VALUES (:g, :t, NOW() + INTERVAL " . (int)RESET_VALIDADE_MIN . " MINUTE, :ip)")
                        ->execute([':g' => $gestor['id_gestor'], ':t' => $hash, ':ip' => $ip]);
                    $pdo->commit();

                    $link = url_base_login() . '/redefinir_senha.php?token=' . $token;
                    $nome = $gestor['nome_gestor'];
                    $min  = (int)RESET_VALIDADE_MIN;

                    $html = '<div style="font-family:Segoe UI,Arial,sans-serif;max-width:520px;margin:auto;color:#222">'
                          . '<h2 style="color:#780c0c">RestControl</h2>'
                          . '<p>Olá, ' . h($nome) . '!</p>'
                          . '<p>Recebemos um pedido para redefinir a senha da sua conta. Clique no botão abaixo para criar uma nova senha:</p>'
                          . '<p style="text-align:center;margin:28px 0"><a href="' . h($link) . '" style="background:#780c0c;color:#fff;padding:12px 26px;border-radius:24px;text-decoration:none;font-weight:600">Redefinir minha senha</a></p>'
                          . '<p style="font-size:13px;color:#555">Se o botão não funcionar, copie e cole este endereço no navegador:<br><a href="' . h($link) . '">' . h($link) . '</a></p>'
                          . '<p style="font-size:13px;color:#555">Este link vale por ' . $min . ' minutos e só pode ser usado uma vez. Se você não pediu a redefinição, ignore este e-mail: sua senha continua a mesma.</p>'
                          . '</div>';
                    $texto = "Olá, {$nome}!\n\nRecebemos um pedido para redefinir a senha da sua conta no RestControl.\n"
                           . "Acesse o link abaixo para criar uma nova senha (vale por {$min} minutos e só pode ser usado uma vez):\n\n{$link}\n\n"
                           . "Se você não pediu a redefinição, ignore este e-mail: sua senha continua a mesma.";

                    $ok = enviar_email($gestor['e_mail'], $nome, 'RestControl - Redefinição de senha', $html, $texto);
                    if (!$ok && acesso_local()) {
                        $aviso_dev = 'Aviso para quem está testando (só aparece em localhost): o e-mail não pôde ser enviado. Confira pag_login/config_email.php.';
                    } elseif ($ok && MAIL_MODO === 'arquivo' && acesso_local()) {
                        $aviso_dev = 'Modo de teste: nenhum e-mail real foi enviado. A mensagem foi salva na pasta pag_login/_emails/ (abra o arquivo .txt para pegar o link).';
                    }
                }
            }

            // Resposta sempre igual, exista a conta ou não (não revela quem tem cadastro)
            $enviado = true;
            $_SESSION['csrf_reset'] = bin2hex(random_bytes(32));
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RestControl - Recuperar senha</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <div class="login-container">
        <div class="login-banner">
            <div class="banner-content">
                <img src="logo.png" alt="RestControl Logo" class="logo-img">
                <h2>Esqueceu a senha?</h2>
                <p>Sem problemas. Informe o e-mail do seu cadastro e enviaremos um link para você criar uma nova senha.</p>
            </div>
        </div>

        <div class="login-form-wrapper">
            <div class="form-box">
                <h2>Recuperar senha</h2>

                <?php if ($enviado): ?>
                    <div class="alert alert-ok">
                        <i class="fa-solid fa-circle-check"></i>
                        Se existir uma conta com esse e-mail, enviamos um link para redefinir a senha. Ele vale por <?php echo (int)RESET_VALIDADE_MIN; ?> minutos. Confira também a caixa de spam.
                    </div>
                    <?php if ($aviso_dev): ?>
                        <div class="alert alert-aviso"><?php echo h($aviso_dev); ?></div>
                    <?php endif; ?>
                    <div class="button-group">
                        <a href="index.php" class="btn btn-primary">Voltar ao login</a>
                    </div>
                <?php else: ?>
                    <p class="subtitle">Digite o e-mail que você usou no cadastro</p>

                    <?php if ($erro): ?>
                        <div class="alert alert-erro"><i class="fa-solid fa-circle-exclamation"></i> <?php echo h($erro); ?></div>
                    <?php endif; ?>

                    <form action="esqueci_senha.php" method="POST">
                        <input type="hidden" name="csrf" value="<?php echo h($_SESSION['csrf_reset']); ?>">
                        <div class="input-group">
                            <label for="email">E-mail</label>
                            <div class="input-field">
                                <i class="fa-regular fa-envelope"></i>
                                <input type="email" id="email" name="email" placeholder="Ex: gestor@restcontrol.com" maxlength="255" required autofocus>
                            </div>
                        </div>

                        <div class="button-group">
                            <button type="submit" class="btn btn-primary">Enviar link</button>
                            <a href="index.php" class="btn btn-secondary">Voltar</a>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

</body>
</html>

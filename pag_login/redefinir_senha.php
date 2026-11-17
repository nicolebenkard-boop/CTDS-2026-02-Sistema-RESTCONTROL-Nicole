<?php
// redefinir_senha.php - página aberta pelo link do e-mail: define a nova senha
session_start();
require_once '../conexao.php';
require_once __DIR__ . '/enviar_email.php';

// O token está na URL: não deixa ele vazar por cache nem por Referer (ex.: CDN de ícones)
header('Cache-Control: no-store');
header('Referrer-Policy: no-referrer');

if (empty($_SESSION['csrf_reset'])) {
    $_SESSION['csrf_reset'] = bin2hex(random_bytes(32));
}

function h($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

$token = $_POST['token'] ?? $_GET['token'] ?? '';
$token = is_string($token) ? $token : '';

$registro = null; // linha de tb_recuperacao_senha válida
$gestor   = null;
if (preg_match('/^[a-f0-9]{64}$/', $token)) {
    $st = $pdo->prepare("SELECT r.id_recuperacao, r.id_gestor, g.nome_gestor, g.e_mail
                         FROM tb_recuperacao_senha r
                         INNER JOIN tb_gestor g ON g.id_gestor = r.id_gestor
                         WHERE r.token_hash = :t AND r.usado = 0 AND r.expira_em > NOW()
                         LIMIT 1");
    $st->execute([':t' => hash('sha256', $token)]);
    $registro = $st->fetch(PDO::FETCH_ASSOC) ?: null;
}

$erro = null;

if ($registro && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $nova = $_POST['nova_senha'] ?? '';
    $conf = $_POST['confirmar_senha'] ?? '';

    if (!hash_equals($_SESSION['csrf_reset'], $_POST['csrf'] ?? '')) {
        $erro = 'Sessão expirada. Recarregue a página e tente de novo.';
    } elseif (strlen($nova) < 8) {
        $erro = 'A nova senha deve ter pelo menos 8 caracteres.';
    } elseif (strlen($nova) > 72) {
        $erro = 'A senha pode ter no máximo 72 caracteres.'; // limite do bcrypt
    } elseif ($nova !== $conf) {
        $erro = 'A confirmação não é igual à nova senha.';
    } else {
        try {
            $pdo->beginTransaction();

            // Marca como usado primeiro; se outra aba já usou, não altera nada
            $upd = $pdo->prepare("UPDATE tb_recuperacao_senha SET usado = 1 WHERE id_recuperacao = :id AND usado = 0");
            $upd->execute([':id' => $registro['id_recuperacao']]);

            if ($upd->rowCount() === 1) {
                $pdo->prepare("UPDATE tb_gestor SET senha = :s WHERE id_gestor = :g")
                    ->execute([':s' => password_hash($nova, PASSWORD_DEFAULT), ':g' => $registro['id_gestor']]);
                // qualquer outro link pendente dessa conta também morre
                $pdo->prepare("UPDATE tb_recuperacao_senha SET usado = 1 WHERE id_gestor = :g")
                    ->execute([':g' => $registro['id_gestor']]);
                $pdo->commit();
            } else {
                $pdo->rollBack();
                header('Location: redefinir_senha.php?token=' . urlencode($token));
                exit();
            }
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            error_log('[RestControl] Erro ao redefinir senha: ' . $e->getMessage());
            $erro = 'Não foi possível salvar a nova senha. Tente novamente.';
        }

        if ($erro === null) {
            // Aviso de segurança por e-mail (se falhar, não atrapalha)
            $txt = "Olá, {$registro['nome_gestor']}!\n\nA senha da sua conta no RestControl acabou de ser alterada.\n"
                 . "Se foi você, não precisa fazer nada. Se não foi, peça uma nova redefinição pela tela de login e entre em contato com o suporte.";
            enviar_email($registro['e_mail'], $registro['nome_gestor'], 'RestControl - Sua senha foi alterada',
                '<div style="font-family:Segoe UI,Arial,sans-serif;max-width:520px;margin:auto;color:#222"><h2 style="color:#780c0c">RestControl</h2><p>'
                . nl2br(h($txt)) . '</p></div>', $txt);

            $_SESSION['csrf_reset'] = bin2hex(random_bytes(32));
            header('Location: index.php?msg=senha_redefinida');
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="referrer" content="no-referrer">
    <title>RestControl - Nova senha</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <div class="login-container">
        <div class="login-banner">
            <div class="banner-content">
                <img src="logo.png" alt="RestControl Logo" class="logo-img">
                <h2>Nova senha</h2>
                <p>Crie uma senha forte, com pelo menos 8 caracteres, para proteger o seu restaurante.</p>
            </div>
        </div>

        <div class="login-form-wrapper">
            <div class="form-box">
                <?php if (!$registro): ?>
                    <h2>Link inválido</h2>
                    <div class="alert alert-erro">
                        <i class="fa-solid fa-circle-exclamation"></i>
                        Este link é inválido, já foi usado ou expirou. Peça um novo para continuar.
                    </div>
                    <div class="button-group">
                        <a href="esqueci_senha.php" class="btn btn-primary">Pedir novo link</a>
                        <a href="index.php" class="btn btn-secondary">Login</a>
                    </div>
                <?php else: ?>
                    <h2>Redefinir senha</h2>
                    <p class="subtitle">Conta: <?php echo h($registro['e_mail']); ?></p>

                    <?php if ($erro): ?>
                        <div class="alert alert-erro"><i class="fa-solid fa-circle-exclamation"></i> <?php echo h($erro); ?></div>
                    <?php endif; ?>

                    <form action="redefinir_senha.php" method="POST" autocomplete="off">
                        <input type="hidden" name="token" value="<?php echo h($token); ?>">
                        <input type="hidden" name="csrf" value="<?php echo h($_SESSION['csrf_reset']); ?>">

                        <div class="input-group">
                            <label for="nova_senha">Nova senha</label>
                            <div class="input-field">
                                <i class="fa-solid fa-lock"></i>
                                <input type="password" id="nova_senha" name="nova_senha" placeholder="Mínimo 8 caracteres" minlength="8" maxlength="72" required autofocus>
                                <i class="fa-regular fa-eye toggle-senha" data-alvo="nova_senha" style="cursor:pointer;"></i>
                            </div>
                        </div>

                        <div class="input-group">
                            <label for="confirmar_senha">Confirmar nova senha</label>
                            <div class="input-field">
                                <i class="fa-solid fa-lock"></i>
                                <input type="password" id="confirmar_senha" name="confirmar_senha" placeholder="Repita a senha" minlength="8" maxlength="72" required>
                                <i class="fa-regular fa-eye toggle-senha" data-alvo="confirmar_senha" style="cursor:pointer;"></i>
                            </div>
                        </div>

                        <div class="button-group">
                            <button type="submit" class="btn btn-primary">Salvar nova senha</button>
                            <a href="index.php" class="btn btn-secondary">Cancelar</a>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('.toggle-senha').forEach(function (olho) {
            olho.addEventListener('click', function () {
                var campo = document.getElementById(this.dataset.alvo);
                campo.type = campo.type === 'password' ? 'text' : 'password';
                this.classList.toggle('fa-eye');
                this.classList.toggle('fa-eye-slash');
            });
        });
    </script>
</body>
</html>

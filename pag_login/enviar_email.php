<?php
// enviar_email.php - função única de envio de e-mail do sistema
require_once __DIR__ . '/config_email.php';

/** True se quem está acessando é o próprio computador (localhost). */
function acesso_local(): bool {
    if (PHP_SAPI === 'cli') return true;
    return in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true);
}

/**
 * Envia um e-mail (HTML + texto puro). Retorna true se foi enviado/gravado.
 * Nunca lança exceção: em caso de erro, registra no log do PHP e devolve false.
 */
function enviar_email(string $para, string $nome, string $assunto, string $html, string $texto): bool {

    // ---- Modo de teste: grava em arquivo (somente em localhost) ----
    if (MAIL_MODO === 'arquivo') {
        if (!acesso_local()) {
            error_log('[RestControl] MAIL_MODO=arquivo só funciona em localhost. Configure o SMTP em pag_login/config_email.php');
            return false;
        }
        $dir = __DIR__ . '/_emails';
        if (!is_dir($dir) && !@mkdir($dir, 0755, true)) return false;
        if (!file_exists($dir . '/.htaccess')) {
            @file_put_contents($dir . '/.htaccess', "Require all denied\n<IfModule !mod_authz_core.c>\nDeny from all\n</IfModule>\n");
        }
        $arquivo = $dir . '/' . date('Ymd_His') . '_' . bin2hex(random_bytes(3)) . '.txt';
        $conteudo = "Para: {$nome} <{$para}>\nAssunto: {$assunto}\n\n{$texto}\n";
        return @file_put_contents($arquivo, $conteudo) !== false;
    }

    // ---- Envio real por SMTP (PHPMailer) ----
    if (SMTP_USUARIO === '' || SMTP_SENHA === '') {
        error_log('[RestControl] SMTP sem usuário/senha em pag_login/config_email.php');
        return false;
    }

    require_once __DIR__ . '/lib/phpmailer/Exception.php';
    require_once __DIR__ . '/lib/phpmailer/PHPMailer.php';
    require_once __DIR__ . '/lib/phpmailer/SMTP.php';

    $m = new PHPMailer\PHPMailer\PHPMailer(true);
    try {
        $m->isSMTP();
        $m->Host     = SMTP_HOST;
        $m->Port     = SMTP_PORTA;
        $m->SMTPAuth = true;
        $m->Username = SMTP_USUARIO;
        $m->Password = SMTP_SENHA;
        if (SMTP_SEGURANCA === 'tls') {
            $m->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        } elseif (SMTP_SEGURANCA === 'ssl') {
            $m->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
        } else {
            $m->SMTPSecure  = '';
            $m->SMTPAutoTLS = false;
        }
        $m->Timeout = 15;
        $m->CharSet = 'UTF-8';
        $m->setFrom(MAIL_REMETENTE_EMAIL ?: SMTP_USUARIO, MAIL_REMETENTE_NOME);
        $m->addAddress($para, $nome);
        $m->isHTML(true);
        $m->Subject = $assunto;
        $m->Body    = $html;
        $m->AltBody = $texto;
        $m->send();
        return true;
    } catch (Throwable $e) {
        error_log('[RestControl] Falha ao enviar e-mail: ' . $e->getMessage());
        return false;
    }
}

/** Endereço base do sistema (até a pasta pag_login), sem barra no final. */
function url_base_login(): string {
    if (APP_URL_BASE !== '') {
        return rtrim(APP_URL_BASE, '/') . '/pag_login';
    }
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    if (!preg_match('/^[A-Za-z0-9.\-]+(:\d{1,5})?$/', $host)) $host = 'localhost'; // evita Host manipulado
    $https  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['SERVER_PORT'] ?? '') == 443);
    $pasta  = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/pag_login/x.php')), '/');
    return ($https ? 'https' : 'http') . '://' . $host . $pasta;
}

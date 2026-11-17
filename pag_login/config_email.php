<?php
// =====================================================================
// config_email.php - configuração do envio de e-mails (recuperar senha)
// =====================================================================
//
// MODO DE ENVIO
//   'smtp'    -> envia e-mail de verdade (use no site publicado e para testar de verdade)
//   'arquivo' -> NÃO envia: salva o e-mail na pasta pag_login/_emails/ (só funciona
//                no seu computador, em localhost). Bom para testar sem configurar nada.
define('MAIL_MODO', 'arquivo');

// ---------------------------------------------------------------------
// DADOS DO SMTP (só usados quando MAIL_MODO = 'smtp')
//
// Exemplo com Gmail (recomendado para o TCC):
//   1) Crie/use uma conta Gmail só para o sistema (ex.: restcontrol.suporte@gmail.com)
//   2) Ative a "Verificação em duas etapas" na conta Google
//   3) Em https://myaccount.google.com/apppasswords gere uma "Senha de app" (16 letras)
//   4) Preencha abaixo: usuário = o e-mail do Gmail, senha = a senha de app (NÃO a senha normal)
// ---------------------------------------------------------------------
define('SMTP_HOST',      'smtp.gmail.com');
define('SMTP_PORTA',     587);
define('SMTP_SEGURANCA', 'tls');   // 'tls' (porta 587), 'ssl' (porta 465) ou 'none'
define('SMTP_USUARIO',   '');      // ex.: 'restcontrol.suporte@gmail.com'
define('SMTP_SENHA',     '');      // senha de app do Gmail

// Quem aparece como remetente (no Gmail precisa ser o mesmo e-mail do SMTP_USUARIO)
define('MAIL_REMETENTE_EMAIL', SMTP_USUARIO);
define('MAIL_REMETENTE_NOME',  'RestControl');

// ---------------------------------------------------------------------
// ENDEREÇO DO SISTEMA usado no link do e-mail (sem barra no final).
// Deixe vazio para descobrir sozinho (ok no XAMPP).
// No site publicado, preencha. Ex.: 'https://meusite.com.br/TCC_RESTAURANTE'
// ---------------------------------------------------------------------
define('APP_URL_BASE', '');

// Por quanto tempo o link de redefinição vale (minutos)
define('RESET_VALIDADE_MIN', 30);

<?php
// Funções compartilhadas do módulo Fornecedores.

function h($valor) {
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}

/** Lê e valida os campos do formulário. Devolve [dados, erro|null]. */
function ler_campos_fornecedor(): array {
    $dados = [
        'nome_fornecedor'   => trim($_POST['nome_fornecedor'] ?? ''),
        'telefone'          => trim($_POST['telefone'] ?? ''),
        'email'             => trim($_POST['email'] ?? ''),
        'produto_fornecido' => trim($_POST['produto_fornecido'] ?? ''),
    ];

    $digitos = preg_replace('/\D/', '', $dados['telefone']);

    if ($dados['nome_fornecedor'] === '') {
        return [$dados, 'Informe o nome do fornecedor.'];
    }
    if (strlen($digitos) < 10 || strlen($digitos) > 11) {
        return [$dados, 'Informe um telefone válido com DDD (10 ou 11 dígitos).'];
    }
    if ($dados['email'] !== '' && !filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
        return [$dados, 'O e-mail informado não é válido.'];
    }
    return [$dados, null];
}

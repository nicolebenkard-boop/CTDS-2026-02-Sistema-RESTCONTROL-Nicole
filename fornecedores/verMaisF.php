<?php
session_start();
require_once '../conexao.php'; // deve fornecer $pdo (PDO), igual ao login
require_once '_helpers.php';
require_once '../estoque/helpers.php';

// Bloqueia acesso de quem não está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../pag_login/index.php");
    exit();
}

$id_gestor = $_SESSION['usuario_id']; // mesma chave usada no login_processa.php

// Mantém o estoque mínimo/máximo (calculado pelo mês anterior) em dia antes de mostrar "repor!"
if (estoque_tem_colunas_reposicao($pdo)) {
    atualizar_minimo_maximo($pdo, (int)$id_gestor);
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: fornecedores.php");
    exit();
}

$id_fornecedor = intval($_GET['id']);

// Busca os dados filtrando pelo ID selecionado E pelo gestor logado
$stmt = $pdo->prepare("SELECT * FROM tb_fornecedores WHERE id_fornecedor = :id AND id_gestor = :id_gestor");
$stmt->execute([
    ':id'        => $id_fornecedor,
    ':id_gestor' => $id_gestor,
]);
$fornecedor = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$fornecedor) {
    header("Location: fornecedores.php");
    exit();
}

// Ingredientes do estoque vinculados a este fornecedor (com a situação atual do estoque)
$stmt = $pdo->prepare("
    SELECT e.nome_ingredientes, e.quantidade_atual, e.quantidade_minima
    FROM tb_fornecedor_estoque fe
    INNER JOIN tb_estoque e ON e.id_ingrediente = fe.id_ingrediente
    WHERE fe.id_fornecedor = :f AND e.id_gestor = :g
    ORDER BY e.nome_ingredientes ASC
");
$stmt->execute([':f' => $id_fornecedor, ':g' => $id_gestor]);
$ingredientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$tel_digitos = preg_replace('/\D/', '', $fornecedor['telefone']);
$tel_whats   = (strlen($tel_digitos) <= 11) ? '55' . $tel_digitos : $tel_digitos;
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RestControl - Ficha do Fornecedor</title>
    <link rel="stylesheet" href="verMaisF.css">
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
            <a href="fornecedores.php" class="top-link"><i class="fa-solid fa-list"></i> Voltar</a>
        </div>
    </header>

    <div class="details-container">
        <main class="card-box details-card">
            <div class="card-header">
                <div class="user-avatar">
                    <i class="fa-solid fa-boxes-packing"></i>
                </div>
                <h2><?php echo h($fornecedor['nome_fornecedor']); ?></h2>
                <p class="role-badge">Registro Identificador: #<?php echo $fornecedor['id_fornecedor']; ?></p>
            </div>

            <hr class="divider">

            <div class="details-grid">
                <div class="detail-item">
                    <label><i class="fa-solid fa-truck"></i> Nome do Fornecedor</label>
                    <p><?php echo h($fornecedor['nome_fornecedor']); ?></p>
                </div>

                <div class="detail-item">
                    <label><i class="fa-solid fa-phone"></i> Telefone / WhatsApp</label>
                    <?php if ($fornecedor['telefone'] !== ''): ?>
                        <p>
                            <a class="contact-link" href="tel:<?php echo h($tel_digitos); ?>"><?php echo h($fornecedor['telefone']); ?></a>
                            <a class="whats-link" href="https://wa.me/<?php echo h($tel_whats); ?>" target="_blank" rel="noopener" title="Abrir no WhatsApp"><i class="fa-brands fa-whatsapp"></i></a>
                        </p>
                    <?php else: ?>
                        <p class="muted">Não informado</p>
                    <?php endif; ?>
                </div>

                <div class="detail-item">
                    <label><i class="fa-solid fa-envelope"></i> E-mail</label>
                    <?php if ($fornecedor['email'] !== ''): ?>
                        <p><a class="contact-link" href="mailto:<?php echo h($fornecedor['email']); ?>"><?php echo h($fornecedor['email']); ?></a></p>
                    <?php else: ?>
                        <p class="muted">Não informado</p>
                    <?php endif; ?>
                </div>

                <?php if ($fornecedor['produto_fornecido'] !== ''): ?>
                <div class="detail-item">
                    <label><i class="fa-solid fa-box"></i> Outros produtos / observações</label>
                    <p><?php echo h($fornecedor['produto_fornecido']); ?></p>
                </div>
                <?php endif; ?>

                <div class="detail-item full-width">
                    <label><i class="fa-solid fa-boxes-stacked"></i> Ingredientes que ele fornece (vinculados no Estoque)</label>
                    <?php if ($ingredientes): ?>
                        <ul class="vinc-list">
                            <?php foreach ($ingredientes as $ing): ?>
                                <?php $repor = (float)$ing['quantidade_atual'] <= (float)$ing['quantidade_minima']; ?>
                                <li>
                                    <span><?php echo h($ing['nome_ingredientes']); ?></span>
                                    <span class="<?php echo $repor ? 'repor' : ''; ?>">
                                        Estoque: <?php echo (int)$ing['quantidade_atual']; ?>
                                        <?php if ($repor): ?> — repor!<?php endif; ?>
                                    </span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="muted">Nenhum ingrediente vinculado ainda. Escolha este fornecedor na tela de Estoque.</p>
                    <?php endif; ?>
                </div>
            </div>

            <hr class="divider">

            <div class="card-actions">
                <a href="fornecedores.php" class="btn btn-secondary">Voltar à Lista</a>
                <a href="editarF.php?id=<?php echo (int)$fornecedor['id_fornecedor']; ?>" class="btn btn-secondary"><i class="fa-solid fa-pen"></i> Editar</a>
                <a href="fornecedores.php?excluir=<?php echo $fornecedor['id_fornecedor']; ?>" 
                    class="btn btn-danger"
                    onclick="return confirm(<?php echo h(json_encode('Tem certeza que deseja remover o fornecedor ' . $fornecedor['nome_fornecedor'] . '?')); ?>)">
                    <i class="fa-solid fa-trash-can"></i> Deletar Registro
                </a>
            </div>
        </main>
    </div>

</body>
</html>

<?php
session_start();
require_once '../conexao.php';
require_once '_helpers.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../pag_login/index.php");
    exit();
}

$id_gestor = (int)$_SESSION['usuario_id'];
$id_fornecedor = intval($_GET['id'] ?? $_POST['id_fornecedor'] ?? 0);

// Só carrega se o fornecedor for DESTE gestor
$stmt = $pdo->prepare("SELECT * FROM tb_fornecedores WHERE id_fornecedor = :id AND id_gestor = :g");
$stmt->execute([':id' => $id_fornecedor, ':g' => $id_gestor]);
$fornecedor = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$fornecedor) {
    header("Location: fornecedores.php");
    exit();
}

$erro = null;
$f = $fornecedor;
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['salvar'])) {
    [$f, $erro] = ler_campos_fornecedor();

    if (!$erro) {
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("UPDATE tb_fornecedores
                                   SET nome_fornecedor = :nome, produto_fornecido = :produto, telefone = :telefone, email = :email
                                   WHERE id_fornecedor = :id AND id_gestor = :g");
            $stmt->execute([
                ':nome'     => $f['nome_fornecedor'],
                ':produto'  => $f['produto_fornecido'],
                ':telefone' => $f['telefone'],
                ':email'    => $f['email'],
                ':id'       => $id_fornecedor,
                ':g'        => $id_gestor,
            ]);
            $pdo->commit();

            header("Location: verMaisF.php?id=" . $id_fornecedor);
            exit();
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $erro = "Erro ao salvar as alterações.";
        }
    }
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RestControl - Editar Fornecedor</title>
    <link rel="stylesheet" href="fornecedores.css">
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
            <a href="verMaisF.php?id=<?php echo $id_fornecedor; ?>" class="top-link"><i class="fa-solid fa-list"></i> Voltar</a>
        </div>
    </header>

    <div class="container container-single">
        <section class="card-box form-section">
            <h2><i class="fa-solid fa-pen"></i> Editar Fornecedor</h2>
            <p class="subtitle">Atualize os dados e o contato do fornecedor</p>

            <?php if ($erro): ?>
                <div class="alert-erro"><?php echo h($erro); ?></div>
            <?php endif; ?>

            <form action="editarF.php?id=<?php echo $id_fornecedor; ?>" method="POST" class="form-grid">
                <input type="hidden" name="id_fornecedor" value="<?php echo $id_fornecedor; ?>">
                <?php include '_campos.php'; ?>

                <div class="button-container">
                    <button type="submit" name="salvar" class="btn btn-primary">Salvar Alterações</button>
                </div>
            </form>
        </section>
    </div>

</body>
</html>

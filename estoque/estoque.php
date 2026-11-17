<?php
// estoque.php - Módulo de Estoque RestControl
session_start();
require_once '../conexao.php'; // deve fornecer $pdo (PDO), igual ao login
require_once 'helpers.php';   // converter_unidade() e cálculo automático de estoque mínimo/máximo

// Bloqueia acesso de quem não está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../pag_login/index.php");
    exit();
}

$id_gestor = $_SESSION['usuario_id'];

// Precisa do SQL que cria o tempo e o lote de reposição
if (!estoque_tem_colunas_reposicao($pdo)) {
    die('Falta rodar o arquivo <b>sql/alteracoes_estoque_reposicao.sql</b> no phpMyAdmin (aba SQL do banco restaurante).');
}

/** Unidades de medida aceitas (as mesmas usadas na Ficha Técnica). */
$UNIDADES_VALIDAS = ['g', 'kg', 'ml', 'l', 'un', 'dz'];

/** Devolve só os ids de fornecedores que realmente pertencem a este gestor. */
function ids_fornecedores_validos(PDO $pdo, int $id_gestor, $ids): array {
    if (!is_array($ids)) return [];
    $ids = array_values(array_unique(array_filter(array_map('intval', $ids), fn($i) => $i > 0)));
    if (!$ids) return [];
    $marcadores = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT id_fornecedor FROM tb_fornecedores WHERE id_gestor = ? AND id_fornecedor IN ($marcadores)");
    $stmt->execute(array_merge([$id_gestor], $ids));
    return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
}

/** Substitui os fornecedores vinculados a um ingrediente. */
function salvar_fornecedores_do_ingrediente(PDO $pdo, int $id_ingrediente, array $ids_fornecedores): void {
    $pdo->prepare("DELETE FROM tb_fornecedor_estoque WHERE id_ingrediente = :i")->execute([':i' => $id_ingrediente]);
    $ins = $pdo->prepare("INSERT INTO tb_fornecedor_estoque (id_fornecedor, id_ingrediente) VALUES (:f, :i)");
    foreach ($ids_fornecedores as $id_forn) {
        $ins->execute([':f' => $id_forn, ':i' => $id_ingrediente]);
    }
}

/** Escapa texto para HTML. */
function h_est($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

/** Formata uma quantidade decimal sem zeros à direita desnecessários (ex: 100 em vez de 100,000). */
function formatar_qtd($valor): string {
    return rtrim(rtrim(number_format((float)$valor, 3, ',', '.'), '0'), ',');
}

// LÓGICA DE CADASTRO
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['adicionar_ingrediente'])) {
    $nome_ingredientes = trim($_POST['nome_ingredientes']);
    $quantidade_atual  = floatval(str_replace(',', '.', $_POST['quantidade_atual']));
    $tempo_reposicao   = max(1, min(365, intval($_POST['tempo_reposicao_dias'] ?? 1)));
    $lote_reposicao    = max(0, floatval(str_replace(',', '.', $_POST['lote_reposicao'] ?? '0')));
    $custo_unitario    = floatval(str_replace(',', '.', $_POST['custo_unitario']));
    $unidade_medida    = trim($_POST['unidade_medida'] ?? 'un');
    if (!in_array($unidade_medida, $UNIDADES_VALIDAS, true)) {
        $unidade_medida = 'un';
    }

    $fornecedores_escolhidos = ids_fornecedores_validos($pdo, $id_gestor, $_POST['fornecedores'] ?? []);

    try {
        $pdo->beginTransaction();
        // Mínimo e máximo são calculados automaticamente (atualizar_minimo_maximo); começam com 0 e com o lote.
        $stmt = $pdo->prepare("INSERT INTO tb_estoque (id_gestor, nome_ingredientes, quantidade_atual, quantidade_minima, quantidade_maxima, unidade_medida, custo_unitario, tempo_reposicao_dias, lote_reposicao)
                                VALUES (:id_gestor, :nome, :qatual, 0, :qmax, :unidade, :custo, :tempo, :lote)");
        $stmt->execute([
            ':id_gestor' => $id_gestor,
            ':nome'      => $nome_ingredientes,
            ':qatual'    => $quantidade_atual,
            ':qmax'      => $lote_reposicao,
            ':tempo'     => $tempo_reposicao,
            ':lote'      => $lote_reposicao,
            ':unidade'   => $unidade_medida,
            ':custo'     => $custo_unitario,
        ]);
        salvar_fornecedores_do_ingrediente($pdo, (int)$pdo->lastInsertId(), $fornecedores_escolhidos);
        $pdo->commit();
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
    }

    header("Location: estoque.php");
    exit();
}

// TEMPO DE REPOSIÇÃO E LOTE DE UM INGREDIENTE (o mínimo e o máximo são recalculados abaixo)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['salvar_reposicao'])) {
    $id_ingrediente  = intval($_POST['id_ingrediente']);
    $tempo_reposicao = max(1, min(365, intval($_POST['tempo_reposicao_dias'] ?? 1)));
    $lote_reposicao  = max(0, floatval(str_replace(',', '.', $_POST['lote_reposicao'] ?? '0')));

    $stmt = $pdo->prepare("UPDATE tb_estoque SET tempo_reposicao_dias = :t, lote_reposicao = :l WHERE id_ingrediente = :i AND id_gestor = :g");
    $stmt->execute([':t' => $tempo_reposicao, ':l' => $lote_reposicao, ':i' => $id_ingrediente, ':g' => $id_gestor]);

    header("Location: estoque.php");
    exit();
}

// REPOSIÇÃO DE ESTOQUE (chegou mercadoria nova: soma na quantidade atual)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['repor_estoque'])) {
    $id_ingrediente = intval($_POST['id_ingrediente']);
    $quantidade_repor = floatval(str_replace(',', '.', $_POST['quantidade_repor'] ?? '0'));

    if ($quantidade_repor > 0) {
        $stmt = $pdo->prepare("UPDATE tb_estoque SET quantidade_atual = quantidade_atual + :qtd WHERE id_ingrediente = :id AND id_gestor = :id_gestor");
        $stmt->execute([':qtd' => $quantidade_repor, ':id' => $id_ingrediente, ':id_gestor' => $id_gestor]);
    }

    header("Location: estoque.php");
    exit();
}

// VINCULAR FORNECEDORES A UM INGREDIENTE JÁ CADASTRADO
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['salvar_fornecedores'])) {
    $id_ingrediente = intval($_POST['id_ingrediente']);

    // Só mexe se o ingrediente for deste gestor
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM tb_estoque WHERE id_ingrediente = :i AND id_gestor = :g");
    $stmt->execute([':i' => $id_ingrediente, ':g' => $id_gestor]);

    if ($stmt->fetchColumn() > 0) {
        $escolhidos = ids_fornecedores_validos($pdo, $id_gestor, $_POST['fornecedores'] ?? []);
        try {
            $pdo->beginTransaction();
            salvar_fornecedores_do_ingrediente($pdo, $id_ingrediente, $escolhidos);
            $pdo->commit();
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
        }
    }

    header("Location: estoque.php");
    exit();
}

// LÓGICA DE EXCLUSÃO (só permite excluir ingrediente do próprio gestor)
if (isset($_GET['excluir'])) {
    $id_excluir = intval($_GET['excluir']);

    $stmt = $pdo->prepare("DELETE FROM tb_estoque WHERE id_ingrediente = :id AND id_gestor = :id_gestor");
    $stmt->execute([
        ':id'        => $id_excluir,
        ':id_gestor' => $id_gestor,
    ]);

    header("Location: estoque.php");
    exit();
}

// Recalcula mínimo e máximo de todos os ingredientes com base no mês anterior
$calc = atualizar_minimo_maximo($pdo, $id_gestor);
$mes_base = $calc['periodo']['rotulo'];

// BUSCA SOMENTE dos ingredientes do gestor logado
$stmt = $pdo->prepare("SELECT * FROM tb_estoque WHERE id_gestor = :id_gestor ORDER BY nome_ingredientes ASC");
$stmt->execute([':id_gestor' => $id_gestor]);
$resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fornecedores cadastrados (para as caixinhas de escolha)
$stmt = $pdo->prepare("SELECT id_fornecedor, nome_fornecedor FROM tb_fornecedores WHERE id_gestor = :id_gestor ORDER BY nome_fornecedor ASC");
$stmt->execute([':id_gestor' => $id_gestor]);
$fornecedores = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fornecedores já vinculados a cada ingrediente
$stmt = $pdo->prepare("
    SELECT fe.id_ingrediente, f.id_fornecedor, f.nome_fornecedor
    FROM tb_fornecedor_estoque fe
    INNER JOIN tb_fornecedores f ON f.id_fornecedor = fe.id_fornecedor
    INNER JOIN tb_estoque e ON e.id_ingrediente = fe.id_ingrediente
    WHERE e.id_gestor = :g AND f.id_gestor = :g2
    ORDER BY f.nome_fornecedor ASC
");
$stmt->execute([':g' => $id_gestor, ':g2' => $id_gestor]);
$vinc_por_ingrediente = [];
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $v) {
    $vinc_por_ingrediente[$v['id_ingrediente']][(int)$v['id_fornecedor']] = $v['nome_fornecedor'];
}

/** Caixinhas de fornecedores (usadas no cadastro e na edição de cada ingrediente). */
function chips_fornecedores(array $fornecedores, array $marcados = []): void {
    if (!$fornecedores) {
        echo '<p class="hint">Nenhum fornecedor cadastrado. <a href="../fornecedores/fornecedores.php">Cadastre em Fornecedores</a> para poder escolher aqui.</p>';
        return;
    }
    echo '<div class="chips-check">';
    foreach ($fornecedores as $f) {
        $id = (int)$f['id_fornecedor'];
        $chk = isset($marcados[$id]) ? ' checked' : '';
        echo '<label class="chip-check"><input type="checkbox" name="fornecedores[]" value="' . $id . '"' . $chk . '><span>'
           . htmlspecialchars($f['nome_fornecedor'], ENT_QUOTES, 'UTF-8') . '</span></label>';
    }
    echo '</div>';
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RestControl - Estoque</title>
    <link rel="stylesheet" href="estoque.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <div class="bg-overlay"></div>

    <div class="dashboard-wrapper">
        <header class="top-bar">
            <div class="brand">
                <i class="fa-solid fa-boxes-stacked brand-icon" style="color: #780c0c; font-size: 1.3rem; margin-right: 8px;"></i>
                <span>RestControl</span>
            </div>
            <div class="top-actions">
                <a href="../home/home.php" class="top-link"><i class="fa-solid fa-house"></i> Voltar ao painel</a>
            </div>
        </header>

        <main class="main-content">

            <section class="card-box form-section">
                <h2><i class="fa-solid fa-circle-plus"></i> Registrar Ingrediente</h2>

                <form action="estoque.php" method="POST" id="formEstoque">
                    <div class="form-grid">
                        <div class="input-group">
                            <label for="nome_ingredientes">Nome do Ingrediente</label>
                            <input type="text" id="nome_ingredientes" name="nome_ingredientes" placeholder="Ex: Tomate Cereja" required>
                        </div>

                        <div class="input-group">
                            <label for="quantidade_atual">Quantidade Atual</label>
                            <input type="text" id="quantidade_atual" name="quantidade_atual" placeholder="Ex: 5000" required>
                        </div>

                        <div class="input-group">
                            <label for="unidade_medida">Unidade (como este ingrediente é medido)</label>
                            <select id="unidade_medida" name="unidade_medida">
                                <option value="g">g (gramas)</option>
                                <option value="kg">kg (quilos)</option>
                                <option value="ml">ml (mililitros)</option>
                                <option value="l">l (litros)</option>
                                <option value="un">un (unidade)</option>
                                <option value="dz">dz (dúzia)</option>
                            </select>
                        </div>

                        <div class="input-group">
                            <label for="tempo_reposicao_dias">Tempo de reposição (dias)</label>
                            <input type="number" id="tempo_reposicao_dias" name="tempo_reposicao_dias" min="1" max="365" value="2" placeholder="Ex: 3" required>
                        </div>

                        <div class="input-group">
                            <label for="lote_reposicao">Lote de reposição (quanto costuma comprar por pedido)</label>
                            <input type="text" id="lote_reposicao" name="lote_reposicao" placeholder="Ex: 20 (na unidade escolhida)" required>
                        </div>

                        <div class="input-group">
                            <label for="custo_unitario">Custo por unidade de medida (R$)</label>
                            <input type="text" id="custo_unitario" name="custo_unitario" placeholder="Ex: 5,50 (por kg, por litro, por un...)" required>
                        </div>

                        <div class="input-group input-group-full">
                            <label>Fornecedores deste ingrediente</label>
                            <?php chips_fornecedores($fornecedores); ?>
                        </div>
                    </div>
                    <p class="hint">O estoque <b>mínimo</b> e o <b>máximo</b> são calculados automaticamente pelo sistema com as vendas e os desperdícios do mês anterior: mínimo = consumo médio diário × tempo de reposição; máximo = mínimo + lote de reposição. Tempo de reposição é quantos dias o fornecedor leva para entregar depois do pedido.</p>
                    <p class="hint">Informe a quantidade no total que o ingrediente vem (ex: um pacote de arroz de 5kg = 5000 se a unidade for gramas, ou 5 se a unidade for kg). É essa unidade que será descontada automaticamente quando um prato que usa este ingrediente for vendido.</p>

                    <div class="button-container">
                        <button type="submit" name="adicionar_ingrediente" class="btn btn-primary">Adicionar Ingrediente</button>
                    </div>
                </form>
            </section>

            <section class="card-box list-section">
                <h2><i class="fa-solid fa-boxes-stacked"></i> Ingredientes Cadastrados</h2>
                <p class="hint hint-calc">Mínimo e máximo calculados automaticamente com base em <b><?php echo h_est($mes_base); ?></b> (consumo médio diário × tempo de reposição; máximo = mínimo + lote).</p>
                <div class="func-list">
                    <?php if ($resultado && count($resultado) > 0): ?>
                        <?php foreach ($resultado as $row): ?>
                            <?php
                                $unidade = $row['unidade_medida'] ?: 'un';
                                $indisponivel = (float)$row['quantidade_atual'] <= 0;
                                $abaixo_minimo = !$indisponivel && (float)$row['quantidade_atual'] <= (float)$row['quantidade_minima'];
                                $inf = $calc['ingredientes'][(int)$row['id_ingrediente']] ?? ['consumo_diario' => 0, 'tem_historico' => false];
                            ?>
                            <div class="func-item<?php echo $indisponivel ? ' item-indisponivel' : ''; ?>">
                                <div class="func-info-basic">
                                    <div class="avatar"><i class="fa-solid fa-box"></i></div>
                                    <div>
                                        <h3>
                                            <?php echo htmlspecialchars($row['nome_ingredientes']); ?>
                                            <?php if ($indisponivel): ?>
                                                <span class="badge badge-indisponivel"><i class="fa-solid fa-triangle-exclamation"></i> Indisponível</span>
                                            <?php endif; ?>
                                        </h3>
                                        <p>
                                            Estoque: <?php echo formatar_qtd($row['quantidade_atual']); ?> <?php echo htmlspecialchars($unidade); ?>
                                            (mín: <?php echo formatar_qtd($row['quantidade_minima']); ?> / máx: <?php echo formatar_qtd($row['quantidade_maxima']); ?> <?php echo htmlspecialchars($unidade); ?>)
                                            <?php if ($abaixo_minimo): ?>
                                                <span style="color:#ef4444; font-weight:600;"> — repor!</span>
                                            <?php endif; ?>
                                        </p>
                                        <p class="calc-info">
                                            <?php if ($inf['tem_historico']): ?>
                                                Consumo médio: <?php echo formatar_qtd(round($inf['consumo_diario'], 3)); ?> <?php echo htmlspecialchars($unidade); ?>/dia
                                            <?php else: ?>
                                                Sem consumo em <?php echo h_est($mes_base); ?> (mínimo mantido)
                                            <?php endif; ?>
                                            · Reposição: <?php echo (int)$row['tempo_reposicao_dias']; ?> dia(s)
                                            · Lote: <?php echo formatar_qtd($row['lote_reposicao']); ?> <?php echo htmlspecialchars($unidade); ?>
                                        </p>
                                        <p>Custo Unitário: R$ <?php echo number_format($row['custo_unitario'], 2, ',', '.'); ?></p>
                                        <?php $vinc = $vinc_por_ingrediente[$row['id_ingrediente']] ?? []; ?>
                                        <?php if ($vinc): ?>
                                            <div class="chips">
                                                <?php foreach ($vinc as $nome_f): ?>
                                                    <span class="chip"><i class="fa-solid fa-truck-ramp-box"></i> <?php echo htmlspecialchars($nome_f); ?></span>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php else: ?>
                                            <p class="sem-forn">Sem fornecedor vinculado</p>
                                        <?php endif; ?>

                                        <details class="forn-edit">
                                            <summary><i class="fa-solid fa-box-open"></i> Repor estoque</summary>
                                            <form action="estoque.php" method="POST" class="form-repor">
                                                <input type="hidden" name="id_ingrediente" value="<?php echo (int)$row['id_ingrediente']; ?>">
                                                <input type="text" name="quantidade_repor" placeholder="Qtd. recebida (<?php echo htmlspecialchars($unidade); ?>)" required>
                                                <button type="submit" name="repor_estoque" class="btn btn-primary btn-sm">Adicionar ao estoque</button>
                                            </form>
                                        </details>

                                        <details class="forn-edit">
                                            <summary><i class="fa-solid fa-clock-rotate-left"></i> Tempo de reposição e lote</summary>
                                            <form action="estoque.php" method="POST" class="form-repor">
                                                <input type="hidden" name="id_ingrediente" value="<?php echo (int)$row['id_ingrediente']; ?>">
                                                <label class="mini-label">Tempo de reposição (dias)
                                                    <input type="number" name="tempo_reposicao_dias" min="1" max="365" value="<?php echo (int)$row['tempo_reposicao_dias']; ?>" required>
                                                </label>
                                                <label class="mini-label">Lote (<?php echo htmlspecialchars($unidade); ?>)
                                                    <input type="text" name="lote_reposicao" value="<?php echo formatar_qtd($row['lote_reposicao']); ?>" required>
                                                </label>
                                                <button type="submit" name="salvar_reposicao" class="btn btn-primary btn-sm">Salvar</button>
                                            </form>
                                        </details>

                                        <details class="forn-edit">
                                            <summary><i class="fa-solid fa-truck-ramp-box"></i> Escolher fornecedores</summary>
                                            <form action="estoque.php" method="POST">
                                                <input type="hidden" name="id_ingrediente" value="<?php echo (int)$row['id_ingrediente']; ?>">
                                                <?php chips_fornecedores($fornecedores, $vinc); ?>
                                                <?php if ($fornecedores): ?>
                                                    <button type="submit" name="salvar_fornecedores" class="btn btn-primary btn-sm">Salvar</button>
                                                <?php endif; ?>
                                            </form>
                                        </details>
                                    </div>
                                </div>
                                <a href="estoque.php?excluir=<?php echo $row['id_ingrediente']; ?>"
                                   class="btn btn-danger"
                                   onclick="return confirm('Remover \'<?php echo htmlspecialchars($row['nome_ingredientes']); ?>\' do estoque?')">
                                   <i class="fa-solid fa-trash-can"></i>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="empty-msg">Nenhum ingrediente adicionado ao estoque.</p>
                    <?php endif; ?>
                </div>
            </section>

        </main>
    </div>

    <script src="estoque.js"></script>
</body>
</html>

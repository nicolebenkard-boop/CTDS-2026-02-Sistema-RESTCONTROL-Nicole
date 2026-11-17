<?php
// Campos do formulário de fornecedor (usado no cadastro e na edição).
// Espera: $f (array com valores atuais). Os ingredientes são vinculados na tela de Estoque.
?>
<div class="input-group">
    <label>Nome do Fornecedor / Empresa</label>
    <input type="text" name="nome_fornecedor" value="<?php echo h($f['nome_fornecedor'] ?? ''); ?>" placeholder="Ex: Atacadão Alimentos" required>
</div>

<div class="input-group">
    <label>Telefone / WhatsApp</label>
    <input type="tel" name="telefone" value="<?php echo h($f['telefone'] ?? ''); ?>" placeholder="(14) 99999-9999" maxlength="20" required>
</div>

<div class="input-group">
    <label>E-mail (opcional)</label>
    <input type="email" name="email" value="<?php echo h($f['email'] ?? ''); ?>" placeholder="contato@fornecedor.com" maxlength="255">
</div>

<div class="input-group">
    <label>Outros produtos / observações (opcional)</label>
    <input type="text" name="produto_fornecido" value="<?php echo h($f['produto_fornecido'] ?? ''); ?>" placeholder="Ex: descartáveis, embalagens...">
</div>

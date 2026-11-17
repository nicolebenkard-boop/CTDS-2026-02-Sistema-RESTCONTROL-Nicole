<?php
// Conteúdo dos Termos e Condições. Edite o texto aqui.
// Usa: moeda(), VALOR_MENSAL e CONTATO_TERMOS definidos em configuracoes.php.
?>
<p class="termos-data">Última atualização: 23/09/2026</p>

<h4>1. Aceitação dos termos</h4>
<p>Ao criar uma conta e usar o RestControl, você declara que leu e concorda com estes Termos e Condições. Se não concordar, não utilize o sistema.</p>

<h4>2. O que é o RestControl</h4>
<p>O RestControl é um sistema online de apoio à gestão de restaurantes. Ele reúne ferramentas como cadastro de pratos e fichas técnicas, estoque, fornecedores, funcionários, mesas, pedidos, cardápio digital por QR Code, quadro de tarefas (Kanban) e layout do salão. Ele é uma ferramenta de apoio e não substitui contabilidade, emissão de nota fiscal ou outras obrigações legais do seu negócio.</p>

<h4>3. Conta e responsabilidades</h4>
<p>Você deve informar dados verdadeiros no cadastro e mantê-los atualizados. Você é responsável por guardar sua senha e por tudo o que for feito com a sua conta. Cada conta pertence a um restaurante e deve ser usada somente por pessoas autorizadas por você.</p>

<h4>4. Assinatura e pagamento</h4>
<p>O acesso é feito mediante assinatura mensal de <?php echo moeda(VALOR_MENSAL); ?>, paga por Pix. Não há cobrança automática. Após a confirmação do pagamento pelo administrador, o vencimento da conta é estendido em 1 mês por mensalidade paga. Você também pode pagar meses adiantados, e cada mês soma 30 dias ao vencimento atual. A confirmação é feita manualmente e pode levar algum tempo. Quando a assinatura vence, o acesso fica bloqueado até um novo pagamento, e os dados permanecem guardados. O valor do plano pode ser reajustado, com aviso prévio.</p>

<h4>5. Cancelamento</h4>
<p>Você pode cancelar a assinatura a qualquer momento em Configurações &gt; Assinatura. O cancelamento impede a renovação, mas o acesso continua até o fim do período já pago. Pedidos de reembolso serão analisados conforme a legislação aplicável.</p>

<h4>6. Exclusão da conta</h4>
<p>Você pode apagar sua conta em Configurações &gt; Privacidade e Segurança. A exclusão é definitiva e remove os dados do seu restaurante (pratos, fichas técnicas, imagens, estoque, fornecedores, funcionários, mesas, pedidos, histórico, tarefas e layouts). Não é possível recuperá-los depois.</p>

<h4>7. Dados pessoais e privacidade (LGPD)</h4>
<p><strong>Quais dados tratamos.</strong> Dados do gestor (nome, CPF, CNPJ, nome do restaurante, e-mail, telefone e senha); dados que você cadastra sobre o seu restaurante, incluindo funcionários (nome, CPF, início do trabalho) e fornecedores (nome e contato); e dados de clientes informados no cardápio digital (nome, telefone e número da mesa).</p>
<p><strong>Para que usamos.</strong> Apenas para fazer o sistema funcionar: autenticar você, liberar o acesso conforme a assinatura, e permitir a gestão do restaurante e o atendimento das mesas.</p>
<p><strong>Papéis.</strong> Em relação aos dados de seus funcionários e clientes, o restaurante (você) decide como são usados. Por isso, cabe a você informar essas pessoas e ter base legal para cadastrá-las.</p>
<p><strong>Separação entre restaurantes.</strong> Cada restaurante acessa somente os próprios dados.</p>
<p><strong>Compartilhamento.</strong> Não vendemos dados. O QR Code do cardápio é gerado por um serviço externo (api.qrserver.com), que recebe o endereço do link do seu cardápio.</p>
<p><strong>Seus direitos.</strong> Nos termos da LGPD, você pode solicitar acesso, correção e exclusão dos seus dados. A exclusão da conta pode ser feita por você mesmo no sistema; para outras solicitações, use o contato no final destes termos.</p>

<h4>8. Segurança</h4>
<p>As senhas são armazenadas de forma criptografada (hash) e o acesso às telas exige login. Nenhum sistema é totalmente livre de riscos, por isso use uma senha forte e não a compartilhe.</p>

<h4>9. Uso adequado</h4>
<p>É proibido tentar acessar dados de outros restaurantes, burlar limites do sistema, inserir conteúdo ilegal ou usar o RestControl para fins ilícitos. Contas que descumprirem estas regras podem ser suspensas.</p>

<h4>10. Limitação de responsabilidade</h4>
<p>Os valores, estoques e totais exibidos dependem das informações que você cadastra. O sistema é oferecido sem garantia de funcionamento ininterrupto, e podem ocorrer manutenções ou falhas. O RestControl não se responsabiliza por decisões tomadas com base nos dados cadastrados.</p>

<h4>11. Alterações destes termos</h4>
<p>Estes termos podem ser atualizados. A data da última atualização fica no topo desta página.</p>

<h4>12. Contato</h4>
<p>Dúvidas ou solicitações sobre estes termos e seus dados: <?php echo h(CONTATO_TERMOS); ?></p>

(function () {
    // Copiar chave Pix
    document.querySelectorAll('[data-copy]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var campo = document.querySelector(btn.getAttribute('data-copy'));
            if (!campo) return;
            var texto = campo.value;
            var ok = function () {
                var original = btn.textContent;
                btn.textContent = 'Copiado!';
                setTimeout(function () { btn.textContent = original; }, 1800);
            };
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(texto).then(ok);
            } else {
                campo.select();
                document.execCommand('copy');
                ok();
            }
        });
    });

    // Mudar senha: avisa se a confirmação não bate
    var nova = document.getElementById('nova_senha');
    var conf = document.getElementById('confirmar_senha');
    var erroConf = document.getElementById('erroConfirma');
    var formSenha = document.getElementById('formSenha');
    if (nova && conf && erroConf) {
        var checar = function () {
            erroConf.hidden = !conf.value || conf.value === nova.value;
        };
        nova.addEventListener('input', checar);
        conf.addEventListener('input', checar);
        formSenha.addEventListener('submit', function (e) {
            if (conf.value !== nova.value) {
                e.preventDefault();
                erroConf.hidden = false;
                conf.focus();
            }
        });
    }

    // Apagar conta: só habilita o botão quando digitar EXCLUIR
    // (o servidor confere de novo, isto é só conforto visual)
    var texto = document.getElementById('texto_confirma');
    var btnApagar = document.getElementById('btnApagar');
    var formApagar = document.getElementById('formApagar');
    if (texto && btnApagar) {
        texto.addEventListener('input', function () {
            btnApagar.disabled = texto.value.trim().toUpperCase() !== 'EXCLUIR';
        });
        formApagar.addEventListener('submit', function (e) {
            if (!confirm('Última chance: apagar sua conta e TODOS os dados do restaurante?')) {
                e.preventDefault();
            }
        });
    }
})();

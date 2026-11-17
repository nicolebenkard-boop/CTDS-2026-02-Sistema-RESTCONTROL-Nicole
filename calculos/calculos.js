// calculos.js - Gráficos, exportação CSV e impressão do módulo de Cálculos
document.addEventListener('DOMContentLoaded', function () {
    const D = window.DADOS_CALCULOS;
    if (!D || typeof Chart === 'undefined') return;

    const COR_TEXTO_ESCURO = 'rgba(255,255,255,0.75)';
    const GRADE_ESCURA = 'rgba(255,255,255,0.08)';
    const VINHO = '#a31414';
    const VERDE = '#4ade80';
    const AMARELO = '#facc15';
    const VERMELHO = '#f87171';
    const CINZA = '#9ca3af';

    Chart.defaults.color = COR_TEXTO_ESCURO;
    Chart.defaults.borderColor = GRADE_ESCURA;
    Chart.defaults.font.family = "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif";

    const charts = [];

    const brl = (v) => 'R$ ' + Number(v).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    // Se não houver nenhum valor, troca o gráfico por uma mensagem
    function temDados(...series) {
        return series.some(s => s.some(v => Number(v) !== 0));
    }
    function semDados(idCanvas, msg) {
        const canvas = document.getElementById(idCanvas);
        if (!canvas) return;
        const area = canvas.parentElement;
        area.innerHTML = '<div class="sem-dados">' + msg + '</div>';
    }
    function criar(id, config, ...series) {
        const canvas = document.getElementById(id);
        if (!canvas) return;
        if (!temDados(...series)) {
            semDados(id, 'Sem dados neste período.');
            return;
        }
        charts.push(new Chart(canvas, config));
    }

    // 1) Receita e lucro bruto por dia
    criar('chartDias', {
        type: 'bar',
        data: {
            labels: D.dias.labels,
            datasets: [
                { type: 'bar', label: 'Receita', data: D.dias.receita, backgroundColor: VINHO, borderRadius: 4 },
                { type: 'line', label: 'Lucro bruto', data: D.dias.lucro, borderColor: VERDE, backgroundColor: VERDE, tension: 0.25, pointRadius: 2 }
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { tooltip: { callbacks: { label: c => c.dataset.label + ': ' + brl(c.parsed.y) } } },
            scales: { x: { title: { display: true, text: 'Dia do mês' } }, y: { ticks: { callback: v => brl(v) } } }
        }
    }, D.dias.receita, D.dias.lucro);

    // 2) Pedidos por dia
    criar('chartPedidos', {
        type: 'bar',
        data: { labels: D.dias.labels, datasets: [{ label: 'Pedidos', data: D.dias.pedidos, backgroundColor: VINHO, borderRadius: 4 }] },
        options: {
            responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } },
            scales: { x: { title: { display: true, text: 'Dia do mês' } }, y: { beginAtZero: true, ticks: { precision: 0 } } }
        }
    }, D.dias.pedidos);

    // 3) Movimento por horário
    criar('chartHoras', {
        type: 'bar',
        data: { labels: D.horas.labels, datasets: [{ label: 'Comandas abertas', data: D.horas.valores, backgroundColor: AMARELO, borderRadius: 4 }] },
        options: {
            responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
        }
    }, D.horas.valores);

    // 4) Média por dia da semana
    criar('chartSemana', {
        type: 'bar',
        data: { labels: D.semana.labels, datasets: [{ label: 'Média de pedidos', data: D.semana.valores, backgroundColor: VERDE, borderRadius: 4 }] },
        options: {
            responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true } }
        }
    }, D.semana.valores);

    // 5) Curva ABC (Pareto): barras coloridas por classe + linha do % acumulado
    const corClasse = { A: VERDE, B: AMARELO, C: VERMELHO };
    criar('chartAbc', {
        type: 'bar',
        data: {
            labels: D.abc.labels,
            datasets: [
                { type: 'line', label: '% acumulado', data: D.abc.acum, yAxisID: 'y1', borderColor: '#fff', backgroundColor: '#fff', tension: 0.2, pointRadius: 3 },
                { type: 'bar', label: 'Receita', data: D.abc.receita, yAxisID: 'y',
                  backgroundColor: D.abc.classes.map(c => corClasse[c] || CINZA), borderRadius: 4 }
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: {
                tooltip: { callbacks: { label: c => c.dataset.yAxisID === 'y1' ? c.dataset.label + ': ' + c.parsed.y + '%' : c.dataset.label + ': ' + brl(c.parsed.y) } },
                legend: { labels: { generateLabels: () => [
                    { text: 'Classe A', fillStyle: VERDE, strokeStyle: VERDE, fontColor: Chart.defaults.color },
                    { text: 'Classe B', fillStyle: AMARELO, strokeStyle: AMARELO, fontColor: Chart.defaults.color },
                    { text: 'Classe C', fillStyle: VERMELHO, strokeStyle: VERMELHO, fontColor: Chart.defaults.color },
                    { text: '% acumulado', fillStyle: '#fff', strokeStyle: '#fff', fontColor: Chart.defaults.color }
                ] } }
            },
            scales: {
                y: { beginAtZero: true, position: 'left', ticks: { callback: v => brl(v) } },
                y1: { beginAtZero: true, max: 100, position: 'right', grid: { drawOnChartArea: false }, ticks: { callback: v => v + '%' } }
            }
        }
    }, D.abc.receita);

    // 6) Desperdício por ingrediente
    criar('chartDesperdicio', {
        type: 'bar',
        data: { labels: D.desperdicio.labels, datasets: [{ label: 'Custo do desperdício', data: D.desperdicio.custo, backgroundColor: VERMELHO, borderRadius: 4 }] },
        options: {
            indexAxis: 'y', responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false },
                tooltip: { callbacks: { label: c => brl(c.parsed.x) } } },
            scales: { x: { beginAtZero: true, ticks: { callback: v => brl(v) } } }
        }
    }, D.desperdicio.custo);

    // ---- Seletor de mês: troca ao escolher ----
    const inputMes = document.getElementById('mes');
    if (inputMes) {
        inputMes.addEventListener('change', function () {
            if (this.value) document.getElementById('formMes').submit();
        });
    }

    // ---- Exportar tabela para CSV (abre no Excel em português) ----
    document.querySelectorAll('[data-csv]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const tabela = document.getElementById(btn.dataset.csv);
            if (!tabela) return;
            const linhas = Array.from(tabela.querySelectorAll('tr')).map(tr =>
                Array.from(tr.children).map(td => '"' + td.textContent.replace(/\s+/g, ' ').trim().replace(/"/g, '""') + '"').join(';')
            );
            const blob = new Blob(['\ufeff' + linhas.join('\r\n')], { type: 'text/csv;charset=utf-8;' });
            const a = document.createElement('a');
            a.href = URL.createObjectURL(blob);
            a.download = btn.dataset.nome || 'relatorio.csv';
            document.body.appendChild(a);
            a.click();
            a.remove();
            URL.revokeObjectURL(a.href);
        });
    });

    // ---- Impressão: gráficos com cores escuras no papel ----
    function trocarCores(texto, grade) {
        Chart.defaults.color = texto;
        Chart.defaults.borderColor = grade;
        charts.forEach(function (c) {
            const o = c.options;
            if (o.plugins && o.plugins.legend && o.plugins.legend.labels) {
                // a legenda da curva ABC tem cores fixas no texto
                o.plugins.legend.labels.color = texto;
            }
            Object.values(o.scales || {}).forEach(function (s) {
                s.ticks = s.ticks || {}; s.ticks.color = texto;
                s.grid = s.grid || {}; s.grid.color = grade;
                if (s.title) s.title.color = texto;
            });
            c.update('none');
        });
    }
    window.addEventListener('beforeprint', () => trocarCores('#222', 'rgba(0,0,0,0.12)'));
    window.addEventListener('afterprint', () => trocarCores(COR_TEXTO_ESCURO, GRADE_ESCURA));

    const btnImprimir = document.getElementById('btnImprimir');
    if (btnImprimir) btnImprimir.addEventListener('click', () => window.print());
});

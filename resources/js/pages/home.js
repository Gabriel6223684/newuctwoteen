document.addEventListener('DOMContentLoaded', () => {
    const saleEl = document.getElementById('resultadoVenda');
    const marketingEl = document.getElementById('resultadoMarketing');

    // Se o template não renderizou os elementos ainda, não quebra o JS.
    if (!saleEl || !marketingEl) return;

    const ChartSale = echarts.init(saleEl);
    const ChartMarketing = echarts.init(marketingEl);

    const now = new Date();
    const year = now.getFullYear();
    const months = Array.from({ length: 12 }, (_, i) => i + 1);
    const monthLabels = months.map((m) => {
        const d = new Date(year, m - 1, 1);
        return d.toLocaleDateString('pt-BR', { month: 'short' }).replace('.', '');
    });

    function setSaleChart({ months, values, year }) {
        const dataChartsSale = {
            title: { text: `Resultado de Vendas - ${year}` },
            grid: { left: 40, right: 20, top: 60, bottom: 40, containLabel: true },
            tooltip: {
                trigger: 'axis',
                axisPointer: { type: 'shadow' },
                formatter: (params) => {
                    const p = params?.[0];
                    return p ? `${p.axisValue}<br/><b>${p.value}</b> vendas` : '';
                }
            },
            legend: { data: ['Totais'] },
            xAxis: {
                type: 'category',
                data: months,
                axisLabel: {
                    interval: 0,
                    rotate: 0
                }
            },
            yAxis: { type: 'value' },
            series: [
                {
                    name: 'Totais',
                    type: 'bar',
                    data: values,
                    barMaxWidth: 40
                }
            ]
        };

        ChartSale.setOption(dataChartsSale, true);
    }

    function setMarketingChart({ pieData }) {
        const dataChartMarketing = {
            tooltip: { trigger: 'item' },
            legend: { top: '5%', left: 'center' },
            series: [
                {
                    name: 'ABC',
                    type: 'pie',
                    radius: ['40%', '70%'],
                    avoidLabelOverlap: false,
                    padAngle: 5,
                    itemStyle: { borderRadius: 10 },
                    label: { show: false, position: 'center' },
                    emphasis: {
                        label: {
                            show: true,
                            fontSize: 40,
                            fontWeight: 'bold'
                        }
                    },
                    labelLine: { show: false },
                    data: pieData
                }
            ]
        };

        ChartMarketing.setOption(dataChartMarketing, true);
    }

    async function loadCharts() {
        try {
            const res = await fetch('/home/charts', { headers: { Accept: 'application/json' } });
            if (!res.ok) throw new Error(`HTTP ${res.status}`);

            const payload = await res.json();
            if (!payload?.status) throw new Error(payload?.msg || 'Erro ao carregar gráficos');

            // Garanta array com 12 itens para o eixo X sempre aparecer (meses)
            const apiMonths = Array.isArray(payload?.vendas?.months) ? payload.vendas.months : [];
            const apiValues = Array.isArray(payload?.vendas?.values) ? payload.vendas.values : [];

            const safeMonths = apiMonths.length === 12 ? apiMonths : monthLabels;
            const safeValues = apiValues.length === 12 ? apiValues.map((v) => Number(v) || 0) : Array(12).fill(0);

            setSaleChart({ months: safeMonths, values: safeValues, year: payload?.year || year });

            setMarketingChart({
                pieData: payload?.abc?.pieData && Array.isArray(payload.abc.pieData)
                    ? payload.abc.pieData
                    : [
                        { value: 0, name: 'Classe A' },
                        { value: 0, name: 'Classe B' },
                        { value: 0, name: 'Classe C' }
                    ]
            });

            // resize após setOption (principalmente se layout/containers mudarem)
            window.requestAnimationFrame(() => {
                ChartSale.resize();
                ChartMarketing.resize();
            });
        } catch (e) {
            // Fallback visual mínimo (evita tela vazia)
            setSaleChart({ months: monthLabels, values: Array(12).fill(0), year });
            setMarketingChart({
                pieData: [
                    { value: 0, name: 'Classe A' },
                    { value: 0, name: 'Classe B' },
                    { value: 0, name: 'Classe C' }
                ]
            });
            console.error('Falha ao carregar charts:', e);
        }
    }

    loadCharts();

    // Recalcula se o usuário redimensionar a janela.
    window.addEventListener('resize', () => {
        try {
            ChartSale.resize();
            ChartMarketing.resize();
        } catch (e) {
            // ignore
        }
    });
});


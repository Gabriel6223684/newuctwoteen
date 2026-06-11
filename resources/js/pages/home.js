document.addEventListener('DOMContentLoaded', () => {
    const saleEl = document.getElementById('resultadoVenda');
    const marketingEl = document.getElementById('resultadoMarketing');

    if (!saleEl || !marketingEl) return;

    const ChartSale = echarts.init(saleEl);
    const ChartMarketing = echarts.init(marketingEl);

    const now = new Date();
    const year = now.getFullYear();

    const months = Array.from({ length: 12 }, (_, i) => i + 1);
    const defaultMonthLabels = months.map((m) => {
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
            legend: { data: ['Vendas'] },
            xAxis: {
                type: 'category',
                data: months,
                axisLabel: { interval: 0, rotate: 0 }
            },
            yAxis: { type: 'value' },
            series: [
                {
                    name: 'Vendas',
                    type: 'bar',
                    data: values,
                    barMaxWidth: 40
                }
            ]
        };
        ChartSale.setOption(dataChartsSale, true);
    }

    function setMarketingChart(abcData) {
        // Garante que pegamos o array correto enviado pelo PHP
        const pieData = abcData && abcData.pieData ? abcData.pieData : [];

        const dataChartMarketing = {
            title: {
                text: 'Distribuição por Mês',
                left: 'center',
                top: '0%'
            },
            tooltip: {
                trigger: 'item',
                formatter: '{b}: <b>R$ {c}</b> ({d}%)' // Mostra o mês, valor formatado e a %
            },
            legend: {
                show: false // Ocultado para evitar que 12 meses fiquem encavalados no topo
            },
            series: [
                {
                    name: 'Vendas',
                    type: 'pie',
                    radius: ['45%', '70%'], // Estilo Rosca/Donut
                    avoidLabelOverlap: true,
                    padAngle: 2,
                    itemStyle: { borderRadius: 6 },
                    label: {
                        show: true,
                        position: 'outside',
                        formatter: '{b}' // Escreve o nome do mês do lado de fora da fatia
                    },
                    labelLine: {
                        show: true // Linha guia apontando para o mês
                    },
                    data: pieData
                }
            ]
        };

        // Altere 'ChartMarketing' para o nome exato da sua instância do ECharts
        if (typeof ChartMarketing !== 'undefined') {
            ChartMarketing.setOption(dataChartMarketing, true);
            ChartMarketing.resize();
        }
    }

    async function loadCharts() {
        try {
            const res = await fetch('/home/charts', { headers: { Accept: 'application/json' } });
            if (!res.ok) throw new Error(`HTTP ${res.status}`);

            const payload = await res.json();
            if (!payload?.status) throw new Error(payload?.msg || 'Erro ao carregar gráficos');

            const safeMonths = Array.isArray(payload?.vendas?.months) && payload.vendas.months.length > 0
                ? payload.vendas.months
                : defaultMonthLabels;

            const safeValues = Array.isArray(payload?.vendas?.values) && payload.vendas.values.length > 0
                ? payload.vendas.values.map((v) => Number(v) || 0)
                : Array(safeMonths.length).fill(0);

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

            window.requestAnimationFrame(() => {
                ChartSale.resize();
                ChartMarketing.resize();
            });
        } catch (e) {
            console.error('Falha ao carregar charts:', e);
            setSaleChart({ months: defaultMonthLabels, values: Array(12).fill(0), year });
            setMarketingChart({
                pieData: [
                    { value: 0, name: 'Classe A' },
                    { value: 0, name: 'Classe B' },
                    { value: 0, name: 'Classe C' }
                ]
            });
        }
    }

    // Inicializa a carga
    loadCharts();

    window.addEventListener('resize', () => {
        try {
            ChartSale.resize();
            ChartMarketing.resize();
        } catch (e) { }
    });
});
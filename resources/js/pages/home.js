const ChartSale = echarts.init(document.getElementById('resultadoVenda'));
const ChartMarketing = echarts.init(document.getElementById('resultadoMarketing'));

const now = new Date();
const year = now.getFullYear();
const months = Array.from({ length: 12 }, (_, i) => i + 1);
const monthLabels = months.map((m) => {
    const d = new Date(year, m - 1, 1);
    return d.toLocaleDateString('pt-BR', { month: 'short' }).replace('.', '');
});

// Atualiza os gráficos consumindo dados do backend (HomeController@charts)
// Esperado formato: { status, year, vendas:{months,values}, abc:{pieData:[{value,name},...]}}

function setSaleChart({ months, values, year }) {
    const dataChartsSale = {
        title: { text: `Resultado de Vendas - ${year}` },
        tooltip: {
            trigger: 'axis',
            formatter: (params) => {
                const p = params?.[0];
                return p ? `${p.axisValue}<br/><b>${p.value}</b> vendas` : '';
            }
        },
        legend: { data: ['Totais'] },
        xAxis: { type: 'category', data: months },
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

    ChartSale.setOption(dataChartsSale);
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

    ChartMarketing.setOption(dataChartMarketing);
}

(async function loadCharts() {
    try {
        const res = await fetch('/home/charts', { headers: { 'Accept': 'application/json' } });
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        const payload = await res.json();
        if (!payload?.status) throw new Error(payload?.msg || 'Erro ao carregar gráficos');

        setSaleChart({ months: payload?.vendas?.months || monthLabels, values: payload?.vendas?.values || [], year: payload?.year || year });
        setMarketingChart({ pieData: payload?.abc?.pieData || [] });
    } catch (e) {
        // Fallback visual mínimo (evita tela vazia)
        setSaleChart({ months: monthLabels, values: Array(12).fill(0), year });
        setMarketingChart({ pieData: [{ value: 0, name: 'Classe A' }, { value: 0, name: 'Classe B' }, { value: 0, name: 'Classe C' }] });
        console.error('Falha ao carregar charts:', e);
    }
})();


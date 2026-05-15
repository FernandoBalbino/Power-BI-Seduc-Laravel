import ApexCharts from 'apexcharts';

const chartInstances = new Map();

window.SeducCharts = {
    render(element, options) {
        if (!element || !options) {
            return;
        }

        if (chartInstances.has(element)) {
            chartInstances.get(element).destroy();
            chartInstances.delete(element);
        }

        const chart = new ApexCharts(element, options);
        chartInstances.set(element, chart);
        chart.render();
    },

    destroyAll() {
        chartInstances.forEach((chart) => chart.destroy());
        chartInstances.clear();
    },
};

document.addEventListener('livewire:navigating', () => {
    window.SeducCharts.destroyAll();
});



import Alpine from 'alpinejs';
import { Chart, registerables } from 'chart.js';

window.Alpine = Alpine;

Alpine.start();

Chart.register(...registerables);
Chart.defaults.font.family = 'Figtree, ui-sans-serif, system-ui, sans-serif';
Chart.defaults.color = '#6b7280';

document.querySelectorAll('canvas[data-chart]').forEach((canvas) => {
    const config = JSON.parse(canvas.dataset.chart);
    new Chart(canvas, config);
});

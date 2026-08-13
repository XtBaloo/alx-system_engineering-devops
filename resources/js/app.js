

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

const spinnerSvg = '<svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>';

document.addEventListener('submit', (event) => {
    const form = event.target;

    if (!(form instanceof HTMLFormElement) || form.hasAttribute('data-no-loading')) {
        return;
    }

    const submitButton = form.querySelector('button[type="submit"]:not([disabled])');

    if (!submitButton) {
        return;
    }

    submitButton.disabled = true;
    submitButton.dataset.originalHtml = submitButton.innerHTML;
    submitButton.innerHTML = `${spinnerSvg}<span>${submitButton.textContent.trim()}</span>`;
});

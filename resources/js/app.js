import './bootstrap';
import './ui';
import './global-search';
import './notifications';

import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';

window.Alpine = Alpine;
window.Chart = Chart;

Alpine.start();

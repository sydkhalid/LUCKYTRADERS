import './bootstrap';
import './ui';
import './global-search';
import './notifications';
import './erp-ux';
import './auth-flow';

import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';
import Swal from 'sweetalert2';
import $ from 'jquery';
import select2 from 'select2';
import DataTable from 'datatables.net-dt';
import Responsive from 'datatables.net-responsive-dt';
import Buttons from 'datatables.net-buttons-dt';
import ButtonsHtml5 from 'datatables.net-buttons/js/buttons.html5.mjs';
import ButtonsPrint from 'datatables.net-buttons/js/buttons.print.mjs';
import JSZip from 'jszip';
import pdfMake from 'pdfmake/build/pdfmake';
import pdfFonts from 'pdfmake/build/vfs_fonts';
import 'select2/dist/css/select2.css';
import 'datatables.net-dt/css/dataTables.dataTables.css';
import 'datatables.net-responsive-dt/css/responsive.dataTables.css';
import 'datatables.net-buttons-dt/css/buttons.dataTables.css';
import 'sweetalert2/dist/sweetalert2.min.css';

window.$ = window.jQuery = $;
window.Alpine = Alpine;
window.Chart = Chart;
window.Swal = Swal;
window.JSZip = JSZip;
pdfMake.vfs = pdfFonts.vfs;
window.pdfMake = pdfMake;

select2($);
DataTable(window, $);
Responsive(window, $);
Buttons(window, $);
ButtonsHtml5(window, $);
ButtonsPrint(window, $);

Alpine.start();

import { Livewire, Alpine } from '../../vendor/livewire/livewire/dist/livewire.esm';

import './bootstrap';

import collapse from '@alpinejs/collapse';

import loginHero from './login';

import Swal from 'sweetalert2';

window.Swal = Swal;

/* --------------------------------------------------------------------------
| Alpine
|--------------------------------------------------------------------------
*/
window.Alpine = Alpine;

Alpine.plugin(collapse);

// Register Alpine Components
Alpine.data('loginHero', loginHero);

/* --------------------------------------------------------------------------
| Chart.js
|--------------------------------------------------------------------------
*/
import { Chart } from 'chart.js';

/* --------------------------------------------------------------------------
| Flatpickr
|--------------------------------------------------------------------------
*/
import flatpickr from 'flatpickr';

/* --------------------------------------------------------------------------
| Dashboard Components
|--------------------------------------------------------------------------
*/
import dashboardCard01 from './components/dashboard-card-01';
import dashboardCard02 from './components/dashboard-card-02';
import dashboardCard03 from './components/dashboard-card-03';
import dashboardCard04 from './components/dashboard-card-04';
import dashboardCard05 from './components/dashboard-card-05';
import dashboardCard06 from './components/dashboard-card-06';
import dashboardCard08 from './components/dashboard-card-08';
import dashboardCard09 from './components/dashboard-card-09';
import dashboardCard11 from './components/dashboard-card-11';

/* --------------------------------------------------------------------------
| Chart Defaults
|--------------------------------------------------------------------------
*/
Chart.defaults.font.family = '"Inter", sans-serif';
Chart.defaults.font.weight = 500;
Chart.defaults.plugins.tooltip.borderWidth = 1;
Chart.defaults.plugins.tooltip.displayColors = false;
Chart.defaults.plugins.tooltip.mode = 'nearest';
Chart.defaults.plugins.tooltip.intersect = false;
Chart.defaults.plugins.tooltip.position = 'nearest';
Chart.defaults.plugins.tooltip.caretSize = 0;
Chart.defaults.plugins.tooltip.caretPadding = 20;
Chart.defaults.plugins.tooltip.cornerRadius = 8;
Chart.defaults.plugins.tooltip.padding = 8;

/* --------------------------------------------------------------------------
| Chart Area Gradient Helper
|--------------------------------------------------------------------------
*/
export const chartAreaGradient = (ctx, chartArea, colorStops) => {

    if (!ctx || !chartArea || !colorStops?.length) {
        return 'transparent';
    }

    const gradient = ctx.createLinearGradient(
        0,
        chartArea.bottom,
        0,
        chartArea.top
    );

    colorStops.forEach(({ stop, color }) => {
        gradient.addColorStop(stop, color);
    });

    return gradient;
};

/* --------------------------------------------------------------------------
| Chart Background Plugin
|--------------------------------------------------------------------------
*/
Chart.register({

    id: 'chartAreaPlugin',

    beforeDraw(chart) {

        if (
            chart.config.options.chartArea &&
            chart.config.options.chartArea.backgroundColor
        ) {

            const ctx = chart.canvas.getContext('2d');
            const { chartArea } = chart;

            ctx.save();

            ctx.fillStyle =
                chart.config.options.chartArea.backgroundColor;

            ctx.fillRect(
                chartArea.left,
                chartArea.top,
                chartArea.right - chartArea.left,
                chartArea.bottom - chartArea.top
            );

            ctx.restore();
        }
    },
});

/* --------------------------------------------------------------------------
| DOM Ready
|--------------------------------------------------------------------------
*/
document.addEventListener('DOMContentLoaded', () => {

    const lightSwitches = document.querySelectorAll('.light-switch');

    if (lightSwitches.length > 0) {

        lightSwitches.forEach((lightSwitch, i) => {

            if (localStorage.getItem('dark-mode') === 'true') {
                lightSwitch.checked = true;
            }

            lightSwitch.addEventListener('change', () => {

                const { checked } = lightSwitch;

                lightSwitches.forEach((el, n) => {
                    if (n !== i) {
                        el.checked = checked;
                    }
                });

                document.documentElement.classList.add('**:transition-none!');

                if (checked) {

                    document.documentElement.classList.add('dark');
                    document.documentElement.style.colorScheme = 'dark';

                    localStorage.setItem('dark-mode', true);

                    document.dispatchEvent(
                        new CustomEvent('darkMode', {
                            detail: { mode: 'on' }
                        })
                    );

                } else {

                    document.documentElement.classList.remove('dark');
                    document.documentElement.style.colorScheme = 'light';

                    localStorage.setItem('dark-mode', false);

                    document.dispatchEvent(
                        new CustomEvent('darkMode', {
                            detail: { mode: 'off' }
                        })
                    );
                }

                setTimeout(() => {
                    document.documentElement.classList.remove('**:transition-none!');
                }, 1);

            });

        });

    }

    flatpickr('.datepicker', {

        mode: 'range',
        static: true,
        monthSelectorType: 'static',
        dateFormat: 'M j, Y',

        defaultDate: [
            new Date().setDate(new Date().getDate() - 6),
            new Date()
        ],

        prevArrow:
            '<svg class="fill-current" width="7" height="11" viewBox="0 0 7 11"><path d="M5.4 10.8l1.4-1.4-4-4 4-4L5.4 0 0 5.4z" /></svg>',

        nextArrow:
            '<svg class="fill-current" width="7" height="11" viewBox="0 0 7 11"><path d="M1.4 10.8L0 9.4l4-4-4-4L1.4 0l5.4 5.4z" /></svg>',

        onReady(selectedDates, dateStr, instance) {

            instance.element.value = dateStr.replace('to', '-');

            const customClass =
                instance.element.getAttribute('data-class');

            if (customClass) {
                instance.calendarContainer.classList.add(customClass);
            }
        },

        onChange(selectedDates, dateStr, instance) {
            instance.element.value = dateStr.replace('to', '-');
        }
    });

    dashboardCard01();
    dashboardCard02();
    dashboardCard03();
    dashboardCard04();
    dashboardCard05();
    dashboardCard06();
    dashboardCard08();
    dashboardCard09();
    dashboardCard11();

});

/* --------------------------------------------------------------------------
| Start Livewire
|--------------------------------------------------------------------------
*/
Livewire.start();

const ctx = document
    .getElementById('premiumLineChart')
    .getContext('2d');

new Chart(ctx, {
    type: 'line',
    data: {
        labels: [
            'Jan',
            'Feb',
            'Mar',
            'Apr',
            'May',
            'Jun',
            'Jul',
            'Aug',
            'Sep',
            'Oct',
            'Nov',
            'Dec'
        ],
        datasets: [{
            label: 'Tickets',
            data: [45, 52, 48, 66, 72, 80, 78, 91, 96, 103, 118, 128],
            tension: 0.4,
            fill: true,
            borderWidth: 3,
            pointRadius: 0,
            pointHoverRadius: 5
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,

        interaction: {
            intersect: false,
            mode: 'index'
        },

        plugins: {
            legend: {
                display: false
            }
        },

        scales: {
            x: {
                grid: {
                    display: false
                }
            },

            y: {
                beginAtZero: true,
                ticks: {
                    precision: 0
                }
            }
        }
    }
});

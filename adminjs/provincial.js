// Geting the canvas
var ctx = document.getElementById('myChart').getContext('2d');

// Creating a new chart instance
var myChart = new Chart(ctx, {
    type: 'bar', 
    data: {
        labels: ['Western Cape', 'Kwa-Zulu Natal', 'Gauteng', 'Limpopo', 'North-West', 'Mmpumalanga', 'Northern Cape', 'Eastern Cape', 'Free State'], // X-axis labels
        datasets: [{
            label: 'Users', // Dataset label for legend
            data: [120, 68, 192, 12, 41, 26, 5, 90, 71],
            backgroundColor: ['#1264B5', '#0A6624', '#D91F0B', '#95C221', '#D6910F', '#353238', '#EBE41E', '#A3268E', '#733A19'],
            borderColor: [ /* Array of colors for borders */ ],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true 
            }
        }
    }
});
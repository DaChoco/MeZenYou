document.addEventListener("DOMContentLoaded", () => {
// Geting the canvas
var ctx = document.getElementById('myProvinceChart').getContext('2d');
var salesctx = document.getElementById('mySalesChart').getContext('2d');
var piectx = document.getElementById('pieconversion');

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

var mySalesChart = new Chart(salesctx, {
    type: 'bar', 
    data: {
        labels: ['Novels', 'Comics/Manga', 'Video Games', 'Electronics', 'Office', 'Beauty'], // X-axis labels
        datasets: [{
            label: 'Users', // Dataset label for legend
            data: [3000, 7000, 892, 117, 416, 260],
            backgroundColor: ['#1264B5', '#0A6624', '#D91F0B', '#95C221', '#1264B5', '#1264B5'],
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

  new Chart(piectx, {
    type: 'doughnut',
    data: {
      labels: ['Product View', 'Add to Cart', 'Go To Checkout', 'Completed Purchase'],
      datasets: [{
        data: [0.3, 0.15, 0.15, 0.4],
        backgroundColor: ['#F0290A','#D97F18','#B9C74A','#52E014']
      }]
    }
  });
});


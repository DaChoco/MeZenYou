const API = window.ENV.API_URL;
const provinces = ['Western Cape', 'Kwa-Zulu Natal', 'Gauteng', 'Limpopo', 'North-West', 'Mmpumalanga', 'Northern Cape', 'Eastern Cape', 'Free State'];

async function retrieveProvincialData() {
    let url = `${API}/api/admin/provincial.php`;

    const response = await fetch(url, { credentials: "include" });
    const data = await response.json()
    console.log(data)
    const provincialusers = data.data_province

    const map = {};

    provincialusers.forEach(item => {
        if (item.province) {
            map[item.province] = item.clients;
        }
    });
    const orderedClients = provinces.map(prov => map[prov] ?? 0);
    return orderedClients;

}

async function retrieveAdminUserData() {
    const res = await fetch(`${API}/api/account/role.php`, {
        credentials: "include",
    });

    const data = await res.json();

    return data
}
document.addEventListener("DOMContentLoaded", async () => {
    // Geting the canvas
    var ctx = document.getElementById('myProvinceChart').getContext('2d');
    var salesctx = document.getElementById('mySalesChart').getContext('2d');
    var piectx = document.getElementById('pieconversion');
    const adminicon = document.getElementById('adminicon');
    const currentuser = document.getElementById('currentuser');
    const currentrole = document.getElementById('rolepage');
    const signoutbtn = document.getElementById('signoutbtn');

    signoutbtn.addEventListener('click', async () => {
        const res = await fetch(`${API}/api/auth/logout.php`, { credentials: "include" });

        const data = await res.json();
        if (data.success) window.location.href = '/';
    })


    const perProvince = await retrieveProvincialData();
    const data = await retrieveAdminUserData();
    currentuser.innerText = data["username"];
    if (data['role'] === "ADMIN") currentrole.innerText = "Head Website Administrator";
    if (data['role'] === "MODERATOR") currentrole.innerText = "Content Moderator"

    adminicon.setAttribute("src", `${data["icon"]}?tr=w-200,c-maintain_ratio`);

    // Creating a new chart instance
    var myChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: provinces, // X-axis labels
            datasets: [{
                label: 'Users', // Dataset label for legend
                data: perProvince,
                backgroundColor: ['#1264B5', '#0A6624', '#D91F0B', '#95C221', '#D6910F', '#353238', '#EBE41E', '#A3268E', '#733A19'],
                borderColor: [ /* Array of colors for borders */],
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
                borderColor: [ /* Array of colors for borders */],
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
                backgroundColor: ['#F0290A', '#D97F18', '#B9C74A', '#52E014']
            }]
        }
    });
});


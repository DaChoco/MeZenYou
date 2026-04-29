const searchbar = document.getElementById('usersearch');
const API = window.ENV.API_URL;
const provinces = ['Western Cape', 'Kwa-Zulu Natal', 'Gauteng', 'Limpopo', 'North-West', 'Mmpumalanga', 'Northern Cape', 'Eastern Cape', 'Free State'];
const categories = ['Novels','Comics/Manga', 'Video Games', 'Electronics', 'Office', 'Colelctibles', 'Other']
async function retrieveChartlData() {
    let url = `${API}/api/admin/charts.php`;

    const response = await fetch(url, { credentials: "include" });
    const data = await response.json()
    console.log(data)
    const provincialusers = data.data_province
    const categoryusers = data.data_category;

    const provincemap = {};
    const categorymap = {};

    provincialusers.forEach(item => {
        if (item.province) {
            provincemap[item.province] = item.clients;
        }
    });

    categoryusers.forEach(item=>{
        if (item.category){
            categorymap[item.category] = item.total_sales
        }
    });
    const orderedCategory = categories.map(cat => categorymap[cat] ?? 0)
    const orderedClients = provinces.map(prov => provincemap[prov] ?? 0);


    return {prov: orderedClients, categ: orderedCategory};

}

async function retrieveAdminUserData() {
    const res = await fetch(`${API}/api/account/role.php`, {
        credentials: "include",
    });

    const data = await res.json();

    return data
}

async function SearchDB(user_input){
    const res = await fetch(`${API}/api/admin/search.php`, {
        credentials: "include",
        method: "POST",
        body: JSON.stringify({txt: user_input})
    });

    const data = await res.json();
    console.log(data);

    return data

}

searchbar.addEventListener('keydown', async (e)=>{
    if (e.key === "Enter"){
        const result = await SearchDB(searchbar.value);
    }
})
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


    const chartData = await retrieveChartlData();
    const data = await retrieveAdminUserData();
    currentuser.innerText = data["username"];
    if (data['role'] === "ADMIN") currentrole.innerText = "Head Website Administrator";
    if (data['role'] === "MODERATOR") currentrole.innerText = "Content Moderator"
    
    adminicon.setAttribute("src", `${data["icon"]}?t=${data["timestamp"]}&tr=w-200,c-maintain_ratio,f-auto,q-85`);

    // Creating a new chart instance
    var myChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: provinces, // X-axis labels
            datasets: [{
                label: 'Users', // Dataset label for legend
                data: chartData.prov,
                backgroundColor: ['#1264B5', '#0A6624', '#D91F0B', '#95C221', '#D6910F', '#353238', '#EBE41E', '#A3268E', '#733A19'],
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
            labels: categories, // X-axis labels
            datasets: [{
                label: 'Price (ZAR)', 
                data: chartData.categ,
                backgroundColor: ['#1264B5', '#0A6624', '#D91F0B', '#95C221', '#1264B5', '#1264B5'],
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


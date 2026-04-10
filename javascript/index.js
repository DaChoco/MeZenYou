const api = window.ENV.API_URL;
const queryString = window.location.search;

const urlParams = new URLSearchParams(queryString);
const searchParams = urlParams.get('q');
const container = document.getElementById("product_section_id");
const userEmail = document.getElementById("userEmail");
const toggleBtn = document.getElementById('menuToggle');
const layout = document.getElementById('layout')
const sidenav = document.querySelector(".sidenav");

let currentCategory = "";

async function searchProducts() {
    let url = `${api}/api/browse/fullsearch.php?q=${searchParams}`;

    const response = await fetch(url);
    const data = await response.json();
    renderProducts(data);
    console.log(data)



}


async function loadProducts() {

    const min = document.getElementById("minPrice");
    const max = document.getElementById("maxPrice");


    let url = `${api}/api/browse/products.php?category=${currentCategory}&min=${min.value}&max=${max.value}`;

    const res = await fetch(url, {
        credentials: "include"
    });

    const data = await res.json();
    console.log(data);

    // Show user email
    console.log(userEmail);
    userEmail.textContent = data.user
        ? `Signed in as: ${data.user}`
        : "Not logged in";

    renderProducts(data.products);
}

function renderProducts(products) {
    container.innerHTML = "";

    products.forEach(product => {
        const card = document.createElement("article");

        card.className = "p-4 h-fit rounded-md h-auto bg-white grid grid-cols-1 grid-rows-[2fr_1fr] shadow-sm";

        card.innerHTML = `
            <a href="/pages/product.html?id=${product.id}">
                <div class="w-full h-52 flex items-center justify-center overflow-hidden bg-gray-100">
                    <img class="object-contain max-h-full" src="${product.image}" alt="Item Card" />
                </div>
            </a>

            <div class="text-section w-full flex flex-col justify-center items-start line-clamp-1 truncate">
                <span>${product.product_name}</span>
                <p class="font-bold">R${product.price}</p>
                <p class="font-semibold text-normalred">${product.category}</p>
                <p class="text-gray-800">${product.location}</p>
                <p class="text-yellow-600">${5}</p>
            </div>
        `;

        container.appendChild(card);
    });

}

document.addEventListener("DOMContentLoaded", function () {
    toggleBtn.addEventListener("click", () => {

        console.log("HELLO");


        layout.classList.toggle("lg:grid-cols-[250px_1fr]");
        layout.classList.toggle("lg:grid-cols-[64px_1fr]");

     
        sidenav.classList.toggle("h-12");
        

    });
    if (!searchParams) {
        loadProducts();
    }
    else {
        searchProducts();
    }
    document.getElementById("applyFilter").addEventListener("click", loadProducts);

    document.querySelectorAll("[data-category]").forEach(link => {
        link.addEventListener("click", (e) => {
            e.preventDefault();
            console.log("HELLO")
            currentCategory = link.dataset.category;
            loadProducts();
        });

        document.getElementById("applyFilter").addEventListener("click", loadProducts);





    });

})





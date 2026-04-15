const API_URL = window.ENV.API_URL;
const container = document.getElementById("product_section_id");

document.addEventListener("DOMContentLoaded", async () => {
    await loadSellerProducts();
});

async function loadSellerProducts() {
    const response = await fetch(`${API_URL}/api/account/seller/retrieve.php`);

    const data = await response.json();

    console.log(data)

    if (data.redirect) {
        window.location.href = data.redirect;
        return;
    }

    const products = data.products;
    renderProducts(products);
}

function renderProducts(products) {
    container.innerHTML = "";

    products.forEach((product) => {
        const card = document.createElement("article");
        let icon = '';
        let warningmsg = '';
        if (product.is_active === 1){
            icon = 'fa-x';
            warningmsg = 'Do you want to delete this product?'
        }
        else{
             icon = 'fa-check';
             warningmsg = 'Do you want to reinstate this product?'
        }

        card.className =
            "relative p-4 rounded-md bg-white grid grid-cols-1  shadow-sm";

        card.innerHTML = `
            <button class="delete-btn absolute top-0 right-0 w-10 h-10 rounded-md bg-red-500 text-white p-1 flex items-center justify-center shadow-md hover:bg-red-600">
            <i class="fa-solid ${icon} fa-2x"></i>
            </button>
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
            </div>
        `;

        container.appendChild(card);

        const deleteBtn = card.querySelector(".delete-btn");

        deleteBtn.addEventListener("click", async (e) => {
            e.stopPropagation();
            e.preventDefault();

            console.log("Delete product:", product.id);
            if (confirm(warningmsg)) {
                const active = Boolean(product.is_active)
                const response = await fetch(`${API_URL}/api/account/seller/active.php`,
                    {   credentials: "include", 
                        body: JSON.stringify({ pid: product.id, active: !active }), 
                        method: "POST" 
                    })
                const data = await response.json()

                if (data.success){
                    alert(data.message);
                }
                console.log(data)

            } else {
       
                return;

            }






        });
    });
}

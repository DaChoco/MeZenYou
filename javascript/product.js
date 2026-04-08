const queryString = window.location.search;
const API_URL = window.ENV.API_URL;
const urlParams = new URLSearchParams(queryString);

document.addEventListener('DOMContentLoaded', async ()=>{
    const section_container = document.getElementById('item-display');

    async function loadProduct(){
    const ID = urlParams.get('id');
    let url = `${API_URL}/api/item.php?id=${ID}`;

    const response = await fetch(url, {credentials: "include"})
    const data = await response.json()
    console.log(data)

    renderProduct(data.product, data.user);
    loadReviews(ID);

}

async function loadReviews(ID){
    const response = await fetch(`${API_URL}/api/browse/retrievereviews.php?pid=${ID}`);
    const data = await response.json()

    const reviewzone = document.getElementById('commenthere');

    console.log(data.items)

    data.items.forEach(item=>{

        const review = document.createElement('article')
        review.className = "border-2 border-current p-5"
        const unixTimestamp = item.timestamp;
        const date = new Date(unixTimestamp * 1000);

        review.innerHTML = `<span class="flex flex-row justify-between">
                <p>USER: #${item.uID}</p><p>Rating: ${item.rating}</p><p>${date.toUTCString()}</p>
            </span>
            <p>${item.comment}</p>`


        reviewzone.appendChild(review)
    })
    
}

async function renderProduct(product, user){

    section_container.innerHTML =
    `
    <div class="image-section">
            <img src="${product['image']}" alt="" class="w-1/2 mx-auto my-0">
        </div>

        <div class="add-to-cart-or-buy row-span-2 bg-white border-2 border-red-500">

            <div class="top-fourth border-b-2 border-gray-400">
                <h1 class="text-4xl">${product['product_name']}</h1>
                <p>By ${product['author']}</p>
                <p class="text-xl">February 2026</p>

            </div>

            <div class="middle-fourth border-b-2 border-gray-400 py-2">
                <span class="text-4xl font-bold">R${product['price']}</span>
                <p>All prices include VAT</p>
                <p>Get it Tommorrow for an extra R30.00</p>
                <p>Deliver to ${user} - Sea Point, Cape Town</p>
                <span class="text-lg text-green-700">IN STOCK</span>
            </div>

             <div class="lower-fourth flex flex-col space-y-5 rounded-sm [&_*]:w-full">
                <a href="/pages/msg.html"><button type="button" class="p-3 shadow-md bg-slate-700 text-white rounded-sm hover:bg-hoverbtnred duration-150">MSG Seller</button></a>
                <input type="number" placeholder="Quantity" class="p-3 outline-none border border-black">
                <button type="button" class="bg-slate-800 text-white rounded-sm p-3 shadow-md">Add to Cart</button>
                <a href="/pages/checkout.html"><button type="button" class="bg-red-700 hover:bg-hoverbtnred text-white rounded-sm p-3 shadow-md">Proceed to Checkout</button></a>
            </div>

            <div class="[&>*]:text-lg space-y-5">
                <p class="text-yellow-600">${5}</p>
                <p>Seller: ${product['seller_name']}</p>
                <p>Ships From: ${product['location']}</p>
                <p>Payment: Secure transaction</p>
            </div>

        </div>

        <div class="product-description bg-white w-full p-5 h-full self-end border-2 border-red-500">
            <span class="font-bold text-gray-700 text-3xl">Description</span>
            
            <p>${product['descriptiontxt']}</p>
        </div>

    `

}
    loadProduct()
})
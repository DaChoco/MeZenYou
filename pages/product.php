<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../dist/output.css" />
    <title>Product</title>
    <script src="/javascript/includefooter.js"></script>
    <script src="/javascript/includetopnav.js"></script>
</head>
<body>
    <div id="topnav"></div>

    <section class="product-section grid grid-cols-2 grid-rows-2 gap-2 m-8">

        <div class="image-section">
            <img src="../images/37c5f1ca-d930-432c-9e4e-0c632f954b85.png" alt="" class="w-1/2 mx-auto my-0">
        </div>

        <div class="add-to-cart-or-buy row-span-2 bg-white border-2 border-red-500">

            <div class="top-fourth border-b-2 border-gray-400">
            <h1 class="text-4xl">Love Bullet Vol. 2</h1>
            <p>By Inee</p>
            <p class="text-xl">February 2026</p>
            
            </div>

            <div class="middle-fourth border-b-2 border-gray-400 py-2">
                <span class="text-4xl font-bold">R300.00</span>
                <p>All prices include VAT</p>
                <p>Get it Tommorrow for an extra R30.00</p>
                <p>Deliver to END USER - Sea Point, Cape Town</p>
                <span class="text-lg text-green-700">IN STOCK</span>
            </div>

            <div class="lower-fourth flex flex-col space-y-5 [&>*]:py-3 rounded-sm [&>*]:shadow-md">
                <button type="button" class="bg-slate-700 text-white rounded-sm hover:bg-hoverbtnred duration-150">MSG Seller</button>
                <input type="number" placeholder="Quantity">
                <button type="button" class="bg-slate-800 text-white rounded-sm">Add to Cart</button>
                <button type="button" class="bg-red-700 hover:bg-hoverbtnred text-white rounded-sm">Proceed to Checkout</button>
            </div>

            <div class="[&>*]:text-lg space-y-5">
                <p>Seller: Jeffery Van Rooyen</p>
                <p>Ships From: Amazon Cape Town Warehouse</p>
                <p>Payment: Secure transaction</p>
            </div>

        </div>

        <div class="product-description bg-white w-full p-5 h-full self-end border-2 border-red-500">
            <span class="font-bold text-gray-700 text-3xl">Description</span>
            <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Perspiciatis voluptate earum alias. Rem illum voluptatum cupiditate similique voluptates? Quae excepturi quam soluta facilis necessitatibus vel molestiae quaerat dicta? Aliquid impedit odit, iste facilis magnam nam? Culpa ea aspernatur nisi placeat, eveniet, ratione distinctio, aliquam quidem mollitia molestias numquam natus consequuntur!</p>
            <br>
            <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Odio ad deserunt nam quae quo. Fugiat non et quas animi consequatur quos eius a voluptatum corrupti, deleniti adipisci doloribus illo. Magnam!</p>
        </div>

    </section>

    <section class="review-section w-8/12 h-auto mx-auto p-2 flex flex-col space-y-5 ">
        <span class="text-xl">Reviews</span>
        <div class="border-2 border-current p-5">
        <span class="flex flex-row"><p>USERNAME</p> - <p>USER REVIEW</p></span>
        <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Commodi nisi culpa impedit veritatis placeat reiciendis nihil cupiditate odit delectus reprehenderit.</p>
        </div>
    </section>

    <div id="footer"></div>
    
</body>
</html>

<?php
// Example: Dummy Output
$products = [
    [
        "name" => "SHY Vol. 8",
        "price" => 280,
        "category" => "Comics/Manga",
        "location" => "Cape Town",
        "image" => "/images/SHYVol8.webp",
        "rating" => "★★★★☆",
        "id" => 1
    ],
    [
        "name" => "Love Bullet Vol. 2",
        "price" => 310,
        "category" => "Comics/Manga",
        "location" => "Durban",
        "image" => "/images/37c5f1ca-d930-432c-9e4e-0c632f954b85.png",
        "rating" => "★★★★★",
        "id" => 2
    ],
    [
        "name" => "Iphone 17",
        "price" => 19999,
        "category" => "Electronics",
        "location" => "Cape Town",
        "image" => "https://m.media-amazon.com/images/I/61X5FknDWuL._AC_SL1500_.jpg",
        "rating" => "★★★★★",
        "id" => 3
    ]
];


?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="./dist/output.css" />
    <title>Home</title>
    <script src="/javascript/includefooter.js"></script>
    <script src="/javascript/includetopnav.js"></script>
</head>

<body class="bg-slate-200">
    <!--Reusable component-->
    <div id="topnav"></div>

    <div class="grid grid-cols-3 grid-rows-1">
        <div class="sidenav col-span-1 justify-start flex flex-col items-start p-10 w-4/5 bg-white space-y-3">
            <h4 class="font-bold text-2xl py-5 w-full border-b border-gray-400">
                Shop By Department
            </h4>

            <ul
                class="w-full [&>*]:w-full [&>*]:text-xl justify-start [&>*]:px-1 [&>*]:py-2 duration-200 hover:[&>*]:bg-red-700  hover:[&>*]:text-white">
                <li class="font-bold">All Departments</li>
                <li><a href="/index.php?category=novels">Novels</a></li>
                <li><a href="/index.php?category=comicsmanga">Comics/Manga</a></li>
                <li><a href="/index.php?category=videogames">Video Games</a></li>
                <li><a href="/index.php?category=electronics">Electronics</a></li>
                <li><a href="/index.php?category=furniture">Furniture</a></li>
                <li><a href="/index.php?category=office">Office</a></li>
                <li><a href="/index.php?category=beauty">Beauty</a></li>
            </ul>

            <h4 class="font-bold text-2xl py-5 w-full border-b border-gray-400 my-3">Shop By Price Tag</h4>
            <div class="space-y-4 [&>input]:border-2 [&>input]:border-normalred">
                <input type="number" placeholder="Min" class="p-2 outline-none">
                <p>to</p>
                <input type="number" placeholder="Max" class="p-2 outline-none">

                
            </div>

            <button class="bg-normalred p-4 text-white font-semibold w-2/5">Apply</button>

            <h4 class="font-bold text-2xl py-5 w-full border-b border-gray-400 my-3">Help & Settings</h4>
            <ul class="[&>*]:text-gray-600">
                <li><a href="/pages/account.php">My Account</a></li>
                <li><a href="/pages/auth.php?type=seller">Become a Seller</a></li>
            </ul>

        </div>

        <section class="grid grid-cols-[repeat(auto-fill,minmax(160px,1fr))] grid-rows-2 gap-6 col-span-2 p-10 max-h-full [&>*]:shadow-sm">
            <?php foreach ($products as $product): ?>
        <!--EXAMPLE OF CHARACTER CARD-->
            <article class="p-4 h-fitrounded-md h-auto bg-white grid grid-cols-1 grid-rows-[2fr_1fr]">
                <a href="./pages/product.php?id=<?php echo $product['id']; ?>">
                    <div class="w-full h-52 flex items-center justify-center overflow-hidden bg-gray-100">
                        <img class="object-contain max-h-full" src="<?php echo $product['image']; ?>" alt="Item Card" />
                    </div>
                </a>

                <div class="text-section w-full flex flex-col justify-center items-center">
                        <span class=""><?php echo $product['name']; ?></span>
                        <p class="font-bold">R<?php echo $product['price']; ?></p>
                        <p class="font-semibold text-normalred"><?php echo $product['category']; ?></p>
                        <p class="text-gray-800"><?php echo $product['location']; ?></p>
                        <p class="text-yellow-600"><?php echo $product['rating']; ?></p>
                    </div>
            </article>
            <?php endforeach; ?>
        </section>
    </div>

    <!--Reusable component-->
    <div id="footer"></div>
</body>

</html>
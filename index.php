
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
        <div class="sidenav col-span-1 justify-start flex flex-col items-start p-10 w-4/5 bg-white">
            <h4 class="font-bold text-2xl py-5 w-full border-b border-gray-400">
                Shop By Department
            </h4>

            <ul
                class="w-full [&>*]:w-full [&>*]:text-xl justify-start [&>*]:px-1 [&>*]:py-2 duration-200 hover:[&>*]:bg-red-700  hover:[&>*]:text-white">
                <li class="font-bold">All Departments</li>
                <li>Novels</li>
                <li>Comics/Manga</li>
                <li>Video Games</li>
                <li>Electronics</li>
                <li>Furniture</li>
                <li>Office</li>
                <li>Beauty</li>
            </ul>

            <h4 class="font-bold text-2xl py-5 w-full border-b border-gray-400 my-3">Shop By Price Tag</h4>
            <div class="space-y-4 [&>input]:border-2 [&>input]:border-normalred">
                <input type="number" placeholder="Min" class="p-2 outline-none">
                <p>to</p>
                <input type="number" placeholder="Max" class="p-2 outline-none">
            </div>

            <h4 class="font-bold text-2xl py-5 w-full border-b border-gray-400 my-3">Help & Settings</h4>
            <ul class="[&>*]:text-gray-600">
                <li><a href="/pages/account.php">My Account</a></li>
                <li><a href="/pages/auth.php"></a>Become a Seller</li>
            </ul>

        </div>

        <section class="grid grid-cols-[repeat(auto-fill,minmax(160px,1fr))] grid-rows-2 gap-6 col-span-2 p-10 max-h-full [&>*]:shadow-sm">
            <?php foreach ($products as $product): ?>
        <!--EXAMPLE OF CHARACTER CARD-->
            <article class="p-4 h-fitrounded-md h-auto bg-white">
                <a href="./pages/product.php?id=<?php echo $product['id']; ?>">
                    <div class="w-auto">
                        <img class="object-contain w-full" src="<?php echo $product['image']; ?>" alt="Item Card" />
                    </div>

                    <div class="text-section w-full flex flex-col justify-center items-center">
                        <span class="text-lg"><?php echo $product['name']; ?></span>
                        <p class="font-bold">R<?php echo $product['price']; ?></p>
                        <p class="font-semibold text-normalred"><?php echo $product['category']; ?></p>
                        <p class="text-gray-800"><?php echo $product['location']; ?></p>
                        <p class="text-yellow-600"><?php echo $product['rating']; ?></p>
                    </div>
                </a>
            </article>
            <?php endforeach; ?>
        </section>
    </div>

    <!--Reusable component-->
    <div id="footer"></div>
</body>

</html>
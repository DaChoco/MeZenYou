<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout Page</title>
    <script src="/javascript/includefooter.js"></script>
    <script src="/javascript/includetopnav.js"></script>
    <link rel="stylesheet" href="/dist/output.css">
</head>

<body>

    <div id="topnav"></div>

    <section class="grid md:grid-cols-2 grid-rows-1">

        <div class="m-5 space-y-10 flex flex-col justify-between">
            <form action="" class="p-10 bg-white [&_input]:outline-none [&_input]:border-2 h-full">
                <h1 class="font-bold text-3xl">Checkout</h1>

                <div class="flex flex-col [&_input]:p-2 space-y-5">
                    <span>Shipping Information</span>
                    <div class="flex flex-row [&>*]:bg-normalred [&>*]:text-white [&>*]:w-1/4 space-x-5">
                        <button class="p-5 rounded-md">Delivery</button>
                        <button class="p-5 rounded-md">Pick Up</button>
                    </div>

                    <div class="flex flex-col">
                        <label for="" class="font-semibold">Full Name:</label>
                        <input type="text" placeholder="Full Name...">
                    </div>

                    <div class="flex flex-col">
                        <label for="" class="font-semibold">Email Address:</label>
                        <input type="email" placeholder="Email Address...">
                    </div>

                    <div class="flex flex-col">
                        <label for="" class="font-semibold">Cellphone Number:</label>
                        <input type="tel" placeholder="Phone Number...">
                    </div>

                    <div class="flex flex-row [&>*]:flex [&>*]:flex-col space-x-2 w-full h-auto [&_div]:w-1/4 mx-auto">
                        <div>
                            <label for="">Province</label>
                            <input type="text" name="" id="" required>
                        </div>

                        <div>
                            <label for="">City</label>
                            <input type="text" required>
                        </div>

                        <div>
                            <label for="">Postal</label>
                            <input type="text" required>
                        </div>

                        <div>
                            <label for="">Street</label>
                            <input type=" text" name="" id="" required>
                        </div>

                    </div>


                </div>
            </form>

            <div class="bg-white p-10 h-fit">
                <span class="text-lg font-semibold">Payment Method</span>
                <div class="flex flex-row space-x-5">
                    <input class="checked:text-black text-gray-600" type="radio" name="" id="">
                    <p>Yoco Payment</p>

                    <input type="radio" name="" id="">
                    <p>Online Payment</p>

                    <input type="radio">
                    <p>Online Payment</p>
                </div>
            </div>

        </div>

        <div class="m-5 p-10 bg-white [&_input]:outline-none [&_input]:border-2 space-y-10 overflow-hidden ">
            <span class="font-bold text-3xl">Order Summary</span>

            <div class="flex flex-col flex-1 justify-between space-y-5 md:h-[700px]">
                <!--CART ITEMS ZONE-->
                <div class="overflow-y-auto space-y-10 border-b-2 flex flex-1 flex-col">
                    <article class="grid grid-cols-[1fr_2fr_1fr]  gap-3">
                        <img src="/images/SHYVol8.webp" alt="" class="object-contain">
                        <div class="flex flex-col justify-between">

                            <div>
                                <span class="font-semibold text-lg">Shy; Vol. 8</span>
                                <p>1x</p>
                            </div>

                            <p class="font-bold text-xl">R280.00</p>
                        </div>

                        <input type="number" class="h-2/5">
                    </article>

                    <article class="grid grid-cols-[1fr_2fr_1fr]  gap-3">
                        <img src="/images/SHYVol8.webp" alt="" class="object-contain">
                        <div class="flex flex-col justify-between">

                            <div>
                                <span class="font-semibold text-lg">Shy; Vol. 8</span>
                                <p>1x</p>
                            </div>

                            <p class="font-bold text-xl">R280.00</p>
                        </div>

                        <input type="number" class="h-2/5">
                    </article>


                </div>
<!--Summary-->
                <form class="flex flex-col [&>*]:flex [&>*]:flex-row [&>*]:justify-between [&_span]:font-bold [&_span]:text-lg">

                    <div>
                        <p>Subtotal: </p>
                        <span>R1300</span>
                    </div>

                    <div>
                        <p>Shipping: </p>
                        <span>R100</span>
                    </div>

                    <div>
                        <p>Discount: </p>
                        <span>0%</span>
                    </div>

                    <div>
                        <p>Total: </p>
                        <span>R1400</span>
                    </div>

                    <button class="bg-normalred text-white w-1/3 self-end p-3">Purchase</button>
                </form>

            </div>


        </div>


    </section>

    <div id="footer"></div>

</body>

</html>
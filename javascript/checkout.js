const API_URL = window.ENV.API_URL;
const uname = document.getElementById('nameinput');
const email = document.getElementById('emailinput');
const tel = document.getElementById('phoneinput');

const province = document.getElementById('provinceinput');
const city = document.getElementById('cityinput');
const street = document.getElementById('streetinput');
const postal = document.getElementById('postalinput');

const cartitemzone = document.getElementById('cartitemzoneid');
const shippingprice = document.getElementById('shippingid')
const addedprice = document.getElementById('addedpriceid');
const totalprice = document.getElementById('totalpriceid')

let deliverytype = "delivery";

document.getElementById('deliverybtn').addEventListener('click', () => {
  deliverytype = "delivery"
  shippingprice.innerText = `R30.00`
  alert("Order set to delivery!")
});
document.getElementById('pickupbtn').addEventListener('click', () => {
  deliverytype = "pick up"
  shippingprice.innerText = "R0.00"
  alert("Order set to pick up!")
})

async function loadSignedInData() {
  //This function loads up your address on your behalf if youre signed in
  let url = `${API_URL}/api/cart/userdata.php`

  const response = await fetch(url,
    { credentials: "include" });
  const data = await response.json()

  if (data.success) {
    const addressarray = data.address.split("-")


    street.value = `${addressarray[0]}, ${addressarray[1]}`;
    city.value = addressarray[2];
    province.value = addressarray[3];
    postal.value = addressarray[4];

    tel.value = data.phone;
    uname.value = data.username;
    email.value = data.email;
  }
  else {
    console.log("USER APPEARS TO LACK A CART")
    console.log(data);
  }


}

async function loadCurrentCart() {
  let url = `${API_URL}/api/cart/retrieve.php`
  const response = await fetch(url,
    { credentials: "include" });
  const data = await response.json()

  console.log(data);
  return data;

}

async function renderCart(cart) {
  cartitemzone.innerHTML = ""

  if (!cart) {
    return;
  }

  cart.forEach(item => {
    const cartitemcard = document.createElement('article');
    cartitemcard.className = 'grid grid-cols-[1fr_2fr_1fr] gap-3'
    cartitemcard.innerHTML = `<img src=${item.image} alt="" class="object-contain">
                        <div class="flex flex-col justify-between">

                            <div>
                                <span class="font-semibold text-lg">${item.product_name}</span>
                                <p>${item.quantity}x</p>
                            </div>

                            <p class="font-bold text-xl">R${item.totalprice.toFixed(2)}</p>
                        </div>

                        <input data-pid=${item.product_id} min="0" placeholder="type..." value=${item.quantity}  type="number" class="h-fit p-3 text-lg">`
    cartitemzone.appendChild(cartitemcard);
    const input = cartitemcard.querySelector("input");

    input.addEventListener("keydown", async (e) => {
      //Give each Input bar an event listener.
      if (e.key === "Enter") {
        const pid = input.dataset.pid;
        const quantity = parseInt(input.value);

        const response = await fetch(`${API_URL}/api/cart/update.php?pid=${pid}&quantity=${quantity}`, {
          method: "POST",
          credentials: "include"
        });

        const data = await response.json();

        if (data.success) {
          const cart = await loadCurrentCart();
          renderCart(cart.cart);
        }
      }
    });



  })
  let sum = 0
  for (i = 0; i < cart.length; i++) {
    sum = sum + cart[i]["totalprice"];
  }
  addedprice.innerText = `R${sum.toFixed(2)}`

  if (deliverytype === "delivery") {
    shippingprice.innerText = `R30.00`
  }

  totalprice.innerText = `R${sum + 30}`

}

document.addEventListener('DOMContentLoaded', async () => {
  await loadSignedInData()
  const cartapi = await loadCurrentCart()

  renderCart(cartapi.cart);

})
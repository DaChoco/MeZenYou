const api = window.ENV.API_URL;
const order_zone = document.getElementById("order-zone");

const streetaddrtxt = document.getElementById('accStreetID');
const phonetxt = document.getElementById('phoneNumID');

const newpw1 = document.getElementById('newpw1')
const newpw2 = document.getElementById('newpw2')

const iconselect = document.getElementById('iconupload')

function capitalizeFirst(str) {
  return str.charAt(0).toUpperCase() + str.slice(1);
}

function dateConversion(sqlDate){

const date = new Date(sqlDate.replace(" ", "T"));

const formatted = date.toLocaleDateString("en-GB", {
  day: "2-digit",
  month: "long",
  year: "numeric"
});



return formatted
}

document.getElementById("iconsubmit").addEventListener('submit', async (e) =>{
  e.preventDefault();
  const formData = new FormData();
  const selectedFile = iconselect.files[0];
  formData.append("image", selectedFile);
  const response = await fetch(`${api}/api/account/uploadicon.php`, {credentials: "include", method: "POST", body: formData})
  const data = await response.json()

  alert(data.message);
  console.log(data)

})

document.getElementById("pwsubmit").addEventListener('submit', async (e) =>{
  e.preventDefault();
  if (newpw1.value !== newpw2.value){
    alert("Passwords Don't Match");
    return;
  }
  const response = await fetch(`${api}/api/account/updatepasswd.php`, {credentials: "include", method: "POST", body: JSON.stringify({password: newpw1.value})})
  const data = await response.json();

  if (data){
    alert(data.message);
  }
})
document.getElementById("logoutbtn").addEventListener("click", async () => {
  const response = await fetch(`${api}/api/auth/logout.php`, {
    method: "POST",
    credentials: "include",
  });

  const data = await response.json();

  if (data.success) {
    alert("SUCCESSFULLY LOGGED OUT OF SESSION");
  }
  window.location.href = "/";
});

async function loadUser() {
  const res = await fetch(`${api}/api/account/role.php`, {
    credentials: "include",
  });

  const data = await res.json();

  if (data.redirect) {
    console.log("User not logged in");
    window.location.href = data.redirect;
  }
  const role = data.role;
  console.log(data);

  if (role === "seller" || role === "ADMIN") {
    document.getElementById("seller-nav").classList.remove("hidden");
    document.getElementById("seller-tools").classList.remove("hidden");
  }
  streetaddrtxt.innerText = data.address.replaceAll("-", ", ");
  phonetxt.innerText = `Phone Number: ${data.phone}`;
}

document.addEventListener("DOMContentLoaded", async () => {
  loadUser();
  let is_delivered = ``;

  const response = await fetch(`${api}/api/account/retrieveorders.php`, {
    credentials: "include",
  });

  const data = await response.json();
  console.log(data.orders);

  if (data.orders) {
    const orders = data.orders;

    orders.forEach((order) => {
      const card = document.createElement("article");

      if (order["order_status"] !== "cancelled") {
        is_delivered = `<p class="text-green-600 mr-2">${capitalizeFirst(order["order_status"])}</p>`;
      } else {
        is_delivered = `<p class="text-normalred mr-2">Cancelled</p>`;
      }
      card.className =
        "order-box grid grid-cols-[25%_75%] gap-x-3 grid-rows-1 bg-white p-3";
      card.innerHTML = `
    <img class="object-contain" src="${order["image"]}" alt="">
        <div class="flex flex-col">
          <h4 class="sm:text-base lg:text-xl font-bold">Order #${order["id"]}</h4>
          <span class="flex flex-row">
             ${is_delivered}
          </span>
          <ul>
              <li class="sm:text-base lg:text-lg">${order["name"]}</li>
          </ul>

            <p class="sm:text-base lg:text-lg">ORDER PLACED: ${dateConversion(order["created_at"])}</p>
            <p class="sm:text-base lg:text-lg">Quantity: ${order["quantity"]}x</p>
            <p class="sm:text-base lg:text-lg">Price Per Item: R${order["price"].toFixed(2)}</p>
            <p class="sm:text-base lg:text-lg font-bold">Order #${order["id"]} Total: R${order["total_price"].toFixed(2)}</p>
        </div>`;
      order_zone.appendChild(card);
    });
  }
});

document
  .getElementById("address-submit")
  .addEventListener("click", async (e) => {
    e.preventDefault();
    const phonenumber = document.getElementById("phoneselect").value;
    const streetaddress = document.getElementById("streetselect").value;
    const suburbaddress = document.getElementById("suburbselect").value;
    const cityaddress = document.getElementById("cityselect").value;
    const province = document.getElementById("provinceselect").value;
    const postalcode = document.getElementById("postcodeid").value;
    const deliveryinstructions = document.getElementById("del-instruct").value;

    const fields = {
      street: streetaddress,
      phone: phonenumber,
      suburb: suburbaddress,
      city: cityaddress,
      province: province,
      postalcode: postalcode,
      delinstructions: deliveryinstructions,
    };
    const response = await fetch(`${api}/api/account/updateaddress.php`, {
      method: "POST",
      credentials: 'include',
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(fields),
    });

    const data = await response.json();

    //NOTE FOR LATER. INCORPORATE better MSG RESPONSE later
    alert(data.message);
  });

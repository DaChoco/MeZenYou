const api = window.ENV.API_URL;
const order_zone = document.getElementById("order-zone");

const streetaddrtxt = document.getElementById('accStreetID');
const phonetxt = document.getElementById('phoneNumID');

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
  console.log(data.dummy);

  if (data.dummy) {
    const orders = data.dummy;

    orders.forEach((order) => {
      const card = document.createElement("article");

      if (order["delivered"] !== "cancelled") {
        is_delivered = `<p class="text-green-600 mr-2">Delivered </p>${order["delivered"]}`;
      } else {
        is_delivered = `<p class="text-normalred mr-2">Cancelled </p>`;
      }
      card.className =
        "order-box grid grid-cols-[25%_75%] gap-x-3 grid-rows-1 bg-white p-3";
      card.innerHTML = `
    <img class="object-contain" src="${order["image"]}" alt="">
        <div class="flex flex-col">
          <h4 class="text-xl font-bold">Order #${order["id"]}</h4>
          <span class="flex flex-row">
             ${is_delivered}
          </span>
          <ul>
              <li>${order["name"]}</li>
          </ul>

            <p>ORDER PLACED: ${order["placed"]}</p>
            <p>Total: R${order["price"]}</p>
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

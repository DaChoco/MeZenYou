const api = window.ENV.API_URL;
const params = new URLSearchParams(window.location.search);
const type = params.get("type");

const seller_registration = document.getElementById("buyerRegister");
const buyer_login = document.getElementById("loginid");
const buyer_register = document.getElementById("registerid");

function showElement(element) {
  element.classList.remove("hidden");
  element.classList.add("flex");
}

function hideAll() {
  seller_registration.classList.add("hidden");
  buyer_login.classList.add("hidden");
  buyer_register.classList.add("hidden");

  seller_registration.classList.remove("flex");
  buyer_login.classList.remove("flex");
  buyer_register.classList.remove("flex");
}

async function CheckIfLoggedIn(){
  let url = `${api}/api/auth/checklogged.php`

  const response = await fetch(url, {credentials: "include"});
  const data = await response.json();

  if (data.logged === true){
    window.location.href = data.redirect;
  }
}

document.addEventListener("DOMContentLoaded", () => {
  CheckIfLoggedIn();
  hideAll();



  if (type === "seller") {
    showElement(seller_registration);
  } else if (type === "login") {
    showElement(buyer_login);
  } else if (type === "register") {
    showElement(buyer_register);
  }
});

document.getElementById('buyerRegister').addEventListener("submit", async (e)=>{
  e.preventDefault();
  const email_seller = document.getElementById('selleremail');
  const name_seller = document.getElementById('sellername');
  const address_seller = document.getElementById('selleraddress');
  const password_seller = document.getElementById('sellerpw');

  const uaddress = address_seller.value.replace(", ", "-")

  if (email_seller.value === null || password_seller.value === null) {
        console.log("INCOMPLETE DATA")
    } else {
      const res = await fetch(`${api}/api/auth/register.php`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        credentials: "include",
        body: JSON.stringify({ email: email_seller.value, password: password_seller.value, username: name_seller.value, is_seller: true, address: uaddress }),
      });

      const data = await res.json();
      if (data.redirect){
        window.location.href = data.redirect;
      }
      else{
        alert(data.message)
        console.log(data.error)
      }
      console.log(data);
    }


})

//CALL /api/register.php
document.getElementById("registerid").addEventListener("submit", async (e) => {
    e.preventDefault(); 

    const email_register = document.getElementById("register_email_id").value;
    const password_register = document.getElementById("register_password_id").value;
    const username_register = document.getElementById("register_username_id").value;

    if (email_register === null || password_register === null) {
        console.log("INCOMPLETE DATA")
    } else {
      const res = await fetch(`${api}/api/auth/register.php`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        credentials: "include",
        body: JSON.stringify({ email: email_register, password: password_register, username: username_register }),
      });

      const data = await res.json();
      if (data.redirect){
        window.location.href = data.redirect;
      }
      console.log(data);
    }
  });

document.getElementById("loginid").addEventListener("submit", async (e) => {
    e.preventDefault(); 

    const email_login = document.getElementById("login_email_id").value;
    const password_login = document.getElementById("login_password_id").value;

      if (email_login === null || password_login === null) {
        console.log("INCOMPLETE DATA")
    } else {
      const res = await fetch(`${api}/api/auth/login.php`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        credentials: "include",
        body: JSON.stringify({ email: email_login, password: password_login }),
      });
      const data = await res.json();
      console.log(data);
      if (data.redirect){

        window.location.href = data.redirect;
      }
      
    }
});

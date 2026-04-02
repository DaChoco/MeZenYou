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

document.addEventListener("DOMContentLoaded", () => {
  hideAll();

  if (type === "seller") {
    showElement(seller_registration);
  } else if (type === "login") {
    showElement(buyer_login);
  } else if (type === "register") {
    showElement(buyer_register);
  }
});

//CALL /api/register.php
document.getElementById("registerid").addEventListener("submit", async (e) => {
    e.preventDefault(); 

    const email = document.getElementById("register_email_id").value;
    const password = document.getElementById("register_password_id").value;

    if (email === null || password === null) {
        console.log("INCOMPLETE DATA")
    } else {
      const res = await fetch("/api/register.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ email, password }),
      });

      const data = await res.json();
      console.log(data);
    }
  });

const API_URL = window.ENV.API_URL;
const uname = document.getElementById('nameinput');
const email = document.getElementById('emailinput');
const tel = document.getElementById('phoneinput');

const province = document.getElementById('provinceinput');
const city = document.getElementById('cityinput');
const street = document.getElementById('streetinput');
const postal = document.getElementById('postalinput');

let deliverytype = "delivery";

document.getElementById('deliverybtn').addEventListener('click', ()=>{
  deliverytype = "delivery"
  alert("Order set to delivery!")
});
document.getElementById('pickupbtn').addEventListener('click', ()=>{
  deliverytype = "pick up"
  alert("Order set to pick up!")
})

async function loadSignedInData(){
  let url = `${API_URL}/api/cart/userdata.php`

  const response = await fetch(url, 
    {credentials: "include"});
  const data = await response.json()

  if (data.success){
    const addressarray = data.address.split("-")


    street.value = `${addressarray[0]}, ${addressarray[1]}`;
    city.value = addressarray[2];
    province.value = addressarray[3];
    postal.value = addressarray[4];

    tel.value = data.phone;
    uname.value = data.username;
    email.value = data.email;
  }
  else{
    console.log("USER APPEARS TO LACK A CART")
    console.log(data);
  }
}
document.addEventListener('DOMContentLoaded', ()=>{
  loadSignedInData()

})
import {ENV} from '../variables.js'
document.getElementById('logoutbtn').addEventListener('click', async ()=>{
const response = await fetch(`${ENV.API_URL}/api/auth/logout.php`, {
  method: "POST",
  credentials: "include"
});

const data = await response.json()

if (data.success){
    alert("SUCCESSFULLY LOGGED OUT OF SESSION");
}
window.location.href = "/";
});
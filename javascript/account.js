document.getElementById('logoutbtn').addEventListener('click', async ()=>{
const response = await fetch("/api/logout.php", {
  method: "POST",
  credentials: "include"
});

const data = await response.json()

if (data.success){
    alert("SUCCESSFULLY LOGGED OUT OF SESSION");
}
window.location.href = "/";
});
const toggleBtn = document.getElementById("menuToggle");
const layout = document.getElementById("layout");
const icons = document.querySelectorAll(".fa-solid");
const custicon = document.querySelectorAll(".custom-fa")

toggleBtn.addEventListener("click", () => {
    console.log("HELLO")
    layout.classList.toggle("sidebar-collapsed");
    icons.forEach(icon => {
        icon.classList.toggle("fa-2x");
    });

    custicon.forEach(icon =>{
        icon.classList.toggle("w-4");
        icon.classList.toggle("w-8");

        icon.classList.toggle("h-4");
        icon.classList.toggle("h-8");
    })
});
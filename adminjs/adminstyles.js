document.addEventListener("sidebarLoaded", () => {
  const toggleBtn = document.getElementById("menuToggle");
  const layout = document.getElementById("layout-admin");

  // Guard clause (prevents crashes if something fails)
  if (!toggleBtn || !layout) return;

  toggleBtn.addEventListener("click", () => {
    console.log("HELLO");

    layout.classList.toggle("sidebar-collapsed");

  
    const icons = document.querySelectorAll(".fa-solid.hover\\:text-blue-500");
    const custicon = document.querySelectorAll(".custom-fa");

    icons.forEach((icon) => {
      icon.classList.toggle("fa-2x");
    });

    custicon.forEach((icon) => {
      if (icon.classList.contains("w-[1.5rem]")) {
        icon.classList.replace("w-[1.5rem]", "w-[2rem]");
      } else {
        icon.classList.replace("w-[2rem]", "w-[1.5rem]");
      }
    });
  });
});

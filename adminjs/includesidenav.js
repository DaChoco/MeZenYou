document.addEventListener("DOMContentLoaded", async () => {
    const container = document.getElementById("sidebar-container");

    const response = await fetch("/components/sidenavadmin.html");
    const html = await response.text();

    container.innerHTML = html;

    document.dispatchEvent(new Event("sidebarLoaded"))
});
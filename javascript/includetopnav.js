fetch("../components/topnav.html")
    .then(response => response.text())
    .then(html => {
        document.getElementById("topnav").innerHTML = html;
    });
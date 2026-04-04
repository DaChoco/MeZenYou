const API_URL = window.ENV.API_URL;
async function protect_admin(){
    let url = `${API_URL}/api/admin/adminguard.php`
    const response = await fetch(url, {credentials: "include"});
    const data = await response.json();

    console.log(data.message);
    if (data.logged === false){
        window.location.href = data.redirect
    }
}

protect_admin()
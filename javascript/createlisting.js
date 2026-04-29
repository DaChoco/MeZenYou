const API_URL = window.ENV.API_URL;
const submitform = document.getElementById('listingsubmitid');
const pname = document.getElementById('name')
const price = document.getElementById('price')
const category = document.getElementById('categoryselect')
const plocation = document.getElementById('location')
const author = document.getElementById('author')
const stock = document.getElementById('stock')
const description = document.getElementById('desc')

submitform.addEventListener('submit', async (e) => {
    e.preventDefault()
    const formData = new FormData();
    formData.append("product_name", pname.value);
    formData.append("price", price.value);
    formData.append("category", category.value);
    formData.append("location", plocation.value);
    formData.append("author", author.value);
    formData.append("stock", stock.value);
    formData.append("description", description.value)
    const file = document.getElementById('image');
    const selectedFile = file.files[0];
    console.log(description.value)
    formData.append("image", selectedFile);



    let url = `${API_URL}/api/account/seller/create.php`
    const response = await fetch(url, {
        credentials: "include",
        method: "POST",
        body: formData
    });
    const data = await response.json();
    console.log(data)

    if (data.status){
        console.log(data.product_id)
        alert("Congratulations, you uploaded an item, you can either keep uploading or proceed to other pages.")
    }
    else{
        alert(data.error)
    }
});
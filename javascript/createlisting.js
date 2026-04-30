const API_URL = window.ENV.API_URL;
const submitform = document.getElementById('listingsubmitid');
const pname = document.getElementById('name')
const price = document.getElementById('price')
const category = document.getElementById('categoryselect')
const plocation = document.getElementById('location')
const author = document.getElementById('author')
const stock = document.getElementById('stock')
const description = document.getElementById('desc')
const clean = (val) => DOMPurify.sanitize(val)

submitform.addEventListener('submit', async (e) => {
    e.preventDefault()
    const formData = new FormData();
    formData.append("product_name", clean(pname.value));
    formData.append("price", clean(price.value));
    formData.append("category", clean(category.value));
    formData.append("location", clean(plocation.value));
    formData.append("author", clean(author.value));
    formData.append("stock", clean(stock.value));
    formData.append("description", clean(description.value))
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
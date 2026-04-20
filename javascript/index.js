const api = window.ENV.API_URL;
const queryString = window.location.href;

const urlbar = new URL(queryString);
if (!urlbar.searchParams.has("pg")) {
    urlbar.searchParams.set("pg", "1");
    window.history.replaceState({}, "", urlbar);
}

const searchParams = urlbar.searchParams.get('q');
const pageNum = Number(urlbar.searchParams.get('pg'));
console.log(pageNum)
const container = document.getElementById("product_section_id");
const userEmail = document.getElementById("userEmail");
const layout = document.getElementById("layout");
const closenav = document.getElementById("closeid");

let currentCategory = "";

async function searchProducts() {
  let url = `${api}/api/browse/fullsearch.php?q=${searchParams}`;

  const response = await fetch(url);
  const data = await response.json();
  const reviews = await reviewscores();
  renderProducts(data, reviews);
}

async function loadProducts() {
  const min = document.getElementById("minPrice");
  const max = document.getElementById("maxPrice");

  let url = `${api}/api/browse/products.php?category=${currentCategory}&min=${min.value}&max=${max.value}&pg=${pageNum}`;

  const res = await fetch(url, {
    credentials: "include",
  });

  const data = await res.json();
  console.log(data);

  // Show user email
  console.log(userEmail);
  userEmail.textContent = data.user
    ? `Signed in as: ${data.user}`
    : "Not logged in";
  const reviews = await reviewscores();
  renderProducts(data.products, reviews);
  renderPageBoxes(data.totalpages);
}
async function reviewscores() {
  const response = await fetch(`${api}/api/browse/reviewscores.php`);
  const data = await response.json();
  const scores = data.items;

  return scores;
}
function renderProducts(products, reviews) {
  container.innerHTML = "";

  const reviewMap = {};
  reviews.forEach((r) => {
    let cleaned = r.pID.replace("PRODUCT#", "");
    reviewMap[cleaned] = r.avg;
  });

  products.forEach((product) => {
    const card = document.createElement("article");
    const avg = reviewMap[product.id] ?? 0;

    card.className = "p-4 rounded-md bg-white grid grid-cols-1  shadow-sm";

    card.innerHTML = `
            <a href="/pages/product.html?id=${product.id}">
                <div class="w-full h-52 flex items-center justify-center overflow-hidden bg-gray-100">
                    <img class="object-contain max-h-full" src="${product.image}" alt="Item Card" />
                </div>
            </a>

            <div class="text-section w-full flex flex-col justify-center items-start line-clamp-1 truncate">
                <span>${product.product_name}</span>
                <p class="font-bold">R${product.price}</p>
                <p class="font-semibold text-normalred">${product.category}</p>
                <p class="text-gray-800">${product.location}</p>
                <p class="text-yellow-600">${avg.toFixed(1)}</p>
            </div>
        `;

    container.appendChild(card);
  });
}

function renderPageBoxes(totalPages){
  const outputarea = document.getElementById('page-btns')
  const btnclasses = `p-3 border-2 border-black pgbtn hover:border-white hover:bg-hoverbtnred hover:text-white`;
  outputarea.innerHTML = "";
  const leftarrow = document.createElement("li")
  leftarrow.setAttribute('id', "leftarrowid")
  leftarrow.innerText = "<-"
  leftarrow.className = btnclasses
  leftarrow.addEventListener('click', ()=> prevPage());
  
  const pageboxcontainer = document.createElement('ul');
  pageboxcontainer.append(leftarrow)
  pageboxcontainer.className = `flex flex-row w-full p-0 relative space-x-5 justify-end`;
  for (let i = 1; i<totalPages+1; i++){
    const btncard = document.createElement('li')
    btncard.setAttribute('id', `pgnum${i}`)
    btncard.className = btnclasses;
    btncard.innerText = i;

    btncard.addEventListener('click', async ()=>{
      urlbar.searchParams.set('pg', btncard.innerText);
      window.location.href = urlbar.toString();
    })
    pageboxcontainer.append(btncard)


  }
  const rightarrow = document.createElement("li")
  rightarrow.setAttribute('id', "rightarrowid")
  rightarrow.className = btnclasses;
  rightarrow.innerText = "->"
  rightarrow.addEventListener('click', ()=> nextPage(totalPages));
  pageboxcontainer.append(rightarrow);
  outputarea.append(pageboxcontainer);
  
}

function nextPage(maxValue){
  if (pageNum < maxValue){
  urlbar.searchParams.set('pg', String(pageNum + 1))
  window.location.href = urlbar.toString();

  }
  else{
    alert("This is the highest number of pages.")
  }
  
}

function prevPage(){
  if (pageNum > 1){
    urlbar.searchParams.set('pg', String(pageNum - 1))
    window.location.href = urlbar.toString();
  }
  else{
    alert("This is the lowest number of pages.")
  }
  

}

document.addEventListener("DOMContentLoaded", function () {
  const sidenav = document.querySelector(".sidenav");

  window.addEventListener("resize", () => {
    if (!window.matchMedia("(min-width: 1024px)").matches) {
      sidenav.classList.remove("index-collapsed");
    }
  });
  closenav.addEventListener("click", () => {
    sidenav.classList.toggle("-translate-x-full");
  });

  if (!searchParams) {
    loadProducts();
  } else {
    searchProducts();
  }
  document
    .getElementById("applyFilter")
    .addEventListener("click", loadProducts);

  document.querySelectorAll("[data-category]").forEach((link) => {
    link.addEventListener("click", (e) => {
      e.preventDefault();
      console.log("HELLO");
      currentCategory = link.dataset.category;
      loadProducts();
    });

    document
      .getElementById("applyFilter")
      .addEventListener("click", loadProducts);
  });
});

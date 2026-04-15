

const url = window.ENV.API_URL;
let autocomplete_results = [];

async function renderNavbar() {
  const response = await fetch("/components/topnav.html");
  const html = await response.text();
  document.getElementById("topnav").innerHTML = html;
  const input_bar = document.getElementById("search-bar-items");
  const autocompletezone = document.getElementById("autocomplete-srch")
  const usericonzone = document.getElementById('usericonzone');

  //----------------------------------------------------
  function debounce(fn, delay) {
    let timeoutId;

    return function (...args) {
      clearTimeout(timeoutId);

      timeoutId = setTimeout(() => {
        fn.apply(this, args);
      }, delay);
    };
  }
  function initMenuToggle(){
    const sidenav = document.querySelector(".sidenav");
    const toggleBtn = document.getElementById('menuToggle');
    if (!toggleBtn || !sidenav || !layout) return;

    const isLargeScreen = () =>
        window.matchMedia("(min-width: 1024px)").matches;
        toggleBtn.addEventListener("click", () => {
        sidenav.classList.toggle('-translate-x-full')
        console.log("HELLO");
         if (isLargeScreen()) {
        layout.classList.toggle("lg:grid-cols-[250px_1fr]");
        layout.classList.toggle("lg:grid-cols-[64px_1fr]");

        
        sidenav.classList.toggle('index-collapsed')
         }
        

    });

  }
  

  function renderDropdown(items){

    items.forEach(e => {

        const listitem = document.createElement('li');
        listitem.className = "hover:font-semibold hover:text-red-700";
        listitem.innerHTML =
        `
        <a href="/pages/product.html?id=${e.id}">${e.product_name}</a>
        `
        autocompletezone.appendChild(listitem);
        
        
    });

  }
  //-------------------------------------------

  const itemautocomplete = async (e) => {
    const searched_item = e.target.value;
    autocompletezone.replaceChildren()

    if (searched_item.length < 2) {
      autocomplete_results = [];
      
      return;
    }

    try {
      const response = await fetch(
        `${url}/api/browse/autocomplete.php?query=${searched_item}`,
      );

      if (!response.ok){
        throw new Error(`HTTP error! Status: ${response.status}`);
      }
      const data = await response.json();
      autocomplete_results = data.results;

      if (!autocomplete_results.length){
        autocompletezone.replaceChildren();
        return;
      }
      renderDropdown(autocomplete_results);


    } catch (error) {
      console.log(error);
    }
  };

  function initAutocomplete() {
    const debouncedAutocomplete = debounce(itemautocomplete, 250);

    input_bar.addEventListener("input", debouncedAutocomplete);
  }
  initAutocomplete();
  initMenuToggle();

  document.addEventListener('click', (e)=>{
    const is_inside = input_bar.contains(e.target) || autocompletezone.contains(e.target);

    if (!is_inside) {
    autocompletezone.replaceChildren(); 
  }

  
}
)
  input_bar.addEventListener('keydown', (e)=>{
    if (e.key === "Enter"){
      //actual searching logic is in index.js.
      window.location.href = `/index.html?q=${input_bar.value}`
    }
  })

  document.getElementById('searchclickbtnred').addEventListener('click', ()=>{
    window.location.href = `/index.html?q=${input_bar.value}`
  })

  const iconresponse = await fetch(`${url}/api/account/role.php`, {credentials: "include"});
  const user = await iconresponse.json()
  if (user.icon){
    
    usericonzone.setAttribute("data-username", user.username);
    usericonzone.innerHTML = `<img class="rounded-full" src=${user.icon} alt="">`;
  }
  console.log(user);
  
}




renderNavbar();


const API = window.ENV.API_URL;
let orderState = [];

const statuscolors = {
    COMPLETED: "bg-[#02b835]",
    pending: "bg-[#d18902]",
    cancelled: "bg-[#eb1000]"
};

const myTable = document.getElementById('order-table');
const queryString = window.location.href;
const urlbar = new URL(queryString);
if (!urlbar.searchParams.has("pg")) {
    urlbar.searchParams.set("pg", "1");
    window.history.replaceState({}, "", urlbar);
}

const rowzone = document.getElementById('row-id-zone')
const pageNum = Number(urlbar.searchParams.get('pg'));

document.addEventListener('DOMContentLoaded', async () => {
    await loadOrders();
})

async function loadOrders() {
    let url = `${API}/api/admin/orders.php?pg=${pageNum}`;

    const response = await fetch(url);
    const data = await response.json();
    orderState = data.orders || [];
    renderTableRows()
    renderPageBoxes(data.totalpages, data.rows);

}

async function changeOrderStatus(user_input, orderID){
    let url = `${API}/api/admin/modifyorder.php`;
    const body = {id: orderID, status: user_input}
    const response = await fetch(url, {credentials: "include", method: "POST", body: JSON.stringify(body)});
    const data = await response.json();
    alert(data.message);

    orderState = orderState.map(order => {
        if (order.id !== userid) return order;

        return {
            ...user,
            status: user_input ?? order.status,
        };
    });

    renderTableRows();
}

function renderTableRows() {
    
    console.log(orderState)
    myTable.innerHTML = "";
    if (!orderState.length) {
        return;
    }
    const tableheadings = document.createElement('tr')
    tableheadings.innerHTML = `<th>Buyer</th>
                            <th>Seller</th>
                            <th>Date</th>
                            <th>Method</th>
                            <th>Price</th>
                            <th>Status</th>`;
    tableheadings.className = `bg-darkgray text-white`;
    myTable.append(tableheadings);

    orderState.forEach(order=>{
        const tablerow = document.createElement('tr')
        tablerow.className = "[&>*]:p-2"

        const order_class = statuscolors[order.status] || "";

        tablerow.innerHTML = `<th>${order.buyer}</th>
                            <th>${order.seller}</th>
                            <th>${order.date}</th>
                            <th>${order.payment}</th>
                            <th>${order.total_price}</th>
                            <th id="STATUS-${order.id}" contenteditable="true" class="${order_class}">${order.status}</th>`

        myTable.append(tablerow);
        const status_element = tablerow.querySelector(`#STATUS-${order.id}`);

        status_element.addEventListener('keydown', async (e) => {

            if (e.key === "Enter") {
                e.preventDefault();
                await changeOrderStatus(status_element.innerText, order.id);
                status_element.blur();
            }

            if (e.key === "Escape") {
                e.preventDefault();
                console.log("ESC PRESSED");
                status_element.innerText = "pending";
                status_element.blur();
            }
        });
    })
}

function renderPageBoxes(totalPages, rows){
  const outputarea = document.getElementById('page-btns')
  rowzone.innerText = `Rows per page (10) out of ${rows} rows`
  const btnclasses = `p-3 border-2 border-black pgbtn hover:border-white hover:bg-cerulean hover:text-white`;
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
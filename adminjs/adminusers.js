const API = window.ENV.API_URL;
let orderState = [];
const rolecolors = {
    ADMIN: "bg-[#87f1f5]",
    MODERATOR: "bg-[#51aafc]",
    seller: "bg-[#d9980d]",
    buyer: "bg-[#e8300c]"

};

const rowzone = document.getElementById('row-id-zone');

const queryString = window.location.href;

const urlbar = new URL(queryString);
if (!urlbar.searchParams.has("pg")) {
    urlbar.searchParams.set("pg", "1");
    window.history.replaceState({}, "", urlbar);
}

const pageNum = Number(urlbar.searchParams.get('pg'));

function timeConverter(createdat) {
    const date = new Date(createdat.replace(" ", "T"));

    const formatted = date.toLocaleDateString("en-GB", {
        day: "2-digit",
        month: "long",
        year: "numeric"
    });

    return formatted
}

document.addEventListener('DOMContentLoaded', async () => {
    await loadUsers();
})
async function deleteUser(userid){
    let url = `${API}/api/admin/delete.php`;
    const body ={deleted_id: userid};

    const response = await fetch(url, { credentials: "include", method: "POST", body: JSON.stringify(body) });
    const data = await response.json();
    alert(data.message);
    if (!data.success){
        return;
    }

    console.log(data.message)

    orderState = orderState.map(user => {
        if (user.id !== userid) return user;

        return {
            ...user,
            status: "DELETED"
        };
    })

    renderTableRows();




}
async function changeStatus(newStatus, newRole, userid) {
    let url = `${API}/api/admin/modifyuser.php`;

    const body = {}

    if (newStatus) {
        body["status"] = newStatus;
    }
    if (newRole) {
        body["role"] = newRole;
    }

    body["clientid"] = userid;

    const response = await fetch(url, { credentials: "include", method: "POST", body: JSON.stringify(body) });
    const data = await response.json();

    orderState = orderState.map(user => {
        if (user.id !== userid) return user;

        return {
            ...user,
            status: newStatus ?? user.status,
            role: newRole ?? user.role
        };
    });
    renderTableRows()
    console.log(data)

}
async function loadUsers() {
    let url = `${API}/api/admin/users.php?pg=${pageNum}`;

    const response = await fetch(url);
    const data = await response.json();
    orderState = data.users || [];
    renderTableRows()
    renderPageBoxes(data.totalpages, data.rows);

}

function renderTableRows() {
    const myTable = document.getElementById('user-table');
    console.log(orderState)
    myTable.innerHTML = "";
    if (!orderState.length) {
        return;
    }
    const tableheadings = document.createElement('tr')
    tableheadings.innerHTML = `<th>ID</th>
                        <th>icon</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Registered</th>
                        <th>Actions</th>`
    tableheadings.className = `bg-darkgray text-white`;
    myTable.append(tableheadings);

    orderState.forEach(user => {
        const tablerow = document.createElement('tr')
        tablerow.className = "[&>*]:p-2"
        const status_class = user.status === "ACTIVE" ? "bg-green-400 text-black" : "bg-normalred text-white";

        const role_class = rolecolors[user.role] || "";
        tablerow.innerHTML = `
                        <td>${user.id}</td>
                        <td><img src="${user.icon}?t=${user.updated_at ?? 0}&tr=w-100,c-maintain_ratio,f-auto,q-70" alt="${user.username}" class="rounded-full w-12 p-0"></td>
                        <td>${user.email}</td>
                        <td contenteditable="true" tabindex="0" id="ROLE-${user.id}" class="${role_class}">${user.role}</td>
                        <td contenteditable="true" tabindex="0" id="STATUS-${user.id}" class="${status_class}">${user.status}</td>
                        <td>${timeConverter(user.created_at)}</td>
                        <td>
                            <i id="DELETE-${user.id}" class="fa-solid fa-trash text-normalred"></i>
                        </td>
                    `
        myTable.append(tablerow);
        const status_element = tablerow.querySelector(`#STATUS-${user.id}`);
        const role_element = tablerow.querySelector(`#ROLE-${user.id}`);
        const delete_btn = tablerow.querySelector(`#DELETE-${user.id}`)

        delete_btn.addEventListener('click', async (e)=>{
            if(confirm(`Are you sure you wish to delete ${user.username}. Note if you do, there is no reversing this.`)){
                await deleteUser(user.id)

            }
            else{
                return;

            }

        })

        status_element.addEventListener('keydown', async (e) => {

            if (e.key === "Enter") {
                e.preventDefault();
                if (user.role === "ADMIN") {
                    alert("The Admin Cannot remove themself")
                    return;
                }
                await changeStatus(status_element.innerText, null, user.id);
                status_element.blur();
            }

            if (e.key === "Escape") {
                e.preventDefault();
                console.log("ESC PRESSED");
                status_element.innerText = "ACTIVE";
                status_element.blur();
            }
        });

        role_element.addEventListener('keydown', async (e) => {

            if (e.key === "Enter") {
                e.preventDefault();
                if (user.role === "ADMIN") {
                    alert("The Admin Cannot remove themself")
                    return;
                }
                await changeStatus(null, role_element.innerText, user.id);
                role_element.blur();
            }

            if (e.key === "Escape") {
                e.preventDefault();
                console.log("ESC PRESSED");
                role_element.innerText = "ACTIVE";
                role_element.blur();
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
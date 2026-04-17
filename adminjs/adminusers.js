const API = window.ENV.API_URL;

document.addEventListener('DOMContentLoaded', async () => {
    const users = await GetUsers();
    renderTableRows(users)
})
async function changeStatus(newStatus, newRole, userid){
    let url = `${API}/api/admin/modifyuser.php`;

    const body = {}

    if (newStatus){
        body["status"] = newStatus;
    }
    if (newRole){
        body["role"] = newRole;
    }

    body["clientid"] = userid;
    const response = await fetch(url, {credentials: "include", method: "POST", body: JSON.stringify(body)});
    const data = await response.json();

    console.log(data)

}
async function GetUsers() {
    let url = `${API}/api/admin/users.php`;

    const response = await fetch(url);
    const data = await response.json();

    return data.users;

}

function renderTableRows(users) {
    const myTable = document.getElementById('user-table');
    myTable.innerHTML = "";
    if (!users) {
        return;
    }
    const tableheadings = document.createElement('tr')
    tableheadings.innerHTML = `<th>ID</th>
                        <th>icon</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Registered</th>
                        <th>Last Active</th>
                        <th>Actions</th>`
    tableheadings.className = `bg-darkgray text-white`;
    myTable.append(tableheadings);

    users.forEach(user => {
        const tablerow = document.createElement('tr')
        tablerow.className = "[&>*]:p-2"
        
        tablerow.innerHTML = `
                        <td>${user.id}</td>
                        <td><img src="${user.icon}" alt="${user.username}" class="rounded-full w-12 p-0"></td>
                        <td>${user.email}</td>
                        <td contenteditable="true" tabindex="0" id="ROLE-${user.id}">${user.role}</td>
                        <td contenteditable="true" tabindex="0" id="STATUS-${user.id}" class="bg-green-400">${user.status}</td>
                        <td>${user.created_at}</td>
                        <td>5 mins ago</td>
                        <td>
                            <i class="fa-solid fa-pen-to-square"></i><i class="fa-solid fa-trash text-normalred"></i>
                        </td>
                    `
        myTable.append(tablerow);
        const status_element = document.getElementById(`STATUS-${user.id}`);
        const role_element = document.getElementById(`ROLE-${user.id}`);

        status_element.addEventListener('keydown', async (e) => {

            if (e.key === "Enter") {
                e.preventDefault();
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
                await changeStatus(role_element.innerText, null, user.id);
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
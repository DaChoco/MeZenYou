const API = window.ENV.API_URL;
let usersState = [];
const rolecolors = {
    ADMIN: "bg-[#21d5db]",
    MODERATOR: "bg-[#1f7acf]",
    seller: "bg-[#d9980d]",
    buyer: "bg-[#e8300c]"

};

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

    usersState = usersState.map(user => {
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
    let url = `${API}/api/admin/users.php`;

    const response = await fetch(url);
    const data = await response.json();
    usersState = data.users || [];
    renderTableRows()

}

function renderTableRows() {
    const myTable = document.getElementById('user-table');
    console.log(usersState)
    myTable.innerHTML = "";
    if (!usersState.length) {
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

    usersState.forEach(user => {
        const tablerow = document.createElement('tr')
        tablerow.className = "[&>*]:p-2"
        const status_class = user.status === "ACTIVE" ? "bg-green-400 text-black" : "bg-normalred text-white";

        const role_class = rolecolors[user.role] || "";
        tablerow.innerHTML = `
                        <td>${user.id}</td>
                        <td><img src="${user.icon}?tr=200,c-maintain_ratio" alt="${user.username}" class="rounded-full w-12 p-0"></td>
                        <td>${user.email}</td>
                        <td contenteditable="true" tabindex="0" id="ROLE-${user.id}" class="${role_class}">${user.role}</td>
                        <td contenteditable="true" tabindex="0" id="STATUS-${user.id}" class="${status_class}">${user.status}</td>
                        <td>${timeConverter(user.created_at)}</td>
                        <td>5 mins ago</td>
                        <td>
                            <i class="fa-solid fa-pen-to-square"></i><i class="fa-solid fa-trash text-normalred"></i>
                        </td>
                    `
        myTable.append(tablerow);
        const status_element = tablerow.querySelector(`#STATUS-${user.id}`);
        const role_element = tablerow.querySelector(`#ROLE-${user.id}`);

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
const api = window.ENV.API_URL;
const order_zone = document.getElementById("order-zone");
const messagebox = document.getElementById('messagebox');
const msglink = document.getElementById('msgbtntab');
const streetaddrtxt = document.getElementById('accStreetID');
const phonetxt = document.getElementById('phoneNumID');

const newpw1 = document.getElementById('newpw1')
const newpw2 = document.getElementById('newpw2')

const iconselect = document.getElementById('iconupload')
let pastedImageFile = null;

const statuscolors = {
    COMPLETED: "text-[#02b835]",
    pending: "text-[#d18902]",
    cancelled: "text-[#eb1000]"
};

const convoentries = document.getElementById('convo-entries');
const headericon = document.getElementById('header-icon');
const headerusername = document.getElementById('header-username')
const scrollzone = document.getElementById('scroll-zone');

let USER = {};
let current_messages = [];
let conversations = [];
let current_version = "";
let is_rendered = false
const urlbar = new URL(window.location.href);
let recieverID = urlbar.searchParams.get('rid');

function sKtoTime(sk){
    const timestamp = Number(sk.split('#')[1]); // extract ms timestamp
    const date = new Date(timestamp);

    return date.toLocaleTimeString([], {
        hour: 'numeric',
        minute: '2-digit',
        hour12: true
    });

}

async function sendMessage(msgtxt) {
  if (!msgtxt || !recieverID) {
    return;
  }

  let url = `${api}/api/messages/send.php`;
  if (!USER) {
    alert("You need to be logged in to send messages. Sorry");
    return;
  }
  const body = { icon: USER["icon"], message: msgtxt, rID: recieverID };
  const response = await fetch(url, {
    credentials: "include",
    body: JSON.stringify(body),
    method: "POST",
  });
  const data = await response.json();

  if (data.success) {
    alert("Message successfully sent!");
    current_messages = await getMessages();
    renderMessages(getReceiverAvatar());
  } else {
    alert("Something went wrong");
    return;
  }
}


function capitalizeFirst(str) {
  return str.charAt(0).toUpperCase() + str.slice(1);
}

function dateConversion(sqlDate){

const date = new Date(sqlDate.replace(" ", "T"));

const formatted = date.toLocaleDateString("en-GB", {
  day: "2-digit",
  month: "long",
  year: "numeric"
});



return formatted
}

msglink.addEventListener('click', (e)=>{
  e.stopPropagation();
  messagebox.classList.toggle('hidden');
})

document.addEventListener('click', (e)=>{
  if (!messagebox.contains(e.target)){
    messagebox.classList.add('hidden');
  }
})

iconselect.addEventListener("focus", () => allowPasteUpload = true);
iconselect.addEventListener("blur", () => allowPasteUpload = false);

let allowPasteUpload = false;
document.addEventListener("paste", (e) => {
  if (!allowPasteUpload) return;

  const items = e.clipboardData?.items;
  if (!items) return;

  for (let item of items) {
    if (item.type.startsWith("image/")) {
      const blob = item.getAsFile();

      // convert to File so backend treats it like normal upload
      pastedImageFile = new File([blob], "pasted-image.png", {
        type: blob.type,
      });

      console.log("Pasted image captured:", pastedImageFile);
      alert("Image pasted! Click submit to upload.");

      break;
    }
  }
});
document.getElementById("iconsubmit").addEventListener('submit', async (e) =>{
  e.preventDefault();
  const selectedFile = iconselect.files[0];
  const fileToUpload = selectedFile || pastedImageFile;


  if (!fileToUpload) {
    alert("No image selected or pasted");
    return;
  }

  const formData = new FormData();
  formData.append("image", fileToUpload);
  formData.append("image", selectedFile);
  const response = await fetch(`${api}/api/account/uploadicon.php`, {credentials: "include", method: "POST", body: formData})
  const data = await response.json()

  alert(data.message);
  console.log(data)

})

document.getElementById("pwsubmit").addEventListener('submit', async (e) =>{
  e.preventDefault();
  if (newpw1.value !== newpw2.value){
    alert("Passwords Don't Match");
    return;
  }
  const response = await fetch(`${api}/api/account/updatepasswd.php`, {credentials: "include", method: "POST", body: JSON.stringify({password: newpw1.value})})
  const data = await response.json();

  if (data){
    alert(data.message);
  }
})
document.getElementById("logoutbtn").addEventListener("click", async () => {
  const response = await fetch(`${api}/api/auth/logout.php`, {
    method: "POST",
    credentials: "include",
  });
  const choice = confirm("Would you wish to log out. This will destroy your session")

  if (choice){
    const data = await response.json();

  if (data.success) {
    alert("SUCCESSFULLY LOGGED OUT OF SESSION");
  }
  window.location.href = "/";

  }
  
});

async function loadUser() {
  console.log("User Agent:", navigator.userAgent);
  console.log("Platform:", navigator.platform);
  const res = await fetch(`${api}/api/account/role.php`, {
    credentials: "include",
  });

  const data = await res.json();

  if (data.redirect) {
    console.log("User not logged in");
    window.location.href = data.redirect;
  }
  const role = data.role;
  console.log(data);

  if (role === "seller" || role === "ADMIN" || role === "MODERATOR") {
    document.getElementById("seller-nav").classList.remove("hidden");
    document.getElementById("seller-tools").classList.remove("hidden");
  }
  streetaddrtxt.innerText = data.address.replaceAll("-", ", ");
  phonetxt.innerText = `Phone Number: ${data.phone}`;
  current_version = data.timestamp;
  return data
}

async function getMessages() {
  let url = `${api}/api/messages/currentmsgs.php?rid=${recieverID}`;
  if (recieverID === USER["user"]) {
    return [];
  }
  const response = await fetch(url, { credentials: "include" });

  const data = await response.json();

  if (data.status) {
    return data.messages;
  } else {
    alert("You may not be signed in. Or something has gone wrong with the server.");
    return [];
  }
}

async function loadMessageBox() {
    const sendbtn = document.getElementById("senditbtn");
    const inputbar = document.getElementById("sendmsgtxt");

    sendbtn.addEventListener("click", () => sendMessage(inputbar.value));
    
    inputbar.addEventListener("keydown", (e) => {
      if (e.key === "Enter") sendMessage(inputbar.value);
      if (e.key === "Escape") {
        inputbar.blur();
        inputbar.value = "";
      }
    });
}

async function getConversations() {

    let url = `${api}/api/messages/conversations.php`;
    const response = await fetch(url, { credentials: "include" });

    const data = await response.json();
    if (data.status) {
        current_version = data.current;
        return data.conversations;
    }
    else {
        alert("INTERNAL SERVER ERROR");
        return []
    }

}


const renderConversations = () => {

    const search_user_bar = document.createElement('input');
    search_user_bar.setAttribute('type', 'text')
    search_user_bar.classList = 'w-full p-2';


    search_user_bar.addEventListener('keydown', async (e)=>{

        if (e.key === "Enter"){
            //temporarily makes a conversation that can be anchored off of
            let url = `${api}/api/admin/search.php`;
            const response = await fetch(url, {credentials: "include", method: "POST", body: JSON.stringify({txt:search_user_bar.value})})
            const data = await response.json();
            console.log(data)
            recieverID = data.user.rID;

            urlbar.searchParams.set('rid', recieverID);
            window.history.pushState({}, "", urlbar);

            headericon.setAttribute('src', `${data.user.icon}?t=${current_version}`);
            headericon.setAttribute('alt', data.user.username)
            headerusername.innerText = data.user.username;

            current_messages = await getMessages();

            renderMessages(getReceiverAvatar());

        }
        else if (e.key === "Escape"){
            e.preventDefault();
            search_user_bar.value = ""
            search_user_bar.blur();
        }

    })
    convoentries.innerHTML = '';
    convoentries.append(search_user_bar);
    let borderstyle = ""
    
    console.log(conversations)

    conversations.map(convo => {
        const isActive = convo.otherID == recieverID;
        const entry = document.createElement('a')
        entry.href = `?rid=${convo.otherID}`

       
        entry.className = 'block'
        entry.innerHTML = `
                        <div class="message-options flex items-center gap-3 px-4 py-3 ${isActive ? 'border-l-2 border-gray-800' : ''}  bg-white hover:bg-white transition-colors">
                            <img src="${convo.avatar}?t=${current_version}"
                                class="rounded-full w-9 h-9 object-cover flex-shrink-0" alt="Welt Yang">
                            <div class="min-w-0 flex-1">
                                <span class="font-semibold text-sm block truncate">${convo.username ?? "UNKNOWN"}</span>
                                <p class="text-xs text-gray-500 truncate">${convo.lastMessage}</p>
                            </div>
                         
                        </div>`;

        entry.addEventListener('click', async (e) => {
            e.preventDefault();

            recieverID = convo.otherID;

            urlbar.searchParams.set('rid', recieverID);
            window.history.pushState({}, "", urlbar);

            headericon.setAttribute('src', `${convo.avatar}?t=${current_version}`);
            headericon.setAttribute('alt', convo.username)
            headerusername.innerText = convo.username;

            current_messages = await getMessages();

            renderMessages(getReceiverAvatar());
            renderConversations();
        });
        convoentries.append(entry);

    })


}

function getReceiverAvatar() {
    const activeConvo = conversations.find(c => c.otherID == recieverID);
    return activeConvo?.avatar ?? "";
}
const renderMessages = (recieverAvatar) => {
    scrollzone.innerHTML = "";
    current_messages.map(msg=>{

       if (String(USER["user"]) !== msg['sID']) {

      const reciever = document.createElement('article');
      reciever.classList = 'flex items-end gap-2'
      reciever.innerHTML = `
                        <img src="${recieverAvatar}?t=${current_version}"
                            class="rounded-full w-8 h-8 object-cover flex-shrink-0" alt="${msg.username}">
                        <div class="max-w-[65%] bg-white border border-gray-200 rounded-tl rounded-tr-xl rounded-br-xl px-4 py-2.5 text-sm leading-relaxed">
                            ${msg.messageText}
                        </div>
                        <span class="text-xs text-gray-400 pb-1 flex-shrink-0">${sKtoTime(msg.SK)}</span>
                        `

      scrollzone.append(reciever);

    }
    else {
      const sender = document.createElement('article');
      sender.classList = 'flex items-end flex-row-reverse gap-2'
      sender.innerHTML = `
                        <div class="max-w-[65%] bg-darkgray text-white rounded-tl-xl rounded-tr rounded-bl-xl px-4 py-2.5 text-sm leading-relaxed">
                            ${msg.messageText}
                        </div>
                        <span class="text-xs text-gray-400 pb-1 flex-shrink-0">${sKtoTime(msg.SK)}</span>
            `;
      scrollzone.append(sender);

    }
        
        
    });

    scrollzone.scrollTop = scrollzone.scrollHeight


}

document.addEventListener("DOMContentLoaded", async () => {
  conversations = await getConversations();

  let ridFromURL = urlbar.searchParams.get('rid');

    const exists = conversations.find(c => c.otherID === ridFromURL);

    if (exists) {
        recieverID = ridFromURL;
    } else if (conversations.length > 0) {
        recieverID = conversations[0].otherID;
        urlbar.searchParams.set('rid', recieverID);
        window.history.replaceState({}, "", urlbar);

    }

    const activeConvo = conversations.find(c => c.otherID === recieverID);
    if (activeConvo) {
        headericon.setAttribute('src', `${activeConvo.avatar}?t=${current_version}`);
        headericon.setAttribute('alt', activeConvo.username)
        headerusername.innerText = activeConvo.username;
    }

  [USER, current_messages] = await Promise.all([
      loadUser(),
      getMessages(),  
    ]);
  loadMessageBox();

  renderConversations();
  renderMessages(getReceiverAvatar());
  let is_delivered = ``;

  const response = await fetch(`${api}/api/account/retrieveorders.php`, {
    credentials: "include",
  });

  const data = await response.json();
  console.log(data.orders);

  if (data.orders) {
    const orders = data.orders;

    orders.forEach((order) => {
      const card = document.createElement("article");

      const order_status_color = statuscolors[order.order_status] || ""
      card.className =
        "order-box grid grid-cols-[25%_75%] gap-x-3 grid-rows-1 bg-white p-3";
      card.innerHTML = `
    <img class="object-contain" src="${order["image"]}" alt="">
        <div class="flex flex-col">
          <h4 class="sm:text-base lg:text-xl font-bold">Order #${order["id"]}</h4>
          <span class="flex flex-row">
             <p class="${order_status_color} mr-2">${capitalizeFirst(order["order_status"])}</p>
          </span>
          <ul>
              <li class="sm:text-base lg:text-lg">${order["name"]}</li>
          </ul>

            <p class="sm:text-base lg:text-lg">ORDER PLACED: ${dateConversion(order["created_at"])}</p>
            <p class="sm:text-base lg:text-lg">Quantity: ${order["quantity"]}x</p>
            <p class="sm:text-base lg:text-lg">Price Per Item: R${order["price"].toFixed(2)}</p>
            <p class="sm:text-base lg:text-lg font-bold">Order #${order["id"]} Total: R${order["total_price"].toFixed(2)}</p>
        </div>`;
      order_zone.appendChild(card);
    });
  }
});

document
  .getElementById("address-submit")
  .addEventListener("click", async (e) => {
    e.preventDefault();
    const phonenumber = document.getElementById("phoneselect").value;
    const streetaddress = document.getElementById("streetselect").value;
    const suburbaddress = document.getElementById("suburbselect").value;
    const cityaddress = document.getElementById("cityselect").value;
    const province = document.getElementById("provinceselect").value;
    const postalcode = document.getElementById("postcodeid").value;
    const deliveryinstructions = document.getElementById("del-instruct").value;

    const fields = {
      street: streetaddress,
      phone: phonenumber,
      suburb: suburbaddress,
      city: cityaddress,
      province: province,
      postalcode: postalcode,
      delinstructions: deliveryinstructions,
    };
    const response = await fetch(`${api}/api/account/updateaddress.php`, {
      method: "POST",
      credentials: 'include',
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(fields),
    });

    const data = await response.json();

    //NOTE FOR LATER. INCORPORATE better MSG RESPONSE later
    alert(data.message);
  });

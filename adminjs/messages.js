let USER = {};
let current_messages = []
let conversations = []
let current_version = "";
const API_URL = window.ENV.API_URL;
const urlbar = new URL(window.location.href);
let recieverID = urlbar.searchParams.get('rid');
const sendbtn = document.getElementById('senditbtn');
const inputbar = document.getElementById('sendmsgtxt');

const convoentries = document.getElementById('convo-entries');
const backconvos = document.getElementById('back-to-convos')
const headericon = document.getElementById('header-icon');
const headerusername = document.getElementById('header-username')
const scrollzone = document.getElementById('scroll-zone');
const clean = (val) => DOMPurify.sanitize(val)

function sKtoTime(sk){
    const timestamp = Number(sk.split('#')[1]); // extract ms timestamp
    const date = new Date(timestamp);

    return date.toLocaleTimeString([], {
        hour: 'numeric',
        minute: '2-digit',
        hour12: true
    });

}

async function retrieveUserData() {
    let url = `${API_URL}/api/account/role.php`

    const response = await fetch(url, { credentials: "include" });
    const data = await response.json();
    return data

}

async function sendMessage() {
    if (!inputbar.value || !recieverID) {
        return;
    }


    let url = `${API_URL}/api/messages/send.php`;
    const body = { icon: USER["icon"], message: inputbar.value, rID: recieverID }
    const response = await fetch(url, { credentials: "include", body: JSON.stringify(body), method: "POST" })
    const data = await response.json();

    if (data.success) {
        alert("Message successfully sent!");
        current_messages = await getMessages();
        renderConversations()

    }
    else {
        alert("Something went wrong")
        return;
    }

}

async function getMessages() {
    let url = `${API_URL}/api/messages/currentmsgs.php?rid=${recieverID}`;

    const response = await fetch(url, { credentials: "include" });

    const data = await response.json();

    if (data.status) {
        return data.messages
    }
    else {
        alert("INTERNAL SERVER ERROR");
        return []
    }


}

async function getConversations() {

    let url = `${API_URL}/api/messages/conversations.php`;
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
            let url = `${API_URL}/api/admin/search.php`;
            const response = await fetch(url, {credentials: "include", method: "POST", body: JSON.stringify({txt:search_user_bar.value})})
            const data = await response.json();
      
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
    


    conversations.map(convo => {
        const isActive = convo.otherID == recieverID;
        const entry = document.createElement('a')
        entry.href = `?rid=${convo.otherID}`

       
        entry.className = 'block'
        entry.innerHTML = `
                        <div id="USER-CHAT-${convo.otherID}" class="message-options flex items-center gap-3 px-4 py-3 ${isActive ? 'border-l-2 border-gray-800' : ''}  bg-white hover:bg-white transition-colors">
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

    const messageoptions = document.querySelectorAll('.message-options');

    document.addEventListener('click', (e)=>{

        const clickedInside = [...messageoptions].some(el => el.contains(e.target));

        if (!clickedInside && !backconvos.contains(e.target)) {
        document.getElementById('sidemsg-zone').classList.add('-translate-x-full');
        }
        })


}

document.getElementById('convo-entries').addEventListener('click', (e) => {
    const entry = e.target.closest('[data-convo]');
    if (entry && window.innerWidth < 1024) {
        document.getElementById('sidemsg-zone').classList.add('-translate-x-full');
    }
});


backconvos.addEventListener('click', () => {
    document.getElementById('sidemsg-zone').classList.remove('-translate-x-full');
});

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
                            class="rounded-full w-8 h-8 object-cover flex-shrink-0" alt="${clean(msg.username)}">
                        <div class="max-w-[65%] bg-white border border-gray-200 rounded-tl rounded-tr-xl rounded-br-xl px-4 py-2.5 text-sm leading-relaxed">
                            ${clean(msg.messageText)}
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
                            ${clean(msg.messageText)}
                        </div>
                        <span class="text-xs text-gray-400 pb-1 flex-shrink-0">${sKtoTime(msg.SK)}</span>
            `;
      scrollzone.append(sender);

    }
        
        
    });

    scrollzone.scrollTop = scrollzone.scrollHeight


}



document.addEventListener('DOMContentLoaded', async (e) => {
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

    [USER, current_messages] = await Promise.all([retrieveUserData(), getMessages()]);

    renderConversations();
    renderMessages(getReceiverAvatar());

    sendbtn.addEventListener('click', async () => sendMessage());
    inputbar.addEventListener('keydown', async (e)=>{
        if (e.key === "Enter"){
            sendMessage();
        }
        else if (e.key === "Escape"){
            inputbar.blur();
            inputbar.value = "";
        }
    })

});

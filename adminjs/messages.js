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
const headericon = document.getElementById('header-icon');
const headerusername = document.getElementById('header-username')

async function retrieveUserData(){
    let url = `${API_URL}/api/account/role.php`

    const response = await fetch(url, {credentials: "include"});
    const data = await response.json();
    return data

}

async function sendMessage(){
    if (!inputbar.value || !recieverID){
        return;
    }


    let url = `${API_URL}/api/messages/send.php`;
    const body = {icon: USER["icon"], message: inputbar.value, rID: recieverID}
    const response = await fetch(url, {credentials: "include", body: JSON.stringify(body), method: "POST"})
    const data = await response.json();

    if (data.success){
        alert("Message successfully sent!");
        current_messages = await getMessages();
        renderConversations()

    }
    else{
        alert("Something went wrong")
        return;
    }

}

async function getMessages(){
    let url = `${API_URL}/api/messages/currentmsgs.php?rid=${recieverID}`;

    const response = await fetch(url, {credentials: "include"});

    const data = await response.json();

    if (data.status){
        console.log(data)
        return data.messages
    }
    else{
        alert("INTERNAL SERVER ERROR");
        return []
    }
    

}

async function getConversations(){

    let url = `${API_URL}/api/messages/conversations.php`;
    const response = await fetch(url, {credentials: "include"});

    const data = await response.json();
    if (data.status){
        current_version = data.current;
        return data.conversations;
    }
    else{
        alert("INTERNAL SERVER ERROR");
        return []
    }

}

const renderConversations = () =>{
    convoentries.innerHTML = '';

    conversations.map(convo =>{

        const entry = document.createElement('a')
        entry.setAttribute('href', `/admin/messages.html?rid=${convo.otherID}`);

        entry.classList = 'block'
        entry.innerHTML = `
                        <div class="message-options flex items-center gap-3 px-4 py-3 border-l-2 border-darkgray bg-white hover:bg-white transition-colors">
                            <img src="${convo.avatar}?t=${current_version}"
                                class="rounded-full w-9 h-9 object-cover flex-shrink-0" alt="Welt Yang">
                            <div class="min-w-0 flex-1">
                                <span class="font-semibold text-sm block truncate">${convo.username ?? "UNKNOWN"}</span>
                                <p class="text-xs text-gray-500 truncate">${convo.lastMessage}</p>
                            </div>
                            <!-- Unread badge -->
                            <span class="bg-darkgray text-white text-xs font-medium px-2 py-0.5 rounded-full flex-shrink-0">3</span>
                        </div>`;
        convoentries.append(entry);
        
    })

    
}

const renderMessages = () =>{


}

document.addEventListener('DOMContentLoaded', async (e)=>{
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
        headerusername.innerText= activeConvo.username;
    }

    [USER, current_messages] = await Promise.all([retrieveUserData(), getMessages()]);

    console.log(current_messages)

    

    renderConversations();

    sendbtn.addEventListener('click', async ()=> sendMessage());

});

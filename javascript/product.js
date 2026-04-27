const queryString = window.location.search;
const API_URL = window.ENV.API_URL;
const urlParams = new URLSearchParams(queryString);
const section_container = document.getElementById("item-display");
const addreview = document.getElementById("addreviewid");
const txtarea = document.getElementById("txtareaid");
const reviewarea = document.getElementById("ratingid");
let is_rendered = false;
let USER = {};
let current_messages = [];
let conversations = [];
let recieverID = 0;
let current_version = "";

const clean = (val) => DOMPurify.sanitize(val);

document.addEventListener("DOMContentLoaded", async () => {
  addreview.addEventListener("click", () => uploadReview(urlParams.get("id")));

  const item = await loadProduct();
  const cartaddbtn = document.getElementById("cartaddbtn");
  cartaddbtn.addEventListener("click", () =>
    addtocart(urlParams.get("id"), item),
  );
  const messagebtn = document.getElementById("msgbtn");

  messagebtn.addEventListener("click", async (e) => {
    e.preventDefault();
    await loadMessageBox();
    [USER, current_messages] = await Promise.all([
      retrieveUserData(),
      getMessages(),
    ]);

    renderMessages();
  });
});

function sKtoTime(sk) {
  const timestamp = Number(sk.split("#")[1]); // extract ms timestamp
  const date = new Date(timestamp);

  return date.toLocaleTimeString([], {
    hour: "numeric",
    minute: "2-digit",
    hour12: true,
  });
}

async function sendMessage(msgtxt) {
  if (!msgtxt || !recieverID) {
    return;
  }

  let url = `${API_URL}/api/messages/send.php`;
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
    renderMessages();
  } else {
    alert("Something went wrong");
    return;
  }
}

function renderMessages() {
  const scrollzone = document.getElementById("scroll-zone");
  const headericon = document.getElementById("header-icon");
  const headerusername = document.getElementById("header-username");
  
  if (!current_messages || current_messages.length === 0) {
    scrollzone.innerHTML = "<p class='text-gray-400'>No messages yet</p>";
    return;
  }


  const recieverAvatar = current_messages.find((c) => c.rID !== USER["user"]);
  if (recieverAvatar) {
    headericon.setAttribute("src", `${recieverAvatar.avatar}?t=${current_version}`,
    );
    headerusername.innerText = recieverAvatar.username;
  }

  scrollzone.innerHTML = "";
  console.log("user:", USER);

  current_messages.map((msg) => {
    if (String(USER["user"]) !== msg["sID"]) {
      const reciever = document.createElement("article");
      reciever.classList = "flex items-end gap-2";
      reciever.innerHTML = `
                        <img src="${msg.avatar}?t=${current_version}"
                            class="rounded-full w-8 h-8 object-cover flex-shrink-0" alt="${msg.username}">
                        <div class="max-w-[65%] bg-white border border-gray-200 rounded-tl rounded-tr-xl rounded-br-xl px-4 py-2.5 text-sm leading-relaxed">
                            ${msg.messageText}
                        </div>
                        <span class="text-xs text-gray-400 pb-1 flex-shrink-0">${sKtoTime(msg.SK)}</span>
                        `;

      scrollzone.append(reciever);
    } else {
      const sender = document.createElement("article");
      sender.classList = "flex items-end flex-row-reverse gap-2";
      sender.innerHTML = `
                        <div class="max-w-[65%] bg-darkgray text-white rounded-tl-xl rounded-tr rounded-bl-xl px-4 py-2.5 text-sm leading-relaxed">
                            ${msg.messageText}
                        </div>
                        <span class="text-xs text-gray-400 pb-1 flex-shrink-0">${sKtoTime(msg.SK)}</span>
            `;
      scrollzone.append(sender);
    }
  });

  scrollzone.scrollTop = scrollzone.scrollHeight;
}

async function retrieveUserData() {
  let url = `${API_URL}/api/account/role.php`;

  const response = await fetch(url, { credentials: "include" });
  const data = await response.json();
  console.log(data);
  current_version = data.timestamp;
  return data;
}

async function getMessages() {
  let url = `${API_URL}/api/messages/currentmsgs.php?rid=${recieverID}`;
  if (recieverID === USER["user"]) {
    return [];
  }
  const response = await fetch(url, { credentials: "include" });

  const data = await response.json();

  if (data.status) {
    return data.messages;
  } else {
    alert("INTERNAL SERVER ERROR");
    return [];
  }
}

async function loadMessageBox() {
  const container = document.getElementById("messagebox");

  if (is_rendered == false) {
    const response = await fetch("/components/msgbubble.html");
    const html = await response.text();

    container.innerHTML = html;
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
  } else {
    container.innerHTML = "";
  }

  is_rendered = !is_rendered;
}

async function loadProduct() {
  const ID = urlParams.get("id");
  let url = `${API_URL}/api/item.php?id=${ID}`;

  const response = await fetch(url, { credentials: "include" });
  const data = await response.json();
  console.log(data);

  renderProduct(data.product, data.user);
  const reviewscoreval = document.getElementById("reviewscore");
  const score = await loadReviews(ID);

  reviewscoreval.innerText = score;

  return data;
}

async function uploadReview(ID) {
  if (!ID) {
    return;
  }
  let url = `${API_URL}/api/browse/uploadreview.php?pid=${ID}`;

  const comment = clean(txtarea.value);
  const rating = Number(clean(reviewarea.value));

  if (!rating || rating === 0) {
    alert("Please input a valid value between 1 and 5. Thank you.");
    return;
  }

  const response = await fetch(url, {
    credentials: "include",
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ rating: rating, comment: comment }),
  });
  const data = await response.json();

  if (data) {
    alert(data.message);
  }
}

async function loadReviews(ID) {
  if (!ID) {
    return;
  }
  const response = await fetch(
    `${API_URL}/api/browse/retrievereviews.php?pid=${ID}`,
  );
  const data = await response.json();

  const reviewzone = document.getElementById("commenthere");

  console.log(data);

  data.items.forEach((item) => {
    const review = document.createElement("article");
    review.className = "border-2 border-current p-5";
    const unixTimestamp = item.timestamp;
    const date = new Date(unixTimestamp * 1000);

    review.innerHTML = `<span class="flex flex-row justify-between">
                <p>${item.username}</p><p>Rating: ${item.rating}</p><p>${date.toUTCString()}</p>
            </span>
            <p>${clean(item.comment)}</p>`;

    reviewzone.appendChild(review);
  });

  return data.avg;
}

async function renderProduct(product, user) {
  recieverID = product["userID"];
  section_container.innerHTML = `
    <div class="image-section h-fit md:h-auto">
            <img src="${product["image"]}" alt="" class=" w-full md:w-1/2 mx-auto my-0">
        </div>

        <div class="add-to-cart-or-buy row-span-2 bg-white border-2 border-red-500">

            <div class="top-fourth border-b-2 border-gray-400">
                <h1 class="text-2xl md:text-4xl">${product["product_name"]}</h1>
                <p>By ${product["author"]}</p>
                <p class="text-xl">February 2026</p>

            </div>

            <div class="middle-fourth border-b-2 border-gray-400 py-2">
                <span class="text-2xl md:text-4xl font-bold">R${product["price"]}</span>
                <p>All prices include VAT</p>
                <p>Get it Tommorrow for an extra R30.00</p>
                <p>Deliver to ${user} - Sea Point, Cape Town</p>
                <span class="text-lg text-green-700">IN STOCK</span>
            </div>

             <div class="lower-fourth flex flex-col space-y-5 rounded-sm [&_*]:w-full">
                <a id="msgbtn" href="?id=${product["id"]}&rid=${product["userID"]}"><button type="button" class="p-3 shadow-md bg-slate-700 text-white rounded-sm hover:bg-hoverbtnred duration-150">MSG Seller</button></a>
                <input id="qtyid" min="1" type="number" placeholder="Quantity" class="p-3 outline-none border border-black">
                <button id="cartaddbtn" type="button" class="bg-slate-800 text-white rounded-sm p-3 shadow-md">Add to Cart</button>
                <a href="/pages/checkout.html"><button type="button" class="bg-red-700 hover:bg-hoverbtnred text-white rounded-sm p-3 shadow-md">Proceed to Checkout</button></a>
            </div>

            <div class="[&>*]:text-lg space-y-5">
                <p id="reviewscore" class="text-yellow-600"></p>
                <p>Seller: ${product["username"]}</p>
                <p>Ships From: ${product["location"]}</p>
                <p>Payment: Secure transaction</p>
            </div>

        </div>

        <div class="product-description bg-white w-full p-5 h-full self-end border-2 border-red-500">
            <span class="font-bold text-gray-700 text-3xl">Description</span>
            
            <p>${product["descriptiontxt"]}</p>
        </div>

    `;
}

//--ADD TO CART

async function addtocart(ID, item) {
  if (!item) {
    alert("EMPTY NO ITEM WAS LOADED");
  }

  const qty = document.getElementById("qtyid");
  if (!qty.value || qty.value === 0) {
    alert("INPUT A VALID QUANTITY");
    return;
  }

  const payload = { qty: qty.value, pid: ID };
  const response = await fetch(`${API_URL}/api/cart/add.php`, {
    credentials: "include",
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(payload),
  });
  const data = await response.json();

  if (data.success) {
    alert(data.message);
  } else {
    console.log(data);
  }
}

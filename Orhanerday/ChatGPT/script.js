const api_path = 'Orhanerday/ChatGPT/';

const urlParams = new URLSearchParams(window.location.search);
let CHAT_ID;

if (urlParams.get('chat_id') === null) {
  window.location.href = 'ai.php?chat_id=' + uuidv4();
} else {
  CHAT_ID = urlParams.get('chat_id');
}

document.getElementById("chat_id").value = CHAT_ID;

const idSession = get(".id_session");
// const CHAT_ID = document.getElementById("chat_id").value;
idSession.textContent = CHAT_ID;
getHistory()

model_id = "3";
const idModel = get(".model_id");
idModel.textContent = model_id;

const msgerForm = get(".msger-inputarea");
const msgerInput = get(".msger-input");
const msgerChat = get(".msger-chat");
const msgerSendBtn = get(".msger-send-btn");

const BOT_IMG = api_path + "chatgpt.svg";
const PERSON_IMG = api_path + "chatgpt.svg";
const BOT_NAME = "ChatGPT";
const PERSON_NAME = "You";
//
// // Observe changes to the chat window element
// const observer = new MutationObserver(function(mutations) {
//   // Scroll to the bottom of the chat window
//   msgerChat.scrollTop = msgerChat.scrollHeight;
// });
//
// // Set up the mutation observer to watch for changes to the chat window
// observer.observe(msgerChat, { childList: true });

// Function to delete chat history records for a user ID using the API
function deleteChatHistory() {
    if (!confirm("Are you sure? Your chat will delete for good.")) {
        return false
    }

    fetch(api_path + 'api.php?chat_id=' + CHAT_ID, {
        method: 'DELETE',
        headers: {'Content-Type': 'application/json'}
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Error deleting chat history: ' + response.statusText);
            }
            // deleteAllCookies()
            // location.reload(); // Reload the page to update the chat history table
            window.location.href = 'ai.php?chat_id=' + uuidv4();
        })
        .catch(error => console.error(error));
}

// Event listener for the quit chat button click
const quitButton = document.querySelector('#quit-button');
quitButton.addEventListener('click', event => {
    event.preventDefault();
    window.location.href = 'index.php';
});

// Event listener for the summarise chat button click
const summaryButton = document.querySelector('#summary-button');
summaryButton.addEventListener('click', event => {
    event.preventDefault();

    summariseChat(CHAT_ID)
        .then(text => {
          idSession.textContent = text;
        })
        .catch(error => {
        console.error(error);
        });

    // window.location.href = 'index.php';
});

// Event listener for the chat history button click
const historyButton = document.querySelector('#history-button');
historyButton.addEventListener('click', event => {
    event.preventDefault();

    const popup = document.getElementById("popup-menu");
    popup.style.display = "block"; // Show the popup
    // popup.style.right = event.clientX + "px"; // Position the popup next to the button
    // popup.style.top = event.clientY + "px"; // Position the popup next to the button

});

// Event listener for the new chat button click
const chatButton = document.querySelector('#chat-button');
chatButton.addEventListener('click', event => {
    event.preventDefault();
    uuid = uuidv4();
    // document.cookie = "chat_id=" + uuid;
    // document.getElementById("chat_id").value = uuid;
    // location.reload();
    window.location.href = 'ai.php?chat_id=' + uuid;
});

// Event listener for the Delete button click
const deleteButton = document.querySelector('#delete-button');
deleteButton.addEventListener('click', event => {
    event.preventDefault();
    deleteChatHistory(CHAT_ID);
});

// Event listener for the model change button
const modelButton = document.querySelector('#model-button');
modelButton.addEventListener('click', event => {
    event.preventDefault();
    if (model_id == '3') {
      model_id = '4';
    } else {
      model_id = '3';
    }
    idModel.textContent = model_id;
});

function waiter(editableObj) {

	$(editableObj).keydown(function (e) {
			// User can submit by pressing enter (shift enter is carriage return)
			if (e.which == 13 && !e.shiftKey) {
					$(editableObj).trigger('blur');
			}
	});

}

// this one checks all textareas on page
$('textarea').keypress(function(e) {
  // Check if the Enter key is pressed
  if (e.keyCode == 13 && !e.shiftKey) {
    e.preventDefault();
    // Trigger the form submission
    const msgText = msgerInput.value;
    if (!msgText) return;

    appendMessage(PERSON_NAME, PERSON_IMG, "right", msgText, "");
    msgerInput.value = "";

    sendMsg(msgText)
    // $(this).closest('form').submit();
  }
});

msgerForm.addEventListener("submit", event => {
    event.preventDefault();

    const msgText = msgerInput.value;
    if (!msgText) return;

    appendMessage(PERSON_NAME, PERSON_IMG, "right", msgText, "");
    msgerInput.value = "";

    sendMsg(msgText)
});

function summariseChat(chat_id) {
  var formData = new FormData();
  formData.append('chat_id', chat_id);
  return fetch(api_path + 'summarise.php', {method: 'POST', body: formData})
    .then(response => response.text())
    .then(text => { return text; })
    .catch(error => {
      console.error(error);
      return null;
    });

}

function getComment(chat_id) {
  var formData = new FormData();
  formData.append('chat_id', chat_id);
  formData.append('last', 'TRUE');
  return fetch(api_path + 'api.php', {method: 'POST', body: formData})
    .then(response => response.text())
    .then(text => { return text; })
    .catch(error => {
      console.error(error);
      return null;
    });
}

function getHistory() {
    var formData = new FormData();
    formData.append('chat_id', CHAT_ID);
    fetch(api_path + 'api.php', {method: 'POST', body: formData})
        .then(response => response.json())
        .then(chatHistory => {
            for (const row of chatHistory) {
                appendMessage(PERSON_NAME, PERSON_IMG, "right", row.comment_human, row.comment_date);
                appendMessage(BOT_NAME, BOT_IMG, "left", row.comment_ai, "", row.comment_date);
                if (row.chat_summary !== null) {
                  idSession.textContent = row.chat_summary;
                }
            }
        })
        .catch(error => console.error(error));
}

function appendMessage(name, img, side, text, chat_id, comment_date) {

    // text = text.replace(/\n/g, "<br>");
    // Apply custom CSS class to code blocks
    var converter = new showdown.Converter();
    // converter.setOption('parseImgDimensions', true);
    // converter.setOption('ghCodeBlocks', true);
    // converter.setOption('prefixHeaderId', 'showdownjs-');
    // converter.setOption('tables', true);
    // converter.setOption('tasklists', true);
    // converter.setFlavor('github');
    // alert(text);
    text = converter.makeHtml(text);
    // text = text.replace(/<code>/g, '<code class="language-R">');
    // text = text.replace(/<code>/g, '<code class="html" data-clipboard-target="#mycodeblock">');
    // text = text.replace(/<\/code><\/pre>/g, '</code></pre><button class="btn" data-clipboard-target="#mycodeblock">Copy to clipboard</button>');
    // new ClipboardJS('.btn'); // code block clipboard

    // alert(text);

    name = "";
    img = "";
    comment_date = formatDate(new Date());
    comment_date = "";
    // if (date == "") date = new Date();
    //   Simple solution for small apps
    const msgHTML = `
    <div class="msg ${side}-msg">
      <div class="msg-img" style="background-image: url(${img})"></div>
      <div class="msg-bubble">
        <div class="msg-info">
          <div class="msg-info-name">${name}</div>
          <div class="msg-info-time">${comment_date}</div>
        </div>

        <div class="msg-text" id=${chat_id}>${text}</div>
      </div>
    </div>
  `;

    msgerChat.insertAdjacentHTML("beforeend", msgHTML);
    hljs.highlightAll(); // add code colour formatting, language defined above
    // msgerChat.scrollTop += 500;
    // msgerChat.scrollTop = msgerChat.scrollHeight;

    // document.addEventListener('DOMContentLoaded', (event) => {
    //   document.querySelectorAll('pre code').forEach((block) => {
    //     hljs.highlightBlock(block);
    //     hljs.addPlugin(new ClipboardJS(block, {
    //       container: block.parentNode
    //     }));
    //   });
    // });


}

function sendMsg(msg) {
    msgerSendBtn.disabled = true
    var formData = new FormData();
    formData.append('msg', msg);
    formData.append('chat_id', CHAT_ID);
    fetch(api_path + 'send-message.php', {method: 'POST', body: formData})
        .then(response => response.json())
        .then(data => {
            let uuid = uuidv4()
            const eventSource = new EventSource(api_path + `event-stream.php?comment_id=${data.comment_id}&chat_id=${encodeURIComponent(CHAT_ID)}&model_id=${model_id}`);
            appendMessage(BOT_NAME, BOT_IMG, "left", "", uuid, "");
            const div = document.getElementById(uuid);

            eventSource.onmessage = function (e) {
                if (e.data == "[DONE]") {
                    msgerSendBtn.disabled = false
                    eventSource.close();
                } else {
                    let txt = JSON.parse(e.data).choices[0].delta.content
                    if (txt !== undefined) {
                        div.innerHTML += txt.replace(/(?:\r\n|\r|\n)/g, '<br>');
                    }
                }
            };

            eventSource.onerror = function (e) {
                console.log(e);
                getComment(CHAT_ID)
                    .then(text => {
                    div.innerHTML += text;
                    })
                    .catch(error => {
                    console.error(error);
                    });
                msgerSendBtn.disabled = false
                eventSource.close();
            };

        })
        .catch(error => console.error(error));


}

// Utils
function get(selector, root = document) {
    return root.querySelector(selector);
}

function formatDate(date) {
    const h = "0" + date.getHours();
    const m = "0" + date.getMinutes();

    return `${h.slice(-2)}:${m.slice(-2)}`;
}

function random(min, max) {
    return Math.floor(Math.random() * (max - min) + min);
}
/*
function getCookie(cname) {
    let name = cname + "=";
    let decodedCookie = decodeURIComponent(document.cookie);
    let ca = decodedCookie.split(';');
    for (let i = 0; i < ca.length; i++) {
        let c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
}
*/
function uuidv4() {
    return ([1e7] + -1e3 + -4e3 + -8e3 + -1e11).replace(/[018]/g, c =>
        (c ^ crypto.getRandomValues(new Uint8Array(1))[0] & 15 >> c / 4).toString(16)
    );
}
/*
function deleteAllCookies() {
    const cookies = document.cookie.split(";");

    for (let i = 0; i < cookies.length; i++) {
        const cookie = cookies[i];
        const eqPos = cookie.indexOf("=");
        const name = eqPos > -1 ? cookie.substr(0, eqPos) : cookie;
        document.cookie = name + "=;expires=Thu, 01 Jan 1970 00:00:00 GMT";
    }
}
*/

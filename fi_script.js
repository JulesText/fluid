let CHAT_ID;

const urlParams = new URLSearchParams(window.location.search);
if (urlParams.get('chat_id') === null) {
  window.location.href = api_file + '?chat_id=' + uuidv4();
} else {
  CHAT_ID = urlParams.get('chat_id');
}

document.getElementById("chat_id").value = CHAT_ID;

const chatSummary = get(".chat_summary");
chatSummary.textContent = CHAT_ID;

getChat()

var areaScroll = document.getElementById("area-scroll");

model_id = "3";
const idModel = get(".model_id");
idModel.textContent = model_id;

word_count = 0;
const nWords = get(".word_count");
nWords.textContent = word_count;

const msgerForm = get(".msger-inputarea");
const msgerInput = get(".msger-input");
const msgerChat = get(".msger-chat");
const msgerSendBtn = get(".msger-send-btn");

// ---- get chat from db ---- //

function getChat() {
    var formData = new FormData();
    formData.append('chat_id', CHAT_ID);
    formData.append('query', 'get_chat');
    fetch('fi_require.php', {method: 'POST', body: formData})
        .then(response => response.json())
        .then(chatHistory => {
            for (const row of chatHistory) {
                appendMessage("right", row.comment_human, "");
                appendMessage("left", row.comment_ai, "");
                if (row.chat_summary !== null) {
                  chatSummary.textContent = row.chat_summary;
                }
            }
            scrollDown(areaScroll);
        })
        .catch(error => console.error(error));
}

// ---- append/print message on screen ---- //

function appendMessage(side, text, chat_id) {

    var converter = new showdown.Converter();
    text = converter.makeHtml(text);

    const msgHTML = `
      <div class="msg ${side}-msg">
        <div class="msg-bubble">
          <div class="msg-text" id=${chat_id}>${text}</div>
        </div>
      </div>
    `;

    msgerChat.insertAdjacentHTML("beforeend", msgHTML);
    hljs.highlightAll(); // add code colour formatting, language defined above

}

// ---- summarise chat ---- //

// Event listener for the summarise chat button click
const summaryButton = document.querySelector('#summary-button');
summaryButton.addEventListener('click', event => {
    event.preventDefault();
    summariseChat(CHAT_ID, "page")
        .then(text => {
          chatSummary.textContent = text;
        })
        .catch(error => {
        console.error(error);
        });
});

function summariseChat(chat_id, call_from) {
  var formData = new FormData();
  formData.append('chat_id', chat_id);
  return fetch('fi_summary.php', {method: 'POST', body: formData})
    .then(response => response.text())
    .then(text => {
      if (call_from == "page") return text;
      if (call_from == "hist") {
        var element = document.getElementById(chat_id);
        element.innerHTML = text;
      }
    })
    .catch(error => {
      console.error(error);
      return null;
    });

}

// ---- new chat ---- //

// Event listener for the new chat button click
const chatButton = document.querySelector('#newchat-button');
chatButton.addEventListener('click', event => {
    event.preventDefault();
    uuid = uuidv4();
    window.location.href = api_file + '?chat_id=' + uuid;
});

// ---- history popup ---- //

// Event listener for the chat history button click
const historyButton = document.querySelector('#history-button');
historyButton.addEventListener('click', event => {
    event.preventDefault();
    const popup = document.getElementById("popup-menu");
    popup.style.display = "block"; // Show the popup
});

// ---- exit ---- //

// Event listener for the quit chat button click
const quitButton = document.querySelector('#quit-button');
quitButton.addEventListener('click', event => {
    event.preventDefault();
    window.location.href = 'index.php';
});

// ---- delete ---- //

// Event listener for the Delete button click
const deleteButton = document.querySelector('#delete-button');
deleteButton.addEventListener('click', event => {
    event.preventDefault();
    deleteChatHistory(CHAT_ID, "page");
});

// Function to delete chat history records for a user ID using the API
function deleteChatHistory(chat_id, call_from) {
    var formData = new FormData();
    formData.append('chat_id', chat_id);
    formData.append('query', 'delete_chat');
    fetch('fi_require.php', {method: 'POST', body: formData})
        .then(response => {
            if (!response.ok) {
                throw new Error('Error deleting chat history: ' + response.statusText);
            }
            if (call_from == "page") window.location.href = api_file + '?chat_id=' + uuidv4();
            if (call_from == "hist") {
              var element = document.getElementById(chat_id);
              element.innerHTML = "";
            }
        })
        .catch(error => console.error(error));
}

// ---- model change ---- //

// Event listener for the model change button
const modelButton = document.querySelector('#model-button');
modelButton.addEventListener('click', event => {
    event.preventDefault();
    if (model_id == '3') {
      model_id = '4';
    } else if (model_id == '4') {
      model_id = '5';
    } else {
      model_id = '3';
    }
    idModel.textContent = model_id;
});

// ---- set words in response ---- //

// Event listener for the words change button
const wordButton = document.querySelector('#word-button');
wordButton.addEventListener('click', event => {
    event.preventDefault();
    word_count = word_count + 10;
    nWords.textContent = word_count;
    nWords.style.opacity = 100;
});

// ---- send message to AI api ---- //

// this one checks all textareas on page
$('textarea').keypress(function(e) {
  // Check if the Enter key is pressed
  if (e.keyCode == 13 && !e.shiftKey) {
    e.preventDefault();
    // Trigger the form submission
    const msgText = msgerInput.value;
    if (!msgText) return;
    msgerInput.value = "";
    sendMsg(msgText)
  }
});

msgerForm.addEventListener("submit", event => {
    event.preventDefault();
    const msgText = msgerInput.value;
    if (!msgText) return;
    msgerInput.value = "";
    sendMsg(msgText)
});

function sendMsg(msg) {
    msgerSendBtn.disabled = true
    // create current response bubbles
    if (word_count > 0) {
      msg = msg + "\n\nprovide your response in " + word_count + " words and provide the actual word count";
    }
    appendMessage("right", msg, "");
    let uuid = uuidv4()
    appendMessage("left", "", uuid);
    const div = document.getElementById(uuid);
    scrollDown(areaScroll);
    // prepare message request
    var formData = new FormData();
    formData.append('chat_id', CHAT_ID);
    formData.append('model_id', model_id);
    formData.append('word_count', word_count);
    formData.append('msg', msg);
    // fetch response
    fetch('fi_response.php', {method: 'POST', body: formData})
        .then(response => response.text())
        .then(text => {
          div.innerHTML = text.replace(/(?:\r\n|\r|\n)/g, '<br>');
          var res = div.innerHTML;
          if (res.includes("```")) {
            window.location.href = "";
            // var lang = 'html';
            // if (res.includes("```javascript")) lang = 'javascript';
            // var regex = /```([^`]+)```/g;
            // res = res.replace(regex, '<pre><code class="' + lang + '">$1</code></pre>');
            // div.innerHTML = res;
          } else {
            scrollDown(areaScroll);
          }
        })
        .catch(error => {
          console.error(error);
          alert(error);
          return null;
        });
    msgerSendBtn.disabled = false
}

// async function streamChunks(div, url) { // not developed
//   try {
//     const response = await fetch(url);
//     for await (const chunk of response.body) {
//       alert(chunk);
//       if (chunk.data == "[DONE]") {
//           var res = div.innerHTML;
//           if (res.split("`").length - 1 === 3) { // if has code then rewrite for highlight js script
//             var regex = /```([^`]+)```/g;
//             res = res.replace(regex, '<pre><code class="html language-html hljs">$1</code></pre>'); // but assumes there is only 1 code chunk in the response, or will not write properly
//             div.innerHTML = res;
//           }
//           msgerSendBtn.disabled = false
//           response.close();
//       } else {
//           let txt = JSON.parse(chunk.data).choices[0].delta.content;
//           if (txt !== undefined) {
//               div.innerHTML += txt.replace(/(?:\r\n|\r|\n)/g, '<br>');
//           }
//       }
//     }
//   } catch (e) {
//     if (e instanceof TypeError) {
//       console.log(e);
//       alert("TypeError: Browser may not support async iteration");
//     } else {
//       alert(`Error in async iterator: ${e}.`);
//     }
//   }
// }

// ---- Utils ---- //

function get(selector, root = document) {
    return root.querySelector(selector);
}

function uuidv4() {
    return ([1e7] + -1e3 + -4e3 + -8e3 + -1e11).replace(/[018]/g, c =>
        (c ^ crypto.getRandomValues(new Uint8Array(1))[0] & 15 >> c / 4).toString(16)
    );
}

function scrollDown(element) {
  element.scroll({ top: element.scrollHeight, behavior: 'smooth' });
}

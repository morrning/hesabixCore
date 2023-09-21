/*
 *  Document   : be_comp_chat.js
 *  Author     : pixelcave
 *  Description: Custom JS code used in Chat Page
 */

// Message Classes
let classesMsgBase = ['fs-sm', 'd-inline-block', 'fw-medium', 'animated', 'fadeIn', 'bg-body-light', 'border-3', 'px-3', 'py-2', 'mb-2', 'shadow-sm', 'mw-100'],
    classesMsgSelf = ['border-end', 'border-primary', 'rounded-start', 'text-start'],
    classesMsgOther = ['border-start', 'border-dark', 'rounded-end'],
    classesMsgHeader = ['fs-sm', 'text-muted', 'animated', 'fadeIn', 'my-2'];

class pageCompChat {
  /*
   * Init chat
   *
   */
  static initChat() {
    let self = this;

    // Set variables
    let chatForms = document.querySelectorAll('.js-chat-form form');

    // Init form submission
    chatForms.forEach(form => {
      form.addEventListener('submit', e => {
        // Stop form submission
        e.preventDefault();
  
        // Get chat input
        let chatInput = e.currentTarget.querySelector('.js-chat-input');
  
        // Add message
        self.chatAddMessage(parseInt(chatInput.dataset.targetChatId), chatInput.value, 'self', chatInput);
      });
    });
  }

  /*
   * Add a header message to a chat window
   *
   */
  static chatAddHeader(chatId, chatMsg, chatMsgLevel) {
    // Get chat messages window
    let chatWindow = document.querySelector('.js-chat-messages[data-chat-id="' + chatId + '"]');

    // If header and chat window exists
    if (chatMsg && chatWindow !== null) {
      // Create new message elements and add the correct classes
      let newChatMsgCon = document.createElement('div');
      let newChatMsg = document.createElement('div');

      classesMsgHeader.forEach(cls => {
        newChatMsg.classList.add(cls);
      });

      if (chatMsgLevel === 'self') {
        newChatMsg.classList.add('text-end');
        newChatMsgCon.classList.add('ms-4');
      } else {
        newChatMsgCon.classList.add('me-4');
      }

      // Set text message
      newChatMsg.innerText = chatMsg;

      // Append message to chat window
      newChatMsgCon.appendChild(newChatMsg);
      chatWindow.appendChild(newChatMsgCon);

      // Scroll the message list to the bottom
      chatWindow.scrollTop = chatWindow.scrollHeight;
    }
  }

  /*
   * Add a message to a chat window
   *
   */
  static chatAddMessage(chatId, chatMsg, chatMsgLevel, chatInput) {
    // Get chat messages window
    let chatWindow = document.querySelector('.js-chat-messages[data-chat-id="' + chatId + '"]');

    // If message and chat window exists
    if (chatMsg && chatWindow !== null) {
      // Create new message elements and add the correct classes
      let newChatMsgCon = document.createElement('div');
      let newChatMsg = document.createElement('div');

      classesMsgBase.forEach(cls => {
        newChatMsg.classList.add(cls);
      });

      if (chatMsgLevel === 'self') {
        newChatMsgCon.classList.add('text-end');
        newChatMsgCon.classList.add('ms-4');

        classesMsgSelf.forEach(cls => {
          newChatMsg.classList.add(cls);
        });
      } else {
        newChatMsgCon.classList.add('me-4');

        classesMsgOther.forEach(cls => {
          newChatMsg.classList.add(cls);
        });
      }

      // Set text message
      newChatMsg.innerText = chatMsg;

      // Append message to chat window
      newChatMsgCon.appendChild(newChatMsg);
      chatWindow.appendChild(newChatMsgCon);

      // Scroll the message list to the bottom
      chatWindow.scrollTop = chatWindow.scrollHeight;

      // If input is set, reset it
      if (chatInput !== null) {
        chatInput.value = '';
      }
    }
  }

  /*
   * Init functionality
   *
   */
  static init() {
    this.initChat();
  }

  /*
   * Add header
   *
   */
  static addHeader(chatId, chatMsg, chatMsgLevel = '') {
    this.chatAddHeader(chatId, chatMsg, chatMsgLevel);
  }

  /*
   * Add message
   *
   */
  static addMessage(chatId, chatMsg, chatMsgLevel = '') {
    this.chatAddMessage(chatId, chatMsg, chatMsgLevel, null);
  }
}

// Initialize when page loads
Dashmix.onLoad(() => {
  pageCompChat.init();

  window.Chat = pageCompChat;
});
<div id="chatBox">
  <div class="chat-header">
    <div class="chat-title"><i class="fa-solid fa-robot"></i> AI Assistant</div>
    <button onclick="toggleChat()" style="background:none;border:none;color:white;cursor:pointer;font-size:18px;"><i class="fa-solid fa-xmark"></i></button>
  </div>
  <div id="chatMessages">
    <div class="msg-bot">
      <div class="avatar"><i class="fa-solid fa-robot"></i></div>
      <div class="bubble">Hello! Welcome to Bird's Nest Coffee! &#x2615; Ask me about our menu or recommendations!</div>
    </div>
  </div>
  <div class="chat-input">
    <input type="text" id="chatInput" placeholder="Ask me something..." autocomplete="off">
    <button id="chatSendBtn" onclick="sendChat()"><i class="fa-solid fa-paper-plane"></i></button>
  </div>
</div>

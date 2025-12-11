<?php
// Chat widget include for backoffice pages. Outputs the markup and client JS.
?>
<!-- Chat widget (included) -->
<div class="chat-widget" id="chatWidget">
  <div class="chat-panel" id="chatPanel" role="dialog" aria-hidden="true">
    <div class="chat-header">
      <div>Assistant</div>
      <button class="chat-close" onclick="toggleChat()" aria-label="Close chat">×</button>
    </div>
    <div class="chat-messages" id="chatMessages">
      <div class="chat-empty">Ask about events — e.g., "How many participants for Event 3?"</div>
    </div>
    <div class="chat-input-row">
      <input id="chatInput" class="chat-input" placeholder="Ask about events or get help..." />
      <button id="chatSend" class="chat-send" onclick="sendChat()">Send</button>
    </div>
  </div>
  <button class="chat-toggle-btn" id="chatToggle" onclick="toggleChat()">💬</button>
</div>

<script>
  // Backoffice chat widget logic (kept local to include)
  (function(){
    const chatPanelAdmin = document.getElementById('chatPanel');
    const chatMessagesAdmin = document.getElementById('chatMessages');
    const chatInputAdmin = document.getElementById('chatInput');
    const chatSendAdmin = document.getElementById('chatSend');
    let chatOpenAdmin = false;

    function toggleChat() {
      if (!chatPanelAdmin) return;
      chatOpenAdmin = !chatOpenAdmin;
      if (chatOpenAdmin) {
        chatPanelAdmin.classList.add('open');
        chatPanelAdmin.setAttribute('aria-hidden','false');
        setTimeout(() => chatInputAdmin && chatInputAdmin.focus(), 200);
      } else {
        chatPanelAdmin.classList.remove('open');
        chatPanelAdmin.setAttribute('aria-hidden','true');
      }
    }

    window.toggleChat = toggleChat;

    function appendMessage(text, who='bot') {
      if (!chatMessagesAdmin) return;
      const empty = chatMessagesAdmin.querySelector('.chat-empty');
      if (empty) empty.remove();
      const wrapper = document.createElement('div');
      wrapper.className = 'msg ' + (who==='user' ? 'user' : 'bot');
      const bubble = document.createElement('span');
      bubble.className = 'bubble';
      bubble.textContent = text;
      wrapper.appendChild(bubble);
      chatMessagesAdmin.appendChild(wrapper);
      chatMessagesAdmin.scrollTop = chatMessagesAdmin.scrollHeight;
    }

    function setLoading(loading) {
      if (!chatSendAdmin || !chatInputAdmin) return;
      chatSendAdmin.disabled = loading;
      chatInputAdmin.disabled = loading;
      chatSendAdmin.textContent = loading ? '...' : 'Send';
    }

    function sendChat() {
      if (!chatInputAdmin) return;
      const text = chatInputAdmin.value.trim();
      if (!text) return;
      appendMessage(text, 'user');
      chatInputAdmin.value = '';
      setLoading(true);

      fetch('api_chat.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ message: text })
      })
      .then(r => r.text())
      .then(raw => {
        try {
          const data = JSON.parse(raw);
          if (typeof data === 'string') {
            appendMessage(data, 'bot');
          } else if (data && data.choices && data.choices[0] && data.choices[0].message) {
            appendMessage(data.choices[0].message.content || JSON.stringify(data), 'bot');
          } else {
            appendMessage(JSON.stringify(data), 'bot');
          }
        } catch (e) {
          appendMessage(raw, 'bot');
        }
      })
      .catch(err => {
        console.error(err);
        appendMessage('Error: could not reach assistant.', 'bot');
      })
      .finally(() => setLoading(false));
    }

    if (chatInputAdmin) {
      chatInputAdmin.addEventListener('keydown', function(e){ if (e.key === 'Enter') { e.preventDefault(); sendChat(); } });
    }

    if (chatSendAdmin) {
      chatSendAdmin.addEventListener('click', sendChat);
    }
  })();
</script>

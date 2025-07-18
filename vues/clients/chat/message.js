document.addEventListener("DOMContentLoaded", function () {
    const chatForm = document.getElementById("chat-form");
    const chatBox = document.getElementById("chat-box");
    const receiverId = document.getElementById("receiver_id").value;
    const userId = window.USER_ID || null;
    let lastMessagesHash = null;

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function hashMessages(messages) {
        // Simple hash: concat sender_id/message/created_at
        return messages.map(m => m.sender_id + m.message + m.created_at).join('|');
    }

    function renderMessages(messages) {
        chatBox.innerHTML = '';
        messages.forEach(msg => {
            const msgDiv = document.createElement('div');
            msgDiv.className = 'chat-message ' + (msg.sender_id == userId ? 'sent' : 'received');
            msgDiv.innerHTML = `<p>${escapeHtml(msg.message)}</p><span class="chat-time">${msg.time}</span>`;
            chatBox.appendChild(msgDiv);
        });
        chatBox.scrollTop = chatBox.scrollHeight;
    }

    function fetchMessages() {
        fetch(`/api/get_message.php?receiver_id=${receiverId}`)
            .then(response => response.json())
            .then(data => {
                if (data.status === "success" && data.data && data.data.messages) {
                    // Adapter le hash pour la nouvelle structure
                    const newHash = hashMessages(data.data.messages);
                    if (newHash !== lastMessagesHash) {
                        // Adapter le rendu pour utiliser time_formatted si dispo
                        chatBox.innerHTML = '';
                        data.data.messages.forEach(msg => {
                            const msgDiv = document.createElement('div');
                            msgDiv.className = 'chat-message ' + (msg.sender_id == userId ? 'sent' : 'received');
                            msgDiv.innerHTML = `<p>${escapeHtml(msg.message)}</p><span class="chat-time">${msg.time_formatted || msg.created_at}</span>`;
                            chatBox.appendChild(msgDiv);
                        });
                        chatBox.scrollTop = chatBox.scrollHeight;
                        lastMessagesHash = newHash;
                    }
                }
            });
    }

    chatForm.addEventListener("submit", function (e) {
        e.preventDefault();
        const messageInput = document.getElementById("message");
        const message = messageInput.value;
        if (message.trim() !== "") {
            fetch("send.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `receiver_id=${receiverId}&message=${encodeURIComponent(message)}&ajax=1`
            }).then(() => {
                // Ajoute le message localement
                const msgDiv = document.createElement('div');
                msgDiv.className = 'chat-message sent';
                msgDiv.innerHTML = `<p>${escapeHtml(message)}</p><span class="chat-time">Maintenant</span>`;
                chatBox.appendChild(msgDiv);
                chatBox.scrollTop = chatBox.scrollHeight;
                messageInput.value = "";
                // Ne pas appeler fetchMessages ici, le polling s'en charge
            });
        }
    });

    fetchMessages();
    setInterval(fetchMessages, 500); // Polling toutes les 500ms
});

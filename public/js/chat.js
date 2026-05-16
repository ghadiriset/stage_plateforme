(function () {
  const container = document.querySelector('.chat-layout');
  if (!container) return;

  const currentUser   = Number(container.dataset.currentUser);
  const activeContact = Number(container.dataset.activeContact);
  if (!currentUser || !activeContact) return;

  const messagesBox = document.getElementById('messages');
  const form        = document.getElementById('chatForm');
  const input       = document.getElementById('messageInput');

  // Dernier ID connu (pour ne charger que les nouveaux messages)
  let lastId = 0;
  const existing = messagesBox.querySelectorAll('.message[data-id]');
  existing.forEach(el => {
    const id = parseInt(el.dataset.id || '0', 10);
    if (id > lastId) lastId = id;
  });

  // Scroll en bas au chargement
  messagesBox.scrollTop = messagesBox.scrollHeight;

  function appendMessage(msg, mine) {
    const div  = document.createElement('div');
    div.className = 'message ' + (mine ? 'mine' : 'theirs');
    div.dataset.id = msg.id;
    const date = new Date(msg.created_at).toLocaleString('fr-FR');
    div.innerHTML = `<p>${escHtml(msg.content)}</p><span>${date}</span>`;
    messagesBox.appendChild(div);
    messagesBox.scrollTop = messagesBox.scrollHeight;
  }

  function escHtml(str) {
    return String(str)
      .replace(/&/g,'&amp;').replace(/</g,'&lt;')
      .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }

  // Polling toutes les 3 secondes
  function poll() {
    fetch(`/api/messages?contact=${activeContact}&after=${lastId}`)
      .then(r => r.json())
      .then(msgs => {
        msgs.forEach(msg => {
          const mine = Number(msg.sender_id) === currentUser;
          appendMessage(msg, mine);
          if (Number(msg.id) > lastId) lastId = Number(msg.id);
        });
      })
      .catch(() => {}); // ignorer les erreurs réseau
  }

  setInterval(poll, 3000);

  // Envoi du message
  if (form) {
    form.addEventListener('submit', function(e) {
      e.preventDefault();
      const content = input.value.trim();
      if (!content) return;

      fetch('/api/send', {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify({ receiver_id: activeContact, content })
      })
      .then(r => r.json())
      .then(msg => {
        appendMessage(msg, true);
        if (Number(msg.id) > lastId) lastId = Number(msg.id);
        input.value = '';
      })
      .catch(() => alert('Erreur lors de l\'envoi du message.'));
    });
  }
})();

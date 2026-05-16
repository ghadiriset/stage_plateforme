<?php
requireAuth();
$currentUser = currentUser();

$contacts = $currentUser['role'] === 'professor'
    ? dbAll("SELECT id, full_name, role, department FROM users WHERE role='student' ORDER BY full_name")
    : dbAll("SELECT id, full_name, role, department FROM users WHERE role='professor' ORDER BY full_name");

$activeContactId = (int)($_GET['user'] ?? ($contacts[0]['id'] ?? 0));

$messages = [];
if ($activeContactId) {
    $messages = dbAll('SELECT * FROM messages WHERE (sender_id=? AND receiver_id=?) OR (sender_id=? AND receiver_id=?) ORDER BY id ASC',
        [$currentUser['id'], $activeContactId, $activeContactId, $currentUser['id']]);
}

renderHead('Chat');
renderHeader();
?>
<section class="page-hero compact">
  <div class="container">
    <span class="eyebrow">Messagerie</span>
    <h1>Chat avec les professeurs</h1>
    <p>Discussion privée entre étudiants et encadrants avec actualisation automatique.</p>
  </div>
</section>
<section class="section">
  <div class="container chat-layout"
       data-current-user="<?= $currentUser['id'] ?>"
       data-active-contact="<?= $activeContactId ?>">
    <aside class="chat-sidebar glass-card">
      <h3>Contacts</h3>
      <div class="contact-list">
        <?php foreach ($contacts as $contact): ?>
        <a href="/chat?user=<?= $contact['id'] ?>" class="contact-item <?= $activeContactId === (int)$contact['id'] ? 'active' : '' ?>">
          <div class="avatar"><?= htmlspecialchars(mb_substr($contact['full_name'], 0, 1)) ?></div>
          <div>
            <strong><?= htmlspecialchars($contact['full_name']) ?></strong>
            <small><?= htmlspecialchars($contact['department']) ?> · <?= $contact['role'] === 'professor' ? 'Professeur' : 'Étudiant' ?></small>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
    </aside>
    <div class="chat-panel glass-card">
      <div class="messages" id="messages">
        <?php foreach ($messages as $msg): ?>
        <div class="message <?= (int)$msg['sender_id'] === (int)$currentUser['id'] ? 'mine' : 'theirs' ?>"
             data-id="<?= $msg['id'] ?>">
          <p><?= htmlspecialchars($msg['content']) ?></p>
          <span><?= date('d/m/Y H:i', strtotime($msg['created_at'])) ?></span>
        </div>
        <?php endforeach; ?>
      </div>
      <?php if ($activeContactId): ?>
      <form id="chatForm" class="chat-form" onsubmit="sendMessage(event)">
        <input type="text" id="messageInput" placeholder="Écrire un message..." autocomplete="off" required>
        <button class="btn btn-primary" type="submit">Envoyer</button>
      </form>
      <?php else: ?>
      <p>Aucun contact disponible.</p>
      <?php endif; ?>
    </div>
  </div>
</section>
<?php renderFooter(); ?>

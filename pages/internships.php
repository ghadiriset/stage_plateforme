<?php
$internships = dbAll('SELECT i.*, c.name AS company_name, c.sector FROM internships i JOIN companies c ON c.id = i.company_id ORDER BY i.created_at DESC');
$user = currentUser();
renderHead('Offres de stage'); renderHeader();
?>
<section class="page-hero compact"><div class="container"><span class="eyebrow">Opportunités</span><h1>Offres de stage</h1><p>Parcours clair pour découvrir les offres, postuler et suivre l'avancement.</p>
<?php if ($user && $user['role'] === 'admin'): ?>
<div style="margin-top:1.5rem;display:flex;gap:1rem;align-items:center;flex-wrap:wrap;">
  <a href="/admin/import-internships" class="btn btn-primary">📊 Importer depuis Excel</a>
  <span style="font-size:0.85rem;color:rgba(255,255,255,0.7)"><?= count($internships) ?> offre(s) dans la base</span>
</div>
<?php endif; ?>
</div></section>
<section class="section"><div class="container"><div class="card-grid">
<?php foreach ($internships as $item): ?>
<article class="info-card internship-card">
  <div class="card-top"><span class="badge success"><?= htmlspecialchars($item['status']) ?></span><span class="muted"><?= htmlspecialchars($item['sector']) ?></span></div>
  <h3><?= htmlspecialchars($item['title']) ?></h3>
  <p><strong><?= htmlspecialchars($item['company_name']) ?></strong></p>
  <p><?= htmlspecialchars($item['description']) ?></p>
  <div class="meta wrap"><span><?= htmlspecialchars($item['duration']) ?></span><span><?= htmlspecialchars($item['location']) ?></span><span>Rémunéré: <?= htmlspecialchars($item['paid']) ?></span></div>
  <?php if ($user && $user['role'] === 'student'): ?>
  <form action="/postuler" method="POST">
    <input type="hidden" name="internship_id" value="<?= $item['id'] ?>">
    <button class="btn btn-primary full">Postuler</button>
  </form>
  <?php else: ?>
  <a href="/login" class="btn btn-outline full">Se connecter pour postuler</a>
  <?php endif; ?>
</article>
<?php endforeach; ?>
</div></div></section>
<?php renderFooter();
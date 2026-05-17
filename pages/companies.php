<?php
$companies = dbAll('SELECT * FROM companies ORDER BY name');
$user = currentUser();
renderHead('Entreprises'); renderHeader();
?>
<section class="page-hero compact"><div class="container"><span class="eyebrow">Partenaires</span><h1>Entreprises partenaires</h1><p>Liste des entreprises partenaires où les étudiants peuvent effectuer leur stage.</p>
<?php if ($user && $user['role'] === 'admin'): ?>
<div style="margin-top:1.5rem;display:flex;gap:1rem;align-items:center;flex-wrap:wrap;">
  <a href="/admin/import-companies" class="btn btn-primary">📊 Importer depuis Excel</a>
  <span style="font-size:0.85rem;color:rgba(255,255,255,0.7)"><?= count($companies) ?> entreprise(s) dans la base</span>
</div>
<?php endif; ?>
</div></section>
<section class="section"><div class="container card-grid">
<?php foreach ($companies as $c): ?>
<article class="info-card">
  <div class="card-top"><span class="badge"><?= htmlspecialchars($c['sector']) ?></span><span class="muted"><?= htmlspecialchars($c['location']) ?></span></div>
  <h3><?= htmlspecialchars($c['name']) ?></h3>
  <p><?= htmlspecialchars($c['description']) ?></p>
  <div class="stack-sm">
    <span><strong>Email:</strong> <?= htmlspecialchars($c['contact_email']) ?></span>
    <span><strong>Site:</strong> <a href="<?= htmlspecialchars($c['website']) ?>" target="_blank"><?= htmlspecialchars($c['website']) ?></a></span>
  </div>
</article>
<?php endforeach; ?>
</div></section>
<?php renderFooter();
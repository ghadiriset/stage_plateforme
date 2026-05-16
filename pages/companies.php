<?php
$companies = dbAll('SELECT * FROM companies ORDER BY name');
renderHead('Entreprises'); renderHeader();
?>
<section class="page-hero compact"><div class="container"><span class="eyebrow">Partenaires</span><h1>Entreprises partenaires</h1><p>Liste des entreprises partenaires où les étudiants peuvent effectuer leur stage.</p></div></section>
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

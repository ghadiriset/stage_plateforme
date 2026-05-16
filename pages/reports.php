<?php
$reports = dbAll('SELECT * FROM reports ORDER BY year DESC, id DESC');
renderHead('Anciens rapports'); renderHeader();
?>
<section class="page-hero compact"><div class="container"><span class="eyebrow">Bibliothèque</span><h1>Anciens rapports de stage</h1><p>Une base d'inspiration pour les étudiants.</p></div></section>
<section class="section"><div class="container reports-grid">
<?php foreach ($reports as $r): ?>
<article class="info-card">
  <div class="card-top"><span class="badge soft"><?= htmlspecialchars($r['year']) ?></span><span class="muted"><?= htmlspecialchars($r['specialty']) ?></span></div>
  <h3><?= htmlspecialchars($r['title']) ?></h3>
  <p><?= htmlspecialchars($r['summary']) ?></p>
  <div class="meta wrap"><span><?= htmlspecialchars($r['student_name']) ?></span><span><a href="<?= htmlspecialchars($r['file_url']) ?>">Voir le rapport</a></span></div>
</article>
<?php endforeach; ?>
</div></section>
<?php renderFooter();

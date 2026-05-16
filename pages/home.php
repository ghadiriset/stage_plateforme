<?php
$companies   = dbAll('SELECT * FROM companies ORDER BY id DESC LIMIT 3');
$internships = dbAll('SELECT i.*, c.name AS company_name FROM internships i JOIN companies c ON c.id = i.company_id ORDER BY i.id DESC LIMIT 4');
$reports     = dbAll('SELECT * FROM reports ORDER BY id DESC LIMIT 3');
renderHead('Accueil');
renderHeader();
?>
<section class="hero">
  <div class="container hero-grid">
    <div>
      <span class="eyebrow">Plateforme dynamique liée à l'ISET de Djerba</span>
      <h1>Gérez les stages, les rapports et l'encadrement sur un seul site moderne.</h1>
      <p>Un espace centralisé pour les étudiants, les professeurs et l'administration avec offres de stage, suivi des candidatures, chat en temps réel et archive des anciens rapports.</p>
      <div class="hero-actions">
        <a href="/stages" class="btn btn-primary">Voir les stages</a>
        <a href="/login" class="btn btn-outline">Accéder à la plateforme</a>
      </div>
      <div class="stats-row">
        <div class="stat-card"><strong>+<?= count($companies) ?></strong><span>entreprises partenaires</span></div>
        <div class="stat-card"><strong>+<?= count($internships) ?></strong><span>offres de stage</span></div>
        <div class="stat-card"><strong>Chat</strong><span>étudiants ↔ professeurs</span></div>
      </div>
    </div>
    <div class="hero-panel glass-card">
      <h3>Modules disponibles</h3>
      <ul class="feature-list">
        <li>Gestion des entreprises partenaires</li>
        <li>Publication des offres de stage</li>
        <li>Ajout des étudiants et professeurs</li>
        <li>Archive des anciens rapports</li>
        <li>Espace chat avec les encadrants</li>
        <li>Tableaux de bord selon le rôle</li>
      </ul>
    </div>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="section-head">
      <div><span class="eyebrow">Entreprises</span><h2>Entreprises partenaires</h2></div>
      <a href="/entreprises" class="text-link">Voir tout</a>
    </div>
    <div class="card-grid">
      <?php foreach ($companies as $company): ?>
      <article class="info-card">
        <span class="badge"><?= htmlspecialchars($company['sector']) ?></span>
        <h3><?= htmlspecialchars($company['name']) ?></h3>
        <p><?= htmlspecialchars($company['description']) ?></p>
        <div class="meta"><span><?= htmlspecialchars($company['location']) ?></span><span><?= htmlspecialchars($company['contact_email']) ?></span></div>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section alt-bg">
  <div class="container">
    <div class="section-head">
      <div><span class="eyebrow">Offres récentes</span><h2>Stages disponibles</h2></div>
      <a href="/stages" class="text-link">Explorer</a>
    </div>
    <div class="card-grid">
      <?php foreach ($internships as $item): ?>
      <article class="info-card internship-card">
        <span class="badge success"><?= htmlspecialchars($item['status']) ?></span>
        <h3><?= htmlspecialchars($item['title']) ?></h3>
        <p><strong><?= htmlspecialchars($item['company_name']) ?></strong> — <?= htmlspecialchars($item['description']) ?></p>
        <div class="meta"><span><?= htmlspecialchars($item['location']) ?></span><span><?= htmlspecialchars($item['duration']) ?></span></div>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="section-head">
      <div><span class="eyebrow">Base documentaire</span><h2>Anciens rapports</h2></div>
      <a href="/rapports" class="text-link">Consulter</a>
    </div>
    <div class="card-grid">
      <?php foreach ($reports as $report): ?>
      <article class="info-card">
        <span class="badge soft"><?= htmlspecialchars($report['year']) ?></span>
        <h3><?= htmlspecialchars($report['title']) ?></h3>
        <p><?= htmlspecialchars($report['summary']) ?></p>
        <div class="meta"><span><?= htmlspecialchars($report['student_name']) ?></span><span><?= htmlspecialchars($report['specialty']) ?></span></div>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php renderFooter(); ?>

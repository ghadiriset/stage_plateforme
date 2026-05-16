<?php
requireAuth();
$user = currentUser();

if ($user['role'] === 'admin') {
    $stats = [
        'students'    => dbGet("SELECT COUNT(*) as total FROM users WHERE role='student'")['total'],
        'professors'  => dbGet("SELECT COUNT(*) as total FROM users WHERE role='professor'")['total'],
        'companies'   => dbGet("SELECT COUNT(*) as total FROM companies")['total'],
        'internships' => dbGet("SELECT COUNT(*) as total FROM internships")['total'],
    ];
    $users     = dbAll('SELECT id, full_name, email, role, department, promotion FROM users ORDER BY id DESC');
    $companies = dbAll('SELECT * FROM companies ORDER BY id DESC');
    $reports   = dbAll('SELECT * FROM reports ORDER BY year DESC, id DESC');
    renderHead('Dashboard administrateur');
    renderHeader();
    ?>
<section class="page-hero compact">
  <div class="container">
    <span class="eyebrow">Administration</span>
    <h1>Dashboard administrateur</h1>
    <p>Gérez les utilisateurs, les entreprises, les offres et les rapports depuis un seul espace.</p>
  </div>
</section>
<section class="section">
  <div class="container">
    <div class="stats-row admin-stats">
      <div class="stat-card"><strong><?= $stats['students'] ?></strong><span>Étudiants</span></div>
      <div class="stat-card"><strong><?= $stats['professors'] ?></strong><span>Professeurs</span></div>
      <div class="stat-card"><strong><?= $stats['companies'] ?></strong><span>Entreprises</span></div>
      <div class="stat-card"><strong><?= $stats['internships'] ?></strong><span>Stages</span></div>
    </div>

    <div class="glass-card" style="margin:2rem 0;border:2px solid var(--iset-blue);background:rgba(29,78,216,0.06);">
      <h3 style="color:var(--iset-blue);">🔐 Comptes de vérification / test</h3>
      <p style="font-size:0.85rem;color:var(--text-2);margin-bottom:1rem;">Visibles uniquement par l'administrateur.</p>
      <div class="table-wrap">
        <table>
          <thead><tr><th>Rôle</th><th>Nom</th><th>Email</th><th>Mot de passe</th></tr></thead>
          <tbody>
            <tr><td><span class="badge-role" style="background:#fef3c7;color:#92400e;">Admin</span></td><td>Administrateur ISET Djerba</td><td>admin@isetdjerba.tn</td><td><code>admin123</code></td></tr>
          </tbody>
        </table>
      </div>
    </div>

    <div class="dashboard-grid-2">
      <div class="glass-card">
        <h3>Ajouter un étudiant ou professeur</h3>
        <form method="POST" action="/admin/users" class="form-grid">
          <input name="full_name" placeholder="Nom complet" required>
          <input name="email" type="email" placeholder="Email" required>
          <input name="password" type="password" placeholder="Mot de passe" required>
          <select name="role">
            <option value="student">Étudiant</option>
            <option value="professor">Professeur</option>
            <option value="admin">Admin</option>
          </select>
          <input name="department" placeholder="Département">
          <input name="promotion" placeholder="Promotion / Classe">
          <button class="btn btn-primary full">Ajouter l'utilisateur</button>
        </form>
      </div>
      <div class="glass-card">
        <h3>Ajouter une entreprise</h3>
        <form method="POST" action="/admin/companies" class="form-grid">
          <input name="name" placeholder="Nom entreprise" required>
          <input name="sector" placeholder="Secteur">
          <input name="location" placeholder="Lieu">
          <input name="contact_email" type="email" placeholder="Email contact">
          <input name="website" placeholder="Site web">
          <textarea name="description" placeholder="Description"></textarea>
          <button class="btn btn-primary full">Ajouter l'entreprise</button>
        </form>
      </div>
      <div class="glass-card">
        <h3>Ajouter une offre de stage</h3>
        <form method="POST" action="/admin/internships" class="form-grid">
          <input name="company_id" type="number" placeholder="ID entreprise" required>
          <input name="title" placeholder="Titre du stage" required>
          <input name="duration" placeholder="Durée">
          <input name="location" placeholder="Lieu">
          <input name="paid" placeholder="Rémunération">
          <input name="status" placeholder="Statut" value="Ouvert">
          <textarea name="description" placeholder="Description"></textarea>
          <button class="btn btn-primary full">Publier le stage</button>
        </form>
      </div>
      <div class="glass-card">
        <h3>Ajouter un ancien rapport</h3>
        <form method="POST" action="/admin/reports" class="form-grid" enctype="multipart/form-data">
          <input name="title" placeholder="Titre" required>
          <input name="student_name" placeholder="Nom étudiant" required>
          <input name="year" placeholder="Année">
          <input name="specialty" placeholder="Spécialité">
          <input name="pdf_file" type="file" accept="application/pdf,.pdf">
          <input name="file_url" placeholder="Lien PDF externe (optionnel)">
          <textarea name="summary" placeholder="Résumé"></textarea>
          <button class="btn btn-primary full">Ajouter le rapport</button>
        </form>
      </div>
    </div>

    <div class="glass-card top-gap">
      <h3>📄 Gestion des rapports</h3>
      <div class="table-wrap">
        <table>
          <thead><tr><th>Titre</th><th>Étudiant</th><th>Année</th><th>Actions</th></tr></thead>
          <tbody>
            <?php if ($reports): foreach ($reports as $r): ?>
            <tr>
              <td><strong><?= htmlspecialchars($r['title']) ?></strong></td>
              <td><?= htmlspecialchars($r['student_name']) ?></td>
              <td><?= htmlspecialchars($r['year'] ?: '-') ?></td>
              <td style="display:flex;gap:6px;align-items:center;">
                <a href="<?= htmlspecialchars($r['file_url']) ?>" class="btn btn-outline btn-sm" target="_blank">Voir</a>
                <form method="POST" action="/admin/reports/<?= $r['id'] ?>/delete" onsubmit="return confirm('Supprimer ce rapport ?');" style="display:inline;">
                  <button type="submit" class="btn btn-danger btn-sm">Supprimer</button>
                </form>
              </td>
            </tr>
            <?php endforeach; else: ?>
            <tr><td colspan="4" style="text-align:center;color:var(--text-2);">Aucun rapport.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <div class="dashboard-grid-2 top-gap">
      <div class="glass-card">
        <h3>Utilisateurs récents</h3>
        <div class="table-wrap">
          <table>
            <thead><tr><th>Nom</th><th>Email</th><th>Rôle</th><th>Département</th><th>Action</th></tr></thead>
            <tbody>
              <?php $nonAdmins = array_filter($users, fn($u) => $u['role'] !== 'admin'); ?>
              <?php if ($nonAdmins): foreach ($nonAdmins as $u): ?>
              <tr>
                <td><?= htmlspecialchars($u['full_name']) ?></td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td><span class="badge-role badge-<?= $u['role'] ?>"><?= $u['role'] === 'student' ? 'Étudiant' : 'Professeur' ?></span></td>
                <td><?= htmlspecialchars($u['department'] ?: '-') ?></td>
                <td>
                  <form method="POST" action="/admin/users/<?= $u['id'] ?>/delete" onsubmit="return confirm('Supprimer <?= htmlspecialchars(addslashes($u['full_name'])) ?> ?');" style="display:inline;">
                    <button type="submit" class="btn btn-danger btn-sm">Supprimer</button>
                  </form>
                </td>
              </tr>
              <?php endforeach; else: ?>
              <tr><td colspan="5" style="text-align:center;color:var(--text-2);">Aucun utilisateur.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
      <div class="glass-card">
        <h3>Entreprises</h3>
        <ul class="dashboard-list">
          <?php foreach ($companies as $c): ?>
          <li><strong><?= htmlspecialchars($c['name']) ?></strong> — <?= htmlspecialchars($c['location']) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
  </div>
</section>
    <?php
}

elseif ($user['role'] === 'student') {
    $internships  = dbAll('SELECT i.*, c.name AS company_name FROM internships i JOIN companies c ON c.id = i.company_id ORDER BY i.id DESC LIMIT 6');
    $applications = dbAll('SELECT a.*, i.title, c.name AS company_name FROM applications a JOIN internships i ON i.id = a.internship_id JOIN companies c ON c.id = i.company_id WHERE a.student_id = ? ORDER BY a.id DESC', [$user['id']]);
    $professors   = dbAll("SELECT id, full_name, email, department FROM users WHERE role='professor'");
    renderHead('Dashboard étudiant');
    renderHeader();
    ?>
<section class="page-hero compact">
  <div class="container">
    <span class="eyebrow">Espace étudiant</span>
    <h1>Dashboard étudiant</h1>
    <p>Consultez les offres, postulez rapidement et échangez avec votre encadrant.</p>
  </div>
</section>
<section class="section">
  <div class="container dashboard-grid-2">
    <div class="glass-card wide-card">
      <div class="section-head small-gap">
        <h3>Offres recommandées</h3>
        <a href="/stages" class="text-link">Toutes les offres</a>
      </div>
      <div class="mini-card-grid">
        <?php foreach ($internships as $item): ?>
        <article class="mini-card">
          <span class="badge success"><?= htmlspecialchars($item['company_name']) ?></span>
          <h4><?= htmlspecialchars($item['title']) ?></h4>
          <p><?= htmlspecialchars($item['description']) ?></p>
          <div class="meta wrap"><span><?= htmlspecialchars($item['location']) ?></span><span><?= htmlspecialchars($item['duration']) ?></span></div>
          <form action="/postuler" method="POST">
            <input type="hidden" name="internship_id" value="<?= $item['id'] ?>">
            <button class="btn btn-primary full">Postuler</button>
          </form>
        </article>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="glass-card">
      <h3>Professeurs disponibles</h3>
      <ul class="dashboard-list">
        <?php foreach ($professors as $prof): ?>
        <li>
          <strong><?= htmlspecialchars($prof['full_name']) ?></strong>
          <span><?= htmlspecialchars($prof['department']) ?></span>
          <a href="/chat?user=<?= $prof['id'] ?>">Ouvrir le chat</a>
        </li>
        <?php endforeach; ?>
      </ul>
    </div>
    <div class="glass-card wide-card">
      <h3>Mes candidatures</h3>
      <div class="table-wrap">
        <table>
          <thead><tr><th>Stage</th><th>Entreprise</th><th>Statut</th><th>Date</th></tr></thead>
          <tbody>
            <?php foreach ($applications as $app): ?>
            <tr>
              <td><?= htmlspecialchars($app['title']) ?></td>
              <td><?= htmlspecialchars($app['company_name']) ?></td>
              <td><span class="badge soft"><?= htmlspecialchars($app['status']) ?></span></td>
              <td><?= date('d/m/Y', strtotime($app['created_at'])) ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    <div class="glass-card wide-card">
      <h3>Soumettre un rapport de stage</h3>
      <form method="POST" action="/student/reports" class="form-grid" enctype="multipart/form-data">
        <input name="title" placeholder="Titre du rapport" required>
        <input name="year" placeholder="Année (ex: 2026)">
        <input name="specialty" placeholder="Spécialité">
        <input name="pdf_file" type="file" accept="application/pdf,.pdf">
        <input name="file_url" placeholder="Lien PDF externe (optionnel)">
        <textarea name="summary" placeholder="Résumé du rapport"></textarea>
        <button class="btn btn-primary full">Soumettre le rapport</button>
      </form>
    </div>
  </div>
</section>
    <?php
}

else {
    // Professeur
    $students = dbAll("SELECT id, full_name, email, department, promotion FROM users WHERE role='student' ORDER BY id DESC");
    $reports  = dbAll('SELECT * FROM reports ORDER BY year DESC');
    renderHead('Dashboard professeur');
    renderHeader();
    ?>
<section class="page-hero compact">
  <div class="container">
    <span class="eyebrow">Espace professeur</span>
    <h1>Dashboard professeur</h1>
    <p>Suivi des étudiants, accès aux rapports et communication rapide avec les stagiaires.</p>
  </div>
</section>
<section class="section">
  <div class="container dashboard-grid-2">
    <div class="glass-card">
      <div class="section-head small-gap">
        <h3>Étudiants suivis</h3>
        <a href="/chat" class="text-link">Messagerie</a>
      </div>
      <ul class="dashboard-list">
        <?php foreach ($students as $s): ?>
        <li>
          <strong><?= htmlspecialchars($s['full_name']) ?></strong>
          <span><?= htmlspecialchars($s['department']) ?> · <?= htmlspecialchars($s['promotion'] ?? '') ?></span>
          <a href="/chat?user=<?= $s['id'] ?>">Discuter</a>
        </li>
        <?php endforeach; ?>
      </ul>
    </div>
    <div class="glass-card">
      <h3>Archives de rapports</h3>
      <ul class="dashboard-list">
        <?php foreach ($reports as $r): ?>
        <li>
          <strong><?= htmlspecialchars($r['title']) ?></strong>
          <span><?= htmlspecialchars($r['student_name']) ?> · <?= htmlspecialchars($r['year']) ?></span>
          <a href="/rapports">Voir</a>
        </li>
        <?php endforeach; ?>
      </ul>
    </div>
    <div class="glass-card wide-card">
      <h3>Soumettre un rapport de stage</h3>
      <form method="POST" action="/student/reports" class="form-grid" enctype="multipart/form-data">
        <input name="title" placeholder="Titre du rapport" required>
        <input name="student_name" placeholder="Nom de l'étudiant" required>
        <input name="year" placeholder="Année (ex: 2026)">
        <input name="specialty" placeholder="Spécialité">
        <input name="pdf_file" type="file" accept="application/pdf,.pdf">
        <input name="file_url" placeholder="Lien PDF externe (optionnel)">
        <textarea name="summary" placeholder="Résumé du rapport"></textarea>
        <button class="btn btn-primary full">Soumettre le rapport</button>
      </form>
    </div>
  </div>
</section>
    <?php
}

renderFooter();

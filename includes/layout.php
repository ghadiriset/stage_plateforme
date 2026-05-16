<?php
function renderHead(string $title): void { ?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?= htmlspecialchars($title) ?> | ISET Djerba Stages</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&family=Instrument+Serif:ital@0;1&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css"/>
  <link rel="stylesheet" href="/css/style.css"/>
</head>
<body>
<?php }

function renderHeader(): void {
    $path = currentPath();
    $user = currentUser();
    function navClass(string $p, string $current): string {
        return $p === $current ? 'active' : '';
    }
?>
<header class="site-header">
  <div class="container nav-wrap">
    <a href="/" class="brand">
      <span class="brand-badge">ISET</span>
      <div>
        <strong>ISET Djerba</strong>
        <small>Plateforme des stages</small>
      </div>
    </a>
    <nav class="nav-links">
      <a href="/" class="<?= navClass('/', $path) ?>">Accueil</a>
      <a href="/entreprises" class="<?= navClass('/entreprises', $path) ?>">Entreprises</a>
      <a href="/stages" class="<?= navClass('/stages', $path) ?>">Stages</a>
      <a href="/rapports" class="<?= navClass('/rapports', $path) ?>">Rapports</a>
      <a href="/a-propos" class="<?= navClass('/a-propos', $path) ?>">À propos</a>
      <?php if ($user): ?>
        <a href="/dashboard">Dashboard</a>
        <a href="/chat">Chat</a>
        <a href="/logout" class="btn btn-outline">Déconnexion</a>
      <?php else: ?>
        <a href="/register" class="btn btn-outline" style="margin-right:8px;">S'inscrire</a>
        <a href="/login" class="btn btn-primary">Connexion</a>
      <?php endif; ?>
    </nav>
  </div>
</header>
<?php }

function renderFooter(): void { ?>
<footer class="site-footer">
  <div class="container footer-grid">
    <div>
      <h4>ISET Djerba</h4>
      <p>Plateforme digitale moderne pour gérer les stages, les entreprises partenaires, les rapports et l'encadrement pédagogique.</p>
    </div>
    <div>
      <h4>Accès rapide</h4>
      <ul>
        <li><a href="/stages">Trouver un stage</a></li>
        <li><a href="/rapports">Consulter les rapports</a></li>
        <li><a href="/chat">Contacter un professeur</a></li>
      </ul>
    </div>
    <div>
      <h4>Pages utiles</h4>
      <ul>
        <li><a href="/dashboard">Espace utilisateur</a></li>
        <li><a href="/entreprises">Entreprises</a></li>
        <li><a href="/a-propos">Mission de la plateforme</a></li>
      </ul>
    </div>
  </div>
  <div class="container footer-bottom">© Ghadir Jrijni 2026 — ISET Djerba</div>
</footer>
<script src="/js/chat.js"></script>
</body>
</html>
<?php }

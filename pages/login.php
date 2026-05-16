<?php
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $user = dbGet('SELECT * FROM users WHERE email = ?', [$email]);
    if (!$user || !password_verify($password, $user['password'])) {
        $error = 'Email ou mot de passe invalide.';
    } else {
        $_SESSION['user'] = [
            'id'         => $user['id'],
            'full_name'  => $user['full_name'],
            'email'      => $user['email'],
            'role'       => $user['role'],
            'department' => $user['department'],
            'promotion'  => $user['promotion'],
        ];
        redirect('/dashboard');
    }
}
renderHead('Connexion');
renderHeader();
?>
<section class="section auth-section">
  <div class="container auth-container">
    <div class="glass-card auth-card">
      <span class="eyebrow">Connexion</span>
      <h1>Accéder à la plateforme</h1>
      <p>Connectez-vous selon votre rôle pour gérer les stages et communiquer avec l'encadrement.</p>
      <?php if ($error): ?>
        <div class="alert"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>
      <form method="POST" action="/login" class="stack-lg">
        <div>
          <label>Email</label>
          <input type="email" name="email" placeholder="nom@isetdjerba.tn" required>
        </div>
        <div>
          <label>Mot de passe</label>
          <input type="password" name="password" placeholder="••••••••" required>
        </div>
        <button class="btn btn-primary full">Se connecter</button>
      </form>
    </div>
  </div>
</section>
<?php renderFooter(); ?>

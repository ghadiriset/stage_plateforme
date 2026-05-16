<?php
$error   = null;
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name        = trim($_POST['full_name'] ?? '');
    $email            = trim($_POST['email'] ?? '');
    $password         = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role             = $_POST['role'] ?? '';
    $department       = $_POST['department'] ?? '';
    $promotion        = $_POST['promotion'] ?? '';
    $grade            = $_POST['grade'] ?? '';

    if (!$full_name || !$email || !$password || !$role || !$department) {
        $error = 'Veuillez remplir tous les champs obligatoires.';
    } elseif (!in_array($role, ['student', 'professor'], true)) {
        $error = 'Rôle invalide.';
    } elseif ($password !== $confirm_password) {
        $error = 'Les mots de passe ne correspondent pas.';
    } elseif (strlen($password) < 6) {
        $error = 'Le mot de passe doit contenir au moins 6 caractères.';
    } elseif (dbGet('SELECT id FROM users WHERE email = ?', [$email])) {
        $error = 'Cette adresse email est déjà utilisée.';
    } else {
        $promoValue = $role === 'professor' ? ($grade ?: 'Encadrant') : $promotion;
        dbRun('INSERT INTO users (full_name, email, password, role, department, promotion) VALUES (?,?,?,?,?,?)', [
            $full_name, $email, password_hash($password, PASSWORD_BCRYPT), $role, $department, $promoValue
        ]);
        $success = "Compte créé avec succès ! Bienvenue $full_name. Vous pouvez maintenant vous connecter.";
    }
}
renderHead('Inscription');
renderHeader();
?>
<section class="section auth-section">
  <div class="container auth-container">
    <div class="glass-card auth-card" style="max-width:520px;">
      <span class="eyebrow">Inscription</span>
      <h1>Créer un compte</h1>
      <p>Rejoignez la plateforme des stages de l'ISET Djerba.</p>
      <?php if ($error): ?>
        <div class="alert"><i class="ti ti-alert-circle"></i> <?= htmlspecialchars($error) ?></div>
      <?php endif; ?>
      <?php if ($success): ?>
        <div class="alert" style="background:#d1fae5;color:#065f46;border-color:#6ee7b7;">
          <i class="ti ti-circle-check"></i> <?= htmlspecialchars($success) ?>
        </div>
      <?php endif; ?>
      <form method="POST" action="/register" class="stack-lg" style="margin-top:1.2rem;">
        <div>
          <label>Je suis <span style="color:red">*</span></label>
          <select name="role" required onchange="toggleFields(this.value)">
            <option value="">— Choisir votre profil —</option>
            <option value="student">Étudiant(e)</option>
            <option value="professor">Enseignant(e) / Encadrant(e)</option>
          </select>
        </div>
        <div>
          <label>Nom complet <span style="color:red">*</span></label>
          <input type="text" name="full_name" placeholder="Ex: Ahmed Ben Salem" required>
        </div>
        <div>
          <label>Adresse email <span style="color:red">*</span></label>
          <input type="email" name="email" placeholder="nom@isetdjerba.tn" required>
        </div>
        <div>
          <label>Département / Spécialité <span style="color:red">*</span></label>
          <select name="department" required>
            <option value="">— Choisir —</option>
            <option value="Informatique">Informatique</option>
            <option value="Réseaux">Réseaux &amp; Télécommunications</option>
            <option value="Multimédia">Multimédia &amp; Design Web</option>
            <option value="Génie Électrique">Génie Électrique</option>
            <option value="Génie Mécanique">Génie Mécanique</option>
            <option value="Gestion">Gestion &amp; Commerce</option>
            <option value="Marketing">Marketing</option>
            <option value="Autre">Autre</option>
          </select>
        </div>
        <div id="promo-field">
          <label>Classe / Niveau</label>
          <select name="promotion">
            <option value="">— Choisir —</option>
            <option value="1A">1ère année (L1)</option>
            <option value="2A">2ème année (L2)</option>
            <option value="3A">3ème année (L3 / PFE)</option>
          </select>
        </div>
        <div id="grade-field" style="display:none;">
          <label>Grade / Fonction</label>
          <select name="grade">
            <option value="Encadrant">Encadrant(e) de stage</option>
            <option value="Maître Assistant">Maître Assistant</option>
            <option value="Assistant">Assistant</option>
            <option value="Professeur">Professeur</option>
          </select>
        </div>
        <div>
          <label>Mot de passe <span style="color:red">*</span></label>
          <input type="password" name="password" placeholder="Minimum 6 caractères" minlength="6" required>
        </div>
        <div>
          <label>Confirmer le mot de passe <span style="color:red">*</span></label>
          <input type="password" name="confirm_password" placeholder="Répéter le mot de passe" minlength="6" required>
        </div>
        <button type="submit" class="btn btn-primary full">
          <i class="ti ti-user-plus"></i> Créer mon compte
        </button>
        <p style="text-align:center;font-size:0.88rem;color:var(--text-2);">
          Déjà inscrit ? <a href="/login" style="color:var(--iset-blue);">Se connecter →</a>
        </p>
      </form>
    </div>
  </div>
</section>
<script>
function toggleFields(role) {
  document.getElementById('promo-field').style.display = role === 'professor' ? 'none' : 'block';
  document.getElementById('grade-field').style.display = role === 'professor' ? 'block' : 'none';
}
</script>
<?php renderFooter(); ?>

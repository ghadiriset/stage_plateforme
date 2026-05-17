<?php
/**
 * pages/admin/import_internships.php
 * Import Excel → insertion directe dans internships
 */

require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/layout.php';

sessionStart();
requireRole('admin');

$result  = null;
$errors  = [];
$preview = [];

// ── Lecture xlsx sans dépendance externe ──────────────────────
function readXlsx(string $path): array {

    $rows = [];
    $strings = [];

    $zip = new ZipArchive();

    if ($zip->open($path) !== true) {
        return [];
    }

    $sharedXml = $zip->getFromName('xl/sharedStrings.xml');

    if ($sharedXml) {

        $xml = simplexml_load_string($sharedXml);

        foreach ($xml->si as $si) {

            $val = '';

            foreach ($si->r as $r) {
                $val .= (string)$r->t;
            }

            if (!$val) {
                $val = (string)$si->t;
            }

            $strings[] = trim($val);
        }
    }

    for ($i = 1; $i <= 10; $i++) {

        $sheetXml = $zip->getFromName("xl/worksheets/sheet{$i}.xml");

        if (!$sheetXml) {
            break;
        }

        $xml = simplexml_load_string($sheetXml);

        $first = true;

        foreach ($xml->sheetData->row as $row) {

            $rowData = [];

            foreach ($row->c as $cell) {

                $t = (string)($cell['t'] ?? '');
                $val = (string)$cell->v;

                if ($t === 's') {
                    $val = $strings[(int)$val] ?? '';
                }

                $rowData[] = trim(
                    preg_replace('/[\x{00A0}\x{200B}]/u', ' ', $val)
                );
            }

            // ignorer l'entête
            if ($first) {
                $first = false;
                continue;
            }

            if (count(array_filter($rowData)) === 0) {
                continue;
            }

            $rows[] = $rowData;
        }
    }

    $zip->close();

    return $rows;
}

// ── Extraire stages ───────────────────────────────────────────
// Format Excel :
// A = Stage
// B = classe
// C = entreprise
function extractInternships(array $rows): array {

    $internships = [];

    foreach ($rows as $row) {

        $title       = trim($row[0] ?? '');
        $class       = trim($row[1] ?? '');
        $companyName = trim($row[2] ?? '');

        if (!$title || !$companyName) {
            continue;
        }

        $internships[] = [
            'title'   => $title,
            'class'   => $class,
            'company' => $companyName
        ];
    }

    return $internships;
}

// ── POST : upload du fichier ──────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excel_file'])) {

    $file = $_FILES['excel_file'];

    $ext = strtolower(
        pathinfo($file['name'], PATHINFO_EXTENSION)
    );

    if ($ext !== 'xlsx') {

        $errors[] = "Seuls les fichiers .xlsx sont acceptés.";

    } elseif ($file['error'] !== UPLOAD_ERR_OK) {

        $errors[] = "Erreur lors de l'upload.";

    } elseif ($file['size'] > 10 * 1024 * 1024) {

        $errors[] = "Fichier trop grand (max 10 Mo).";

    } else {

        $rows = readXlsx($file['tmp_name']);

        $internships = extractInternships($rows);

        if (empty($internships)) {

            $errors[] = "Aucun stage trouvé dans le fichier.";

        } else {

            // Aperçu
            foreach (array_slice($internships, 0, 15) as $stage) {

                $company = dbGet(
                    'SELECT id
                     FROM companies
                     WHERE LOWER(name)=LOWER(?)',
                    [$stage['company']]
                );

                $exists = false;

                if ($company) {

                    $exists = dbGet(
                        'SELECT id
                         FROM internships
                         WHERE LOWER(title)=LOWER(?)
                         AND company_id=?',
                        [
                            $stage['title'],
                            $company['id']
                        ]
                    );
                }

                $preview[] = [
                    'title'   => $stage['title'],
                    'company' => $stage['company'],
                    'exists'  => (bool)$exists
                ];
            }

            $_SESSION['import_internships'] = $internships;
        }
    }
}

// ── POST : confirmation ───────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['confirm_import'])) {

    $internships = $_SESSION['import_internships'] ?? [];

    $inserted = 0;
    $skipped  = 0;

    foreach ($internships as $stage) {

        // vérifier entreprise
        $company = dbGet(
            'SELECT id
             FROM companies
             WHERE LOWER(name)=LOWER(?)',
            [$stage['company']]
        );

        // créer entreprise si inexistante
        if (!$company) {

            dbRun(
                'INSERT INTO companies
                (name, sector, location, description, contact_email, website)
                VALUES (?,?,?,?,?,?)',
                [
                    $stage['company'],
                    '',
                    '',
                    '',
                    '',
                    ''
                ]
            );

            $company = dbGet(
                'SELECT id
                 FROM companies
                 WHERE LOWER(name)=LOWER(?)',
                [$stage['company']]
            );
        }

        // vérifier doublon
        $exists = dbGet(
            'SELECT id
             FROM internships
             WHERE LOWER(title)=LOWER(?)
             AND company_id=?',
            [
                $stage['title'],
                $company['id']
            ]
        );

        if ($exists) {
            $skipped++;
            continue;
        }

        // insertion stage
        dbRun(
            'INSERT INTO internships
            (company_id, title, description, duration, location, paid, status)
            VALUES (?,?,?,?,?,?,?)',
            [
                $company['id'],
                $stage['title'],
                '',
                '',
                '',
                'Non',
                'Ouvert'
            ]
        );

        $inserted++;
    }

    unset($_SESSION['import_internships']);

    $result = compact('inserted', 'skipped');
}

renderHead('Import stages');
renderHeader();
?>

<section class="page-hero compact">
  <div class="container">
    <span class="eyebrow">Administration</span>
    <h1>Importer des stages depuis Excel</h1>
    <p>Les stages du fichier seront ajoutées directement dans la liste des stages partenaires.</p>
  </div>
</section>

<section class="section">
  <div class="container" style="max-width:760px">

    <?php if ($result): ?>
    <!-- ── Résultat ── -->
    <div class="glass-card" style="border:2px solid var(--success);background:var(--success-bg);margin-bottom:2rem;">
      <h3 style="color:#1e6b42;margin-bottom:1.5rem;">✅ Import terminé !</h3>
      <div class="stats-row" style="margin:0 0 1.5rem">
        <div class="stat-card"><strong><?= $result['inserted'] ?></strong><span>stages ajoutées</span></div>
        <div class="stat-card"><strong><?= $result['skipped'] ?></strong><span>Doublons ignorés</span></div>
      </div>
      <div style="display:flex;gap:1rem;flex-wrap:wrap;">
        <a href="/stages" class="btn btn-primary">Voir les stages</a>
        <a href="/admin/import-internships" class="btn btn-outline">Importer un autre fichier</a>
      </div>
    </div>

    <?php elseif (!empty($preview)): ?>
    <!-- ── Aperçu avant confirmation ── -->
    <div class="glass-card" style="margin-bottom:2rem;">
      <h3 style="margin-bottom:.5rem;">Aperçu — <?= count($_SESSION['import_internships'] ?? []) ?> stages détectées</h3>
      <p style="font-size:.85rem;color:var(--text-2);margin-bottom:1rem;">
        Vérifiez les données avant de confirmer. Les doublons seront ignorés automatiquement.
      </p>
      <div class="table-wrap">
        <table>
          <thead>
            <tr><th>Nom de l'entreprise</th><th>Statut</th></tr>
          </thead>
          <tbody>
            <?php foreach ($preview as $p): ?>
            <tr>
              <td><?= htmlspecialchars($p['title']) ?></td>
              <td>
                <?php if ($p['exists']): ?>
                  <span class="badge warning">Déjà existante</span>
                <?php else: ?>
                  <span class="badge success">Nouvelle</span>
                <?php endif; ?>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php $total = count($_SESSION['import_internships'] ?? []); ?>
      <?php if ($total > 15): ?>
        <p style="font-size:.85rem;color:var(--text-2);margin-top:.75rem;">
          … et <?= $total - 15 ?> stages supplémentaires.
        </p>
      <?php endif; ?>
      <form method="POST" action="/admin/import-internships" style="margin-top:1.5rem;display:flex;gap:1rem;flex-wrap:wrap;">
        <button type="submit" name="confirm_import" value="1" class="btn btn-primary">
          ✅ Confirmer l'import (<?= $total ?> stages)
        </button>
        <a href="/admin/import-internships" class="btn btn-outline">Annuler</a>
      </form>
    </div>

    <?php else: ?>
    <!-- ── Formulaire upload ── -->

    <?php if ($errors): ?>
    <div class="glass-card" style="border:2px solid var(--error);background:var(--error-bg);margin-bottom:1.5rem;">
      <?php foreach ($errors as $e): ?>
        <p style="color:var(--error)">⚠️ <?= htmlspecialchars($e) ?></p>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="glass-card">
      <h3 style="margin-bottom:1rem;">📂 Choisir un fichier Excel</h3>

      <div class="glass-card" style="background:var(--iset-blue-light);border:none;margin-bottom:1.5rem;font-size:.85rem;">
        <strong>Format attendu :</strong> Le fichier doit avoir une colonne <strong>entreprise</strong> en 3ème colonne (colonne C).
        <br>Exemple : <code>Stage | classe | entreprise</code>
        <br><br>
        <span style="color:var(--text-2)">Toutes les feuilles du fichier seront lues. La première ligne (entête) est ignorée.</span>
      </div>

      <form method="POST" action="/admin/import-internships" enctype="multipart/form-data" class="form-grid">
        <div>
          <label style="display:block;font-weight:600;margin-bottom:.5rem;">Fichier Excel (.xlsx)</label>
          <input type="file" name="excel_file" accept=".xlsx" required
                 style="display:block;width:100%;padding:.6rem;border:2px dashed var(--border-mid);border-radius:8px;background:var(--surface);cursor:pointer;">
        </div>
        <button type="submit" class="btn btn-primary" style="margin-top:.5rem;">
          👁 Aperçu avant import
        </button>
      </form>
    </div>

    <div style="margin-top:1.5rem;">
      <a href="/stages" class="btn btn-outline">← Retour aux stages</a>
    </div>

    <?php endif; ?>
  </div>
</section>

<?php renderFooter(); ?>
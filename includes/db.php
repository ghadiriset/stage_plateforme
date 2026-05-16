<?php
function getDb(): PDO {
    static $pdo = null;
    if ($pdo) return $pdo;

    $dsn = getenv('DATABASE_URL') ?: '';
    if ($dsn) {
        // Format PostgreSQL sur Render : postgres://user:pass@host:port/dbname
        $url = parse_url($dsn);
        $host = $url['host'];
        $port = $url['port'] ?? 5432;
        $db   = ltrim($url['path'], '/');
        $user = $url['user'];
        $pass = $url['pass'];
        $pdo = new PDO(
            "pgsql:host=$host;port=$port;dbname=$db;sslmode=require",
            $user, $pass,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
        );
    } else {
        // MySQL local (développement)
        $host = getenv('DB_HOST') ?: 'localhost';
        $db   = getenv('DB_NAME') ?: 'iset_stages';
        $user = getenv('DB_USER') ?: 'root';
        $pass = getenv('DB_PASS') ?: '';
        $pdo = new PDO(
            "mysql:host=$host;dbname=$db;charset=utf8mb4",
            $user, $pass,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
        );
    }
    return $pdo;
}

function dbQuery(string $sql, array $params = []): PDOStatement {
    $stmt = getDb()->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

function dbGet(string $sql, array $params = []): ?array {
    return dbQuery($sql, $params)->fetch() ?: null;
}

function dbAll(string $sql, array $params = []): array {
    return dbQuery($sql, $params)->fetchAll();
}

function dbRun(string $sql, array $params = []): void {
    dbQuery($sql, $params);
}

function initDb(): void {
    $pdo = getDb();
    $isPostgres = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'pgsql';

    $serial = $isPostgres ? 'SERIAL' : 'INT AUTO_INCREMENT';
    $ts     = $isPostgres ? 'TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP' : 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP';

    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id $serial PRIMARY KEY,
        full_name TEXT NOT NULL,
        email TEXT UNIQUE NOT NULL,
        password TEXT NOT NULL,
        role TEXT NOT NULL,
        department TEXT,
        promotion TEXT,
        created_at $ts
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS companies (
        id $serial PRIMARY KEY,
        name TEXT NOT NULL,
        sector TEXT,
        location TEXT,
        description TEXT,
        contact_email TEXT,
        website TEXT,
        created_at $ts
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS internships (
        id $serial PRIMARY KEY,
        company_id INT NOT NULL,
        title TEXT NOT NULL,
        description TEXT,
        duration TEXT,
        location TEXT,
        paid TEXT,
        status TEXT DEFAULT 'Ouvert',
        created_at $ts
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS reports (
        id $serial PRIMARY KEY,
        title TEXT NOT NULL,
        student_name TEXT NOT NULL,
        year TEXT,
        specialty TEXT,
        summary TEXT,
        file_url TEXT,
        created_at $ts
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS applications (
        id $serial PRIMARY KEY,
        student_id INT NOT NULL,
        internship_id INT NOT NULL,
        status TEXT DEFAULT 'En attente',
        created_at $ts
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS messages (
        id $serial PRIMARY KEY,
        sender_id INT NOT NULL,
        receiver_id INT NOT NULL,
        content TEXT NOT NULL,
        created_at $ts
    )");

    // Admin par défaut
    $admin = dbGet('SELECT id FROM users WHERE email = ?', ['admin@isetdjerba.tn']);
    if (!$admin) {
        dbRun('INSERT INTO users (full_name, email, password, role, department, promotion) VALUES (?,?,?,?,?,?)', [
            'Administrateur ISET Djerba', 'admin@isetdjerba.tn',
            password_hash('admin123', PASSWORD_BCRYPT), 'admin', 'Administration', '2026'
        ]);
    }

    // Données initiales entreprises
    $count = dbGet('SELECT COUNT(*) as c FROM companies')['c'] ?? 0;
    if ((int)$count === 0) {
        $companies = [
            ['Djerba Tech Solutions','Développement Web','Houmt Souk','Entreprise spécialisée dans les applications web et mobiles.','contact@djerbatech.tn','https://djerbatech.tn'],
            ['Smart Tourism Lab','Tourisme & Data','Midoun','Solutions digitales pour le tourisme intelligent à Djerba.','jobs@smarttourism.tn','https://smarttourism.tn'],
            ['Tunisia Cloud Services','Cloud & DevOps','à distance','Accompagnement cloud, intégration continue et sécurité.','hr@tcs.tn','https://tunisiacloud.tn'],
        ];
        foreach ($companies as $c) {
            dbRun('INSERT INTO companies (name,sector,location,description,contact_email,website) VALUES (?,?,?,?,?,?)', $c);
        }
        $internships = [
            [1,'Stage Développeur Full Stack',"Création d'une plateforme de gestion interne en Node.js et Vue.",'2 à 3 mois','Houmt Souk','Oui','Ouvert'],
            [2,'Stage UX/UI & Front-end',"Conception d'interfaces modernes pour une application touristique.",'1 à 2 mois','Midoun','Selon profil','Ouvert'],
            [3,'Stage DevOps Junior','Mise en place de pipelines CI/CD et supervision des services.','3 mois','Hybride','Oui','Ouvert'],
            [1,'Stage Base de données','Optimisation de schémas SQL et génération de tableaux de bord.','2 mois','Houmt Souk','Non','Ouvert'],
        ];
        foreach ($internships as $i) {
            dbRun('INSERT INTO internships (company_id,title,description,duration,location,paid,status) VALUES (?,?,?,?,?,?,?)', $i);
        }
    }

    $rcount = dbGet('SELECT COUNT(*) as c FROM reports')['c'] ?? 0;
    if ((int)$rcount === 0) {
        $reports = [
            ["Développement d'une application de réservation touristique",'Sarra Gharbi','2024','Informatique','Rapport sur une application de réservation avec React et API REST.','#'],
            ["Automatisation du déploiement cloud",'Youssef Charfi','2023','Réseaux',"Projet d'automatisation CI/CD avec conteneurs et surveillance.",'#'],
            ["Refonte UX d'un portail académique",'Mouna Jaziri','2022','Multimédia','Analyse ergonomique et prototype haute fidélité pour portail étudiant.','#'],
        ];
        foreach ($reports as $r) {
            dbRun('INSERT INTO reports (title,student_name,year,specialty,summary,file_url) VALUES (?,?,?,?,?,?)', $r);
        }
    }
}

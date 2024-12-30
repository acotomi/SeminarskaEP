<?php
require_once __DIR__ . '/../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Preveri, če je uporabnik prijavljen
if (!isset($_SESSION['stranka_id'])) {
    header('Location: ../public/login.php');
    exit;
}

$narocila = [];

try {
    // Pridobi vsa naročila stranke
    $stmt = $pdo->prepare("
        SELECT 
            n.id as narocilo_id,
            n.datum_oddaje,
            n.status,
            n.skupna_cena,
            GROUP_CONCAT(
                CONCAT(
                    na.kolicina,
                    'x ',
                    a.naziv,
                    ' (',
                    FORMAT(na.cena_na_kos, 2),
                    ' €)'
                ) SEPARATOR '; '
            ) as izdelki
        FROM narocilo n
        JOIN narocilo_artikel na ON n.id = na.narocilo_id
        JOIN artikel a ON na.artikel_id = a.id
        WHERE n.stranka_id = ?
        GROUP BY n.id
        ORDER BY n.datum_oddaje DESC
    ");
    $stmt->execute([$_SESSION['stranka_id']]);
    $narocila = $stmt->fetchAll();
} catch (PDOException $e) {
    // Če tabela ne obstaja, ne naredi nič
    if ($e->getCode() != '42S02') {
        throw $e;
    }
}

// Slovar statusov za lepši prikaz
$status_classes = [
    'oddano' => 'warning',
    'potrjeno' => 'success',
    'preklicano' => 'danger',
    'stornirano' => 'secondary'
];

// Slovar statusov za prikaz
$status_labels = [
    'oddano' => 'Oddano',
    'potrjeno' => 'Potrjeno',
    'preklicano' => 'Preklicano',
    'stornirano' => 'Stornirano'
];
?>

<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Moja Naročila - Spletna Prodajalna</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="../public/index.php">Spletna Prodajalna</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../public/kosarica.php">Košarica</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="narocila.php">Moja Naročila</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="profil.php">Moj Profil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../public/odjava.php">Odjava</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2 class="mb-4">Moja Naročila</h2>

        <?php if (empty($narocila)): ?>
            <div class="alert alert-info">
                Nimate še nobenih naročil. <a href="../public/index.php" class="alert-link">Začnite z nakupovanjem</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Številka naročila</th>
                            <th>Datum</th>
                            <th>Status</th>
                            <th>Izdelki</th>
                            <th>Skupna cena</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($narocila as $narocilo): ?>
                            <tr>
                                <td>#<?= $narocilo['narocilo_id'] ?></td>
                                <td><?= date('d.m.Y H:i', strtotime($narocilo['datum_oddaje'])) ?></td>
                                <td>
                                    <span class="badge bg-<?= $status_classes[$narocilo['status']] ?>">
                                        <?= $status_labels[$narocilo['status']] ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($narocilo['izdelki']) ?></td>
                                <td><?= number_format($narocilo['skupna_cena'], 2) ?> €</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

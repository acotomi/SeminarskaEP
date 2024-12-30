<?php
require_once __DIR__ . '/../config/config.php';

session_start();

// Check if user is logged in and is a seller
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'prodajalec') {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

// Get order statistics
try {
    // Count new orders
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM narocilo WHERE status = 'oddano'");
    $stmt->execute();
    $nova_narocila = $stmt->fetch()['total'] ?? 0;

    // Count confirmed orders
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM narocilo WHERE status = 'potrjeno' AND prodajalec_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $potrjena_narocila = $stmt->fetch()['total'] ?? 0;
} catch (PDOException $e) {
    // If table doesn't exist, set counts to 0
    $nova_narocila = 0;
    $potrjena_narocila = 0;
}

?>
<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prodajalec Dashboard - Spletna Prodajalna</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="<?= BASE_URL ?>/seller/index.php">Prodajalec Dashboard</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <span class="nav-link">Dobrodošli, <?= htmlspecialchars($_SESSION['user_name']) ?></span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/odjava.php">Odjava</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h1>Prodajalec Dashboard</h1>
        
        <?php if ($nova_narocila > 0): ?>
        <div class="alert alert-info mt-3">
            <strong>Nova naročila!</strong> Imate <?= $nova_narocila ?> novih naročil, ki čakajo na potrditev.
        </div>
        <?php endif; ?>

        <div class="row mt-4">
            <div class="col-md-4">
                <div class="card text-white bg-primary">
                    <div class="card-body">
                        <h5 class="card-title">Nova Naročila</h5>
                        <p class="card-text display-4"><?= $nova_narocila ?></p>
                        <p class="card-text">Naročila, ki čakajo na potrditev</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-success">
                    <div class="card-body">
                        <h5 class="card-title">Potrjena Naročila</h5>
                        <p class="card-text display-4"><?= $potrjena_narocila ?></p>
                        <p class="card-text">Vaša aktivna naročila</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Upravljanje Artiklov</h5>
                        <p class="card-text">Dodajanje, urejanje in brisanje artiklov v vaši trgovini.</p>
                        <a href="artikli.php" class="btn btn-primary">Upravljaj Artikle</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Pregled Naročil</h5>
                        <p class="card-text">Pregled in upravljanje naročil strank.</p>
                        <a href="narocila.php" class="btn btn-primary">Preglej Naročila</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

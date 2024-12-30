<?php
require_once __DIR__ . '/../config/config.php';

session_start();

// Check if user is logged in and is an administrator
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'administrator') {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

?>
<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Spletna Prodajalna</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="<?= BASE_URL ?>/admin/index.php">Admin Dashboard</a>
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
        <h1>Admin Dashboard</h1>
        <div class="row mt-4">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Upravljanje Prodajalcev</h5>
                        <p class="card-text">Dodajanje, urejanje in brisanje prodajalcev.</p>
                        <a href="prodajalci.php" class="btn btn-primary">Upravljaj Prodajalce</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Pregled Naročil</h5>
                        <p class="card-text">Pregled vseh naročil in njihovih statusov.</p>
                        <a href="narocila.php" class="btn btn-primary">Preglej Naročila</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Upravljanje Artiklov</h5>
                        <p class="card-text">Dodajanje, urejanje in brisanje artiklov.</p>
                        <a href="artikli.php" class="btn btn-primary">Upravljaj Artikle</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

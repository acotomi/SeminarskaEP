<?php
require_once __DIR__ . '/../../includes/check_seller_cert.php';
requireSeller();

// Get unprocessed orders count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM narocilo WHERE status = 'oddano'");
$stmt->execute();
$unprocessed_count = $stmt->fetchColumn();

// Get confirmed orders count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM narocilo WHERE status = 'potrjeno'");
$stmt->execute();
$confirmed_count = $stmt->fetchColumn();
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
            <a class="navbar-brand" href="index.php">Prodajalec Dashboard</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="profile.php">Moj Profil</a>
                <a class="nav-link" href="orders.php">Naročila</a>
                <a class="nav-link" href="products.php">Artikli</a>
                <a class="nav-link" href="customers.php">Stranke</a>
                <a class="nav-link" href="../logout.php">Odjava</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h1>Dobrodošli, <?= htmlspecialchars($_SESSION['user_name']) ?></h1>
        
        <div class="row mt-4">
            <div class="col-md-6 col-lg-3 mb-4">
                <div class="card text-white bg-warning">
                    <div class="card-body">
                        <h5 class="card-title">Nova Naročila</h5>
                        <p class="card-text display-4"><?= $unprocessed_count ?></p>
                        <a href="orders.php?status=oddano" class="btn btn-light">Preglej Nova Naročila</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-3 mb-4">
                <div class="card text-white bg-success">
                    <div class="card-body">
                        <h5 class="card-title">Aktivna Naročila</h5>
                        <p class="card-text display-4"><?= $confirmed_count ?></p>
                        <a href="orders.php?status=potrjeno" class="btn btn-light">Preglej Aktivna Naročila</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-3 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Upravljanje Artiklov</h5>
                        <p class="card-text">Dodajanje in urejanje artiklov v prodajalni.</p>
                        <a href="products.php" class="btn btn-primary">Upravljaj Artikle</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-3 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Upravljanje Strank</h5>
                        <p class="card-text">Dodajanje in urejanje računov strank.</p>
                        <a href="customers.php" class="btn btn-primary">Upravljaj Stranke</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

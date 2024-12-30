<?php
require_once __DIR__ . '/../../includes/check_admin_cert.php';
requireAdmin();
?>

<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrator Dashboard - Spletna Prodajalna</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">Administrator Dashboard</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="profile.php">Moj Profil</a>
                <a class="nav-link" href="manage_sellers.php">Upravljanje Prodajalcev</a>
                <a class="nav-link" href="../logout.php">Odjava</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h1>Dobrodošli, <?= htmlspecialchars($_SESSION['user_name']) ?></h1>
        
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Upravljanje Prodajalcev</h5>
                        <p class="card-text">Dodajanje, urejanje in deaktiviranje računov prodajalcev.</p>
                        <a href="manage_sellers.php" class="btn btn-primary">Upravljaj Prodajalce</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Moj Profil</h5>
                        <p class="card-text">Posodobitev osebnih podatkov in gesla.</p>
                        <a href="profile.php" class="btn btn-primary">Uredi Profil</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

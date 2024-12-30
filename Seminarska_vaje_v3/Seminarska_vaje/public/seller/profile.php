<?php
require_once __DIR__ . '/../../includes/check_seller_cert.php';
requireSeller();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ime = filter_input(INPUT_POST, 'ime', FILTER_SANITIZE_STRING);
    $priimek = filter_input(INPUT_POST, 'priimek', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $trenutno_geslo = $_POST['trenutno_geslo'] ?? '';
    $novo_geslo = $_POST['novo_geslo'] ?? '';
    $potrdi_geslo = $_POST['potrdi_geslo'] ?? '';

    if (empty($ime) || empty($priimek) || empty($email)) {
        $error = 'Vsa polja so obvezna.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM prodajalec WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $seller = $stmt->fetch();

        if (!$seller) {
            $error = 'Napaka pri pridobivanju podatkov.';
        } else {
            // Update basic info
            $stmt = $pdo->prepare("UPDATE prodajalec SET ime = ?, priimek = ?, email = ? WHERE id = ?");
            $stmt->execute([$ime, $priimek, $email, $_SESSION['user_id']]);

            // Update password if provided
            if (!empty($trenutno_geslo)) {
                if (!password_verify($trenutno_geslo, $seller['geslo'])) {
                    $error = 'Trenutno geslo ni pravilno.';
                } else if (empty($novo_geslo) || empty($potrdi_geslo)) {
                    $error = 'Prosim vnesite novo geslo in potrditev.';
                } else if ($novo_geslo !== $potrdi_geslo) {
                    $error = 'Gesli se ne ujemata.';
                } else {
                    $hashed_password = password_hash($novo_geslo, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE prodajalec SET geslo = ? WHERE id = ?");
                    $stmt->execute([$hashed_password, $_SESSION['user_id']]);
                }
            }

            if (empty($error)) {
                $_SESSION['user_name'] = $ime . ' ' . $priimek;
                $success = 'Profil uspešno posodobljen.';
            }
        }
    }
}

// Get current user data
$stmt = $pdo->prepare("SELECT * FROM prodajalec WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$seller = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Uredi Profil - Prodajalec</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">Prodajalec Dashboard</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link active" href="profile.php">Moj Profil</a>
                <a class="nav-link" href="orders.php">Naročila</a>
                <a class="nav-link" href="products.php">Artikli</a>
                <a class="nav-link" href="customers.php">Stranke</a>
                <a class="nav-link" href="../logout.php">Odjava</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>Uredi Profil</h2>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label for="ime" class="form-label">Ime</label>
                                <input type="text" class="form-control" id="ime" name="ime" 
                                       value="<?= htmlspecialchars($seller['ime']) ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="priimek" class="form-label">Priimek</label>
                                <input type="text" class="form-control" id="priimek" name="priimek" 
                                       value="<?= htmlspecialchars($seller['priimek']) ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?= htmlspecialchars($seller['email']) ?>" required>
                            </div>

                            <h4 class="mt-4">Spremeni Geslo</h4>

                            <div class="mb-3">
                                <label for="trenutno_geslo" class="form-label">Trenutno Geslo</label>
                                <input type="password" class="form-control" id="trenutno_geslo" name="trenutno_geslo">
                            </div>

                            <div class="mb-3">
                                <label for="novo_geslo" class="form-label">Novo Geslo</label>
                                <input type="password" class="form-control" id="novo_geslo" name="novo_geslo">
                            </div>

                            <div class="mb-3">
                                <label for="potrdi_geslo" class="form-label">Potrdi Novo Geslo</label>
                                <input type="password" class="form-control" id="potrdi_geslo" name="potrdi_geslo">
                            </div>

                            <button type="submit" class="btn btn-primary">Shrani Spremembe</button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Podatki o Certifikatu</h5>
                    </div>
                    <div class="card-body">
                        <dl>
                            <dt>Subject DN</dt>
                            <dd><?= htmlspecialchars($seller['certificate_subject']) ?></dd>
                            
                            <dt>Issuer DN</dt>
                            <dd><?= htmlspecialchars($seller['certificate_issuer']) ?></dd>
                            
                            <dt>Serial Number</dt>
                            <dd><?= htmlspecialchars($seller['certificate_serial']) ?></dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
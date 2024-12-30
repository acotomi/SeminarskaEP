<?php
require_once '../config.php';
require_once '../includes/auth.php';
require_once '../includes/secure_redirect.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Preveri, če je uporabnik administrator
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php?type=admin');
    exit;
}

// Zahtevaj HTTPS
requireSSL();

$success = '';
$error = '';

// Obdelaj akcije
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    $prodajalec_id = $_POST['prodajalec_id'] ?? 0;

    switch ($action) {
        case 'toggle_status':
            try {
                $stmt = $pdo->prepare("UPDATE prodajalec SET aktiven = NOT aktiven WHERE id = ?");
                $stmt->execute([$prodajalec_id]);
                $success = 'Status prodajalca je bil uspešno posodobljen.';
            } catch (PDOException $e) {
                $error = 'Napaka pri posodabljanju statusa prodajalca.';
            }
            break;

        case 'update':
            $ime = $_POST['ime'] ?? '';
            $priimek = $_POST['priimek'] ?? '';
            $email = $_POST['email'] ?? '';
            
            try {
                $stmt = $pdo->prepare("UPDATE prodajalec SET ime = ?, priimek = ?, email = ? WHERE id = ?");
                $stmt->execute([$ime, $priimek, $email, $prodajalec_id]);
                $success = 'Podatki prodajalca so bili uspešno posodobljeni.';
            } catch (PDOException $e) {
                $error = 'Napaka pri posodabljanju podatkov prodajalca.';
            }
            break;

        case 'add':
            $ime = $_POST['ime'] ?? '';
            $priimek = $_POST['priimek'] ?? '';
            $email = $_POST['email'] ?? '';
            $geslo = $_POST['geslo'] ?? '';

            if (empty($ime) || empty($priimek) || empty($email) || empty($geslo)) {
                $error = 'Vsa polja so obvezna.';
            } else {
                try {
                    $stmt = $pdo->prepare("INSERT INTO prodajalec (ime, priimek, email, geslo, aktiven) VALUES (?, ?, ?, ?, TRUE)");
                    $stmt->execute([$ime, $priimek, $email, password_hash($geslo, PASSWORD_DEFAULT)]);
                    $success = 'Nov prodajalec je bil uspešno dodan.';
                } catch (PDOException $e) {
                    $error = 'Napaka pri dodajanju novega prodajalca.';
                }
            }
            break;
    }
}

// Pridobi vse prodajalce
$stmt = $pdo->query("SELECT id, ime, priimek, email, aktiven FROM prodajalec ORDER BY priimek, ime");
$prodajalci = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upravljanje Prodajalcev - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">Admin Panel</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="../odjava.php">Odjava</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h1>Upravljanje Prodajalcev</h1>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!-- Dodajanje novega prodajalca -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Dodaj novega prodajalca</h5>
            </div>
            <div class="card-body">
                <form method="POST" class="row g-3">
                    <input type="hidden" name="action" value="add">
                    <div class="col-md-6">
                        <label for="ime" class="form-label">Ime</label>
                        <input type="text" class="form-control" id="ime" name="ime" required>
                    </div>
                    <div class="col-md-6">
                        <label for="priimek" class="form-label">Priimek</label>
                        <input type="text" class="form-control" id="priimek" name="priimek" required>
                    </div>
                    <div class="col-md-6">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="col-md-6">
                        <label for="geslo" class="form-label">Geslo</label>
                        <input type="password" class="form-control" id="geslo" name="geslo" required>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Dodaj prodajalca</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Seznam prodajalcev -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Seznam prodajalcev</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Ime</th>
                                <th>Priimek</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Akcije</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($prodajalci as $prodajalec): ?>
                                <tr>
                                    <td><?= htmlspecialchars($prodajalec['ime']) ?></td>
                                    <td><?= htmlspecialchars($prodajalec['priimek']) ?></td>
                                    <td><?= htmlspecialchars($prodajalec['email']) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $prodajalec['aktiven'] ? 'success' : 'danger' ?>">
                                            <?= $prodajalec['aktiven'] ? 'Aktiven' : 'Neaktiven' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-primary" 
                                                onclick="odpriUrejanje(<?= htmlspecialchars(json_encode($prodajalec)) ?>)">
                                            <i class="bi bi-pencil"></i> Uredi
                                        </button>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="action" value="toggle_status">
                                            <input type="hidden" name="prodajalec_id" value="<?= $prodajalec['id'] ?>">
                                            <button type="submit" class="btn btn-sm <?= $prodajalec['aktiven'] ? 'btn-danger' : 'btn-success' ?>">
                                                <?= $prodajalec['aktiven'] ? '<i class="bi bi-x-circle"></i> Deaktiviraj' : '<i class="bi bi-check-circle"></i> Aktiviraj' ?>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal za urejanje -->
    <div class="modal fade" id="urediModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Uredi prodajalca</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="prodajalec_id" id="uredi_id">
                        <div class="mb-3">
                            <label for="uredi_ime" class="form-label">Ime</label>
                            <input type="text" class="form-control" id="uredi_ime" name="ime" required>
                        </div>
                        <div class="mb-3">
                            <label for="uredi_priimek" class="form-label">Priimek</label>
                            <input type="text" class="form-control" id="uredi_priimek" name="priimek" required>
                        </div>
                        <div class="mb-3">
                            <label for="uredi_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="uredi_email" name="email" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Prekliči</button>
                        <button type="submit" class="btn btn-primary">Shrani spremembe</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function odpriUrejanje(prodajalec) {
        document.getElementById('uredi_id').value = prodajalec.id;
        document.getElementById('uredi_ime').value = prodajalec.ime;
        document.getElementById('uredi_priimek').value = prodajalec.priimek;
        document.getElementById('uredi_email').value = prodajalec.email;
        
        new bootstrap.Modal(document.getElementById('urediModal')).show();
    }
    </script>
</body>
</html>

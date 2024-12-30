<?php
require_once __DIR__ . '/../../includes/check_admin_cert.php';
requireAdmin();

$success = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add' || $action === 'edit') {
        // Sanitize inputs using htmlspecialchars
        $ime = htmlspecialchars(trim($_POST['ime'] ?? ''), ENT_QUOTES, 'UTF-8');
        $priimek = htmlspecialchars(trim($_POST['priimek'] ?? ''), ENT_QUOTES, 'UTF-8');
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $geslo = $_POST['geslo'] ?? '';
        $seller_id = filter_input(INPUT_POST, 'seller_id', FILTER_VALIDATE_INT);

        if (empty($ime) || empty($priimek) || empty($email) || (empty($geslo) && $action === 'add')) {
            $error = 'Vsa polja so obvezna.';
        } else {
            if ($action === 'add') {
                $stmt = $pdo->prepare("SELECT id FROM prodajalec WHERE email = ?");
                $stmt->execute([$email]);
                if ($stmt->fetch()) {
                    $error = 'Email naslov je že v uporabi.';
                } else {
                    $hashed_password = password_hash($geslo, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("INSERT INTO prodajalec (ime, priimek, email, geslo) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$ime, $priimek, $email, $hashed_password]);
                    $success = 'Prodajalec uspešno dodan.';
                }
            } else {
                $updates = ["ime = ?", "priimek = ?", "email = ?"];
                $params = [$ime, $priimek, $email];
                
                if (!empty($geslo)) {
                    $updates[] = "geslo = ?";
                    $params[] = password_hash($geslo, PASSWORD_DEFAULT);
                }
                
                $params[] = $seller_id;
                
                $sql = "UPDATE prodajalec SET " . implode(", ", $updates) . " WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                $success = 'Prodajalec uspešno posodobljen.';
            }
        }
    } elseif ($action === 'toggle_status') {
        $seller_id = filter_input(INPUT_POST, 'seller_id', FILTER_VALIDATE_INT);
        $current_status = filter_input(INPUT_POST, 'current_status', FILTER_VALIDATE_BOOLEAN);
        
        $stmt = $pdo->prepare("UPDATE prodajalec SET aktiven = ? WHERE id = ?");
        $stmt->execute([!$current_status, $seller_id]);
        $success = 'Status prodajalca uspešno spremenjen.';
    }
}

// Get all sellers
$stmt = $pdo->query("SELECT * FROM prodajalec ORDER BY ime, priimek");
$sellers = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upravljanje Prodajalcev - Administrator</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">Administrator Dashboard</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="profile.php">Moj Profil</a>
                <a class="nav-link active" href="manage_sellers.php">Upravljanje Prodajalcev</a>
                <a class="nav-link" href="../logout.php">Odjava</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>Upravljanje Prodajalcev</h2>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!-- Add New Seller Form -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Dodaj Novega Prodajalca</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="add">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="ime" class="form-label">Ime</label>
                                <input type="text" class="form-control" id="ime" name="ime" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="priimek" class="form-label">Priimek</label>
                                <input type="text" class="form-control" id="priimek" name="priimek" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="geslo" class="form-label">Geslo</label>
                                <input type="password" class="form-control" id="geslo" name="geslo" required>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Dodaj Prodajalca</button>
                </form>
            </div>
        </div>

        <!-- Sellers List -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Seznam Prodajalcev</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
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
                            <?php foreach ($sellers as $seller): ?>
                                <tr>
                                    <td><?= htmlspecialchars($seller['ime']) ?></td>
                                    <td><?= htmlspecialchars($seller['priimek']) ?></td>
                                    <td><?= htmlspecialchars($seller['email']) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $seller['aktiven'] ? 'success' : 'danger' ?>">
                                            <?= $seller['aktiven'] ? 'Aktiven' : 'Neaktiven' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="action" value="toggle_status">
                                            <input type="hidden" name="seller_id" value="<?= $seller['id'] ?>">
                                            <input type="hidden" name="current_status" value="<?= $seller['aktiven'] ?>">
                                            <button type="submit" class="btn btn-sm btn-<?= $seller['aktiven'] ? 'warning' : 'success' ?>">
                                                <?= $seller['aktiven'] ? 'Deaktiviraj' : 'Aktiviraj' ?>
                                            </button>
                                        </form>
                                        <button type="button" class="btn btn-sm btn-primary" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editModal<?= $seller['id'] ?>">
                                            Uredi
                                        </button>
                                    </td>
                                </tr>

                                <!-- Edit Modal -->
                                <div class="modal fade" id="editModal<?= $seller['id'] ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Uredi Prodajalca</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <form method="POST">
                                                    <input type="hidden" name="action" value="edit">
                                                    <input type="hidden" name="seller_id" value="<?= $seller['id'] ?>">
                                                    
                                                    <div class="mb-3">
                                                        <label for="ime<?= $seller['id'] ?>" class="form-label">Ime</label>
                                                        <input type="text" class="form-control" id="ime<?= $seller['id'] ?>" 
                                                               name="ime" value="<?= htmlspecialchars($seller['ime']) ?>" required>
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <label for="priimek<?= $seller['id'] ?>" class="form-label">Priimek</label>
                                                        <input type="text" class="form-control" id="priimek<?= $seller['id'] ?>" 
                                                               name="priimek" value="<?= htmlspecialchars($seller['priimek']) ?>" required>
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <label for="email<?= $seller['id'] ?>" class="form-label">Email</label>
                                                        <input type="email" class="form-control" id="email<?= $seller['id'] ?>" 
                                                               name="email" value="<?= htmlspecialchars($seller['email']) ?>" required>
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <label for="geslo<?= $seller['id'] ?>" class="form-label">Novo Geslo (pustite prazno če ne želite spremeniti)</label>
                                                        <input type="password" class="form-control" id="geslo<?= $seller['id'] ?>" name="geslo">
                                                    </div>
                                                    
                                                    <button type="submit" class="btn btn-primary">Shrani Spremembe</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

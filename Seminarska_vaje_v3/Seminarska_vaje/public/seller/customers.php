<?php
require_once __DIR__ . '/../../includes/check_seller_cert.php';
requireSeller();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add' || $action === 'edit') {
        $ime = filter_input(INPUT_POST, 'ime', FILTER_SANITIZE_STRING);
        $priimek = filter_input(INPUT_POST, 'priimek', FILTER_SANITIZE_STRING);
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $postna_stevilka = filter_input(INPUT_POST, 'postna_stevilka', FILTER_SANITIZE_STRING);
        $geslo = $_POST['geslo'] ?? '';
        $customer_id = filter_input(INPUT_POST, 'customer_id', FILTER_VALIDATE_INT);

        if (empty($ime) || empty($priimek) || empty($email) || empty($postna_stevilka) || (empty($geslo) && $action === 'add')) {
            $error = 'Prosim izpolnite vsa obvezna polja.';
        } else {
            if ($action === 'add') {
                $stmt = $pdo->prepare("SELECT id FROM stranka WHERE email = ?");
                $stmt->execute([$email]);
                if ($stmt->fetch()) {
                    $error = 'Email naslov je že v uporabi.';
                } else {
                    $hashed_password = password_hash($geslo, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("INSERT INTO stranka (ime, priimek, email, geslo, postna_stevilka) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$ime, $priimek, $email, $hashed_password, $postna_stevilka]);
                    $success = 'Stranka uspešno dodana.';
                }
            } else {
                $updates = ["ime = ?", "priimek = ?", "email = ?", "postna_stevilka = ?"];
                $params = [$ime, $priimek, $email, $postna_stevilka];
                
                if (!empty($geslo)) {
                    $updates[] = "geslo = ?";
                    $params[] = password_hash($geslo, PASSWORD_DEFAULT);
                }
                
                $params[] = $customer_id;
                
                $sql = "UPDATE stranka SET " . implode(", ", $updates) . " WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                $success = 'Stranka uspešno posodobljena.';
            }
        }
    } elseif ($action === 'toggle_status') {
        $customer_id = filter_input(INPUT_POST, 'customer_id', FILTER_VALIDATE_INT);
        $current_status = filter_input(INPUT_POST, 'current_status', FILTER_VALIDATE_BOOLEAN);
        
        $stmt = $pdo->prepare("UPDATE stranka SET aktiven = ? WHERE id = ?");
        $stmt->execute([!$current_status, $customer_id]);
        $success = 'Status stranke uspešno spremenjen.';
    }
}

// Get all customers
$stmt = $pdo->query("SELECT * FROM stranka ORDER BY priimek, ime");
$customers = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upravljanje Strank - Prodajalec</title>
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
                <a class="nav-link active" href="customers.php">Stranke</a>
                <a class="nav-link" href="../logout.php">Odjava</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>Upravljanje Strank</h2>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!-- Add New Customer Form -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Dodaj Novo Stranko</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="add">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="ime" class="form-label">Ime*</label>
                                <input type="text" class="form-control" id="ime" name="ime" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="priimek" class="form-label">Priimek*</label>
                                <input type="text" class="form-control" id="priimek" name="priimek" required>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="mb-3">
                                <label for="postna_stevilka" class="form-label">Poštna Številka*</label>
                                <input type="text" class="form-control" id="postna_stevilka" name="postna_stevilka" 
                                       pattern="[0-9]{4}" title="Vnesite 4-mestno poštno številko" required>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email*</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="mb-3">
                                <label for="geslo" class="form-label">Geslo*</label>
                                <input type="password" class="form-control" id="geslo" name="geslo" required>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Dodaj Stranko</button>
                </form>
            </div>
        </div>

        <!-- Customers List -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Seznam Strank</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Ime</th>
                                <th>Priimek</th>
                                <th>Email</th>
                                <th>Poštna Številka</th>
                                <th>Status</th>
                                <th>Akcije</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($customers as $customer): ?>
                                <tr>
                                    <td><?= htmlspecialchars($customer['ime']) ?></td>
                                    <td><?= htmlspecialchars($customer['priimek']) ?></td>
                                    <td><?= htmlspecialchars($customer['email']) ?></td>
                                    <td><?= htmlspecialchars($customer['postna_stevilka']) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $customer['aktiven'] ? 'success' : 'danger' ?>">
                                            <?= $customer['aktiven'] ? 'Aktiven' : 'Neaktiven' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="action" value="toggle_status">
                                            <input type="hidden" name="customer_id" value="<?= $customer['id'] ?>">
                                            <input type="hidden" name="current_status" value="<?= $customer['aktiven'] ?>">
                                            <button type="submit" class="btn btn-sm btn-<?= $customer['aktiven'] ? 'warning' : 'success' ?>">
                                                <?= $customer['aktiven'] ? 'Deaktiviraj' : 'Aktiviraj' ?>
                                            </button>
                                        </form>
                                        <button type="button" class="btn btn-sm btn-primary" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editModal<?= $customer['id'] ?>">
                                            Uredi
                                        </button>
                                    </td>
                                </tr>

                                <!-- Edit Modal -->
                                <div class="modal fade" id="editModal<?= $customer['id'] ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Uredi Stranko</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <form method="POST">
                                                    <input type="hidden" name="action" value="edit">
                                                    <input type="hidden" name="customer_id" value="<?= $customer['id'] ?>">
                                                    
                                                    <div class="mb-3">
                                                        <label for="ime<?= $customer['id'] ?>" class="form-label">Ime*</label>
                                                        <input type="text" class="form-control" id="ime<?= $customer['id'] ?>" 
                                                               name="ime" value="<?= htmlspecialchars($customer['ime']) ?>" required>
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <label for="priimek<?= $customer['id'] ?>" class="form-label">Priimek*</label>
                                                        <input type="text" class="form-control" id="priimek<?= $customer['id'] ?>" 
                                                               name="priimek" value="<?= htmlspecialchars($customer['priimek']) ?>" required>
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <label for="email<?= $customer['id'] ?>" class="form-label">Email*</label>
                                                        <input type="email" class="form-control" id="email<?= $customer['id'] ?>" 
                                                               name="email" value="<?= htmlspecialchars($customer['email']) ?>" required>
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <label for="postna_stevilka<?= $customer['id'] ?>" class="form-label">Poštna Številka*</label>
                                                        <input type="text" class="form-control" id="postna_stevilka<?= $customer['id'] ?>" 
                                                               name="postna_stevilka" value="<?= htmlspecialchars($customer['postna_stevilka']) ?>" 
                                                               pattern="[0-9]{4}" title="Vnesite 4-mestno poštno številko" required>
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <label for="geslo<?= $customer['id'] ?>" class="form-label">Novo Geslo (pustite prazno če ne želite spremeniti)</label>
                                                        <input type="password" class="form-control" id="geslo<?= $customer['id'] ?>" name="geslo">
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

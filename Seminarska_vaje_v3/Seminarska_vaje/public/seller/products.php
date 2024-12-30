<?php
require_once __DIR__ . '/../../includes/check_seller_cert.php';
requireSeller();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add' || $action === 'edit') {
        $naziv = trim(htmlspecialchars($_POST['naziv'] ?? ''));
        $opis = trim(htmlspecialchars($_POST['opis'] ?? ''));
        $cena = filter_input(INPUT_POST, 'cena', FILTER_VALIDATE_FLOAT);
        $product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);

        if (empty($naziv) || $cena === false) {
            $error = 'Prosim izpolnite vsa obvezna polja.';
        } else {
            if ($action === 'add') {
                $stmt = $pdo->prepare("INSERT INTO artikel (naziv, opis, cena) VALUES (?, ?, ?)");
                $stmt->execute([$naziv, $opis, $cena]);
                $success = 'Artikel uspešno dodan.';
            } else {
                $stmt = $pdo->prepare("UPDATE artikel SET naziv = ?, opis = ?, cena = ? WHERE id = ?");
                $stmt->execute([$naziv, $opis, $cena, $product_id]);
                $success = 'Artikel uspešno posodobljen.';
            }
        }
    } elseif ($action === 'toggle_status') {
        $product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
        $current_status = filter_input(INPUT_POST, 'current_status', FILTER_VALIDATE_BOOLEAN);
        
        $stmt = $pdo->prepare("UPDATE artikel SET aktiven = ? WHERE id = ?");
        $stmt->execute([!$current_status, $product_id]);
        $success = 'Status artikla uspešno spremenjen.';
    }
}

// Get all products
$stmt = $pdo->query("SELECT * FROM artikel ORDER BY naziv");
$products = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upravljanje Artiklov - Prodajalec</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">Prodajalec Dashboard</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="profile.php">Moj Profil</a>
                <a class="nav-link" href="orders.php">Naročila</a>
                <a class="nav-link active" href="products.php">Artikli</a>
                <a class="nav-link" href="customers.php">Stranke</a>
                <a class="nav-link" href="../logout.php">Odjava</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>Upravljanje Artiklov</h2>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!-- Add New Product Form -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Dodaj Nov Artikel</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="add">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="naziv" class="form-label">Naziv*</label>
                                <input type="text" class="form-control" id="naziv" name="naziv" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="cena" class="form-label">Cena (€)*</label>
                                <input type="number" step="0.01" class="form-control" id="cena" name="cena" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="opis" class="form-label">Opis</label>
                                <textarea class="form-control" id="opis" name="opis" rows="1"></textarea>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Dodaj Artikel</button>
                </form>
            </div>
        </div>

        <!-- Products List -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Seznam Artiklov</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Naziv</th>
                                <th>Opis</th>
                                <th>Cena</th>
                                <th>Status</th>
                                <th>Akcije</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td><?= htmlspecialchars($product['naziv']) ?></td>
                                    <td><?= htmlspecialchars($product['opis'] ?? '') ?></td>
                                    <td><?= number_format($product['cena'], 2) ?> €</td>
                                    <td>
                                        <span class="badge bg-<?= $product['aktiven'] ? 'success' : 'danger' ?>">
                                            <?= $product['aktiven'] ? 'Aktiven' : 'Neaktiven' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="action" value="toggle_status">
                                            <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                            <input type="hidden" name="current_status" value="<?= $product['aktiven'] ?>">
                                            <button type="submit" class="btn btn-sm btn-<?= $product['aktiven'] ? 'warning' : 'success' ?>">
                                                <?= $product['aktiven'] ? 'Deaktiviraj' : 'Aktiviraj' ?>
                                            </button>
                                        </form>
                                        <button type="button" class="btn btn-sm btn-primary" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editModal<?= $product['id'] ?>">
                                            Uredi
                                        </button>
                                    </td>
                                </tr>

                                <!-- Edit Modal -->
                                <div class="modal fade" id="editModal<?= $product['id'] ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Uredi Artikel</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <form method="POST">
                                                    <input type="hidden" name="action" value="edit">
                                                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                                    
                                                    <div class="mb-3">
                                                        <label for="naziv<?= $product['id'] ?>" class="form-label">Naziv*</label>
                                                        <input type="text" class="form-control" id="naziv<?= $product['id'] ?>" 
                                                               name="naziv" value="<?= htmlspecialchars($product['naziv']) ?>" required>
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <label for="cena<?= $product['id'] ?>" class="form-label">Cena (€)*</label>
                                                        <input type="number" step="0.01" class="form-control" id="cena<?= $product['id'] ?>" 
                                                               name="cena" value="<?= $product['cena'] ?>" required>
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <label for="opis<?= $product['id'] ?>" class="form-label">Opis</label>
                                                        <textarea class="form-control" id="opis<?= $product['id'] ?>" 
                                                                  name="opis" rows="3"><?= htmlspecialchars($product['opis'] ?? '') ?></textarea>
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

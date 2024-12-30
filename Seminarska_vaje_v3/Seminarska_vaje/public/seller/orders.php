<?php
require_once __DIR__ . '/../../includes/check_seller_cert.php';
requireSeller();

$success = '';
$error = '';

// Handle order status changes
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $order_id = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
    $action = $_POST['action'] ?? '';
    
    if ($order_id) {
        try {
            $pdo->beginTransaction();
            
            // Get current order status
            $stmt = $pdo->prepare("SELECT status FROM narocilo WHERE id = ?");
            $stmt->execute([$order_id]);
            $current_status = $stmt->fetchColumn();
            
            $new_status = '';
            switch ($action) {
                case 'confirm':
                    if ($current_status === 'oddano') {
                        $new_status = 'potrjeno';
                    }
                    break;
                case 'cancel':
                    if ($current_status === 'oddano') {
                        $new_status = 'preklicano';
                    }
                    break;
                case 'revoke':
                    if ($current_status === 'potrjeno') {
                        $new_status = 'stornirano';
                    }
                    break;
            }
            
            if ($new_status) {
                $stmt = $pdo->prepare("UPDATE narocilo SET status = ?, prodajalec_id = ? WHERE id = ?");
                $stmt->execute([$new_status, $_SESSION['user_id'], $order_id]);
                $success = 'Status naročila uspešno posodobljen.';
            } else {
                $error = 'Neveljavna sprememba statusa.';
            }
            
            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'Prišlo je do napake pri posodobitvi naročila.';
        }
    }
}

// Get filter parameters
$status_filter = $_GET['status'] ?? 'oddano';
$valid_statuses = ['oddano', 'potrjeno', 'preklicano', 'stornirano'];
if (!in_array($status_filter, $valid_statuses)) {
    $status_filter = 'oddano';
}

// Get orders
$stmt = $pdo->prepare("
    SELECT n.*, s.ime as stranka_ime, s.priimek as stranka_priimek,
           p.ime as prodajalec_ime, p.priimek as prodajalec_priimek
    FROM narocilo n
    JOIN stranka s ON n.stranka_id = s.id
    LEFT JOIN prodajalec p ON n.prodajalec_id = p.id
    WHERE n.status = ?
    ORDER BY n.datum_oddaje DESC
");
$stmt->execute([$status_filter]);
$orders = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upravljanje Naročil - Prodajalec</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">Prodajalec Dashboard</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="profile.php">Moj Profil</a>
                <a class="nav-link active" href="orders.php">Naročila</a>
                <a class="nav-link" href="products.php">Artikli</a>
                <a class="nav-link" href="customers.php">Stranke</a>
                <a class="nav-link" href="../logout.php">Odjava</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>Upravljanje Naročil</h2>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!-- Status Filter -->
        <div class="btn-group mb-4" role="group">
            <a href="?status=oddano" class="btn btn-<?= $status_filter === 'oddano' ? 'primary' : 'outline-primary' ?>">Nova Naročila</a>
            <a href="?status=potrjeno" class="btn btn-<?= $status_filter === 'potrjeno' ? 'primary' : 'outline-primary' ?>">Potrjena Naročila</a>
            <a href="?status=preklicano" class="btn btn-<?= $status_filter === 'preklicano' ? 'primary' : 'outline-primary' ?>">Preklicana Naročila</a>
            <a href="?status=stornirano" class="btn btn-<?= $status_filter === 'stornirano' ? 'primary' : 'outline-primary' ?>">Stornirana Naročila</a>
        </div>

        <!-- Orders List -->
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID Naročila</th>
                        <th>Stranka</th>
                        <th>Datum Oddaje</th>
                        <th>Skupna Cena</th>
                        <th>Status</th>
                        <th>Akcije</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?= $order['id'] ?></td>
                            <td><?= htmlspecialchars($order['stranka_ime'] . ' ' . $order['stranka_priimek']) ?></td>
                            <td><?= $order['datum_oddaje'] ?></td>
                            <td><?= number_format($order['skupna_cena'], 2) ?> €</td>
                            <td>
                                <span class="badge bg-<?php
                                    switch($order['status']) {
                                        case 'oddano': echo 'warning'; break;
                                        case 'potrjeno': echo 'success'; break;
                                        case 'preklicano': echo 'danger'; break;
                                        case 'stornirano': echo 'secondary'; break;
                                    }
                                ?>">
                                    <?= ucfirst($order['status']) ?>
                                </span>
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-info" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#detailsModal<?= $order['id'] ?>">
                                    Podrobnosti
                                </button>
                                
                                <?php if ($order['status'] === 'oddano'): ?>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                        <input type="hidden" name="action" value="confirm">
                                        <button type="submit" class="btn btn-sm btn-success">Potrdi</button>
                                    </form>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                        <input type="hidden" name="action" value="cancel">
                                        <button type="submit" class="btn btn-sm btn-danger">Prekliči</button>
                                    </form>
                                <?php elseif ($order['status'] === 'potrjeno'): ?>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                        <input type="hidden" name="action" value="revoke">
                                        <button type="submit" class="btn btn-sm btn-warning">Storniraj</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>

                        <!-- Order Details Modal -->
                        <div class="modal fade" id="detailsModal<?= $order['id'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Podrobnosti Naročila #<?= $order['id'] ?></h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <?php
                                        // Get order items
                                        $stmt = $pdo->prepare("
                                            SELECT na.*, a.naziv
                                            FROM narocilo_artikel na
                                            JOIN artikel a ON na.artikel_id = a.id
                                            WHERE na.narocilo_id = ?
                                        ");
                                        $stmt->execute([$order['id']]);
                                        $items = $stmt->fetchAll();
                                        ?>
                                        
                                        <h6>Podatki o Stranki:</h6>
                                        <p>Ime in Priimek: <?= htmlspecialchars($order['stranka_ime'] . ' ' . $order['stranka_priimek']) ?></p>
                                        
                                        <h6 class="mt-4">Postavke Naročila:</h6>
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>Artikel</th>
                                                    <th>Količina</th>
                                                    <th>Cena na Kos</th>
                                                    <th>Skupaj</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($items as $item): ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars($item['naziv']) ?></td>
                                                        <td><?= $item['kolicina'] ?></td>
                                                        <td><?= number_format($item['cena_na_kos'], 2) ?> €</td>
                                                        <td><?= number_format($item['kolicina'] * $item['cena_na_kos'], 2) ?> €</td>
                                                    </tr>
                                                <?php endforeach; ?>
                                                <tr>
                                                    <td colspan="3" class="text-end"><strong>Skupaj:</strong></td>
                                                    <td><strong><?= number_format($order['skupna_cena'], 2) ?> €</strong></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        
                                        <h6 class="mt-4">Zgodovina Naročila:</h6>
                                        <p>Oddano: <?= $order['datum_oddaje'] ?></p>
                                        <?php if ($order['prodajalec_id']): ?>
                                            <p>Obdelal: <?= htmlspecialchars($order['prodajalec_ime'] . ' ' . $order['prodajalec_priimek']) ?></p>
                                            <p>Zadnja sprememba: <?= $order['datum_spremembe'] ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

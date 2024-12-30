<?php
require_once '../config.php';

// Check if seller is logged in
if (!isset($_SESSION['seller_id'])) {
    header('Location: login.php');
    exit;
}

// Handle order status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = $_POST['order_id'] ?? null;
    $action = $_POST['action'] ?? '';
    
    if ($order_id && $action) {
        switch ($action) {
            case 'confirm':
                $stmt = $pdo->prepare("UPDATE narocilo SET status = 'potrjeno' WHERE id = ? AND status = 'neobdelano'");
                break;
            case 'cancel':
                $stmt = $pdo->prepare("UPDATE narocilo SET status = 'preklicano' WHERE id = ? AND status = 'neobdelano'");
                break;
            case 'storno':
                $stmt = $pdo->prepare("UPDATE narocilo SET status = 'stornirano' WHERE id = ? AND status = 'potrjeno'");
                break;
        }
        
        if (isset($stmt)) {
            $stmt->execute([$order_id]);
            header('Location: narocila.php?message=status_updated');
            exit;
        }
    }
}

// Get orders based on filter
$status_filter = $_GET['status'] ?? 'neobdelano';
$stmt = $pdo->prepare("
    SELECT n.*, s.ime, s.priimek, s.email,
           SUM(p.kolicina * p.cena_na_enoto) as skupna_vrednost
    FROM narocilo n
    JOIN stranka s ON n.stranka_id = s.id
    LEFT JOIN postavka_narocila p ON n.id = p.narocilo_id
    WHERE n.status = ?
    GROUP BY n.id
    ORDER BY n.created_at DESC
");
$stmt->execute([$status_filter]);
$orders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upravljanje Naročil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">Prodajalec Panel</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Nadzorna Plošča</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="narocila.php">Naročila</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="artikli.php">Artikli</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="stranke.php">Stranke</a>
                    </li>
                </ul>
                <div class="navbar-nav">
                    <a class="nav-link" href="logout.php">Odjava</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php if (isset($_GET['message'])): ?>
            <div class="alert alert-success">
                Status naročila je bil uspešno posodobljen.
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h3>Naročila</h3>
                    <div class="btn-group">
                        <a href="?status=neobdelano" class="btn btn-<?php echo $status_filter === 'neobdelano' ? 'primary' : 'outline-primary'; ?>">Neobdelana</a>
                        <a href="?status=potrjeno" class="btn btn-<?php echo $status_filter === 'potrjeno' ? 'primary' : 'outline-primary'; ?>">Potrjena</a>
                        <a href="?status=preklicano" class="btn btn-<?php echo $status_filter === 'preklicano' ? 'primary' : 'outline-primary'; ?>">Preklicana</a>
                        <a href="?status=stornirano" class="btn btn-<?php echo $status_filter === 'stornirano' ? 'primary' : 'outline-primary'; ?>">Stornirana</a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Stranka</th>
                                <th>Email</th>
                                <th>Datum</th>
                                <th>Vrednost</th>
                                <th>Akcije</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td><?php echo $order['id']; ?></td>
                                    <td><?php echo htmlspecialchars($order['ime'] . ' ' . $order['priimek']); ?></td>
                                    <td><?php echo htmlspecialchars($order['email']); ?></td>
                                    <td><?php echo date('d.m.Y H:i', strtotime($order['created_at'])); ?></td>
                                    <td><?php echo number_format($order['skupna_vrednost'], 2, ',', '.'); ?> €</td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="narocilo_podrobnosti.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-info">Podrobnosti</a>
                                            
                                            <?php if ($order['status'] === 'neobdelano'): ?>
                                                <form method="POST" action="" style="display: inline;">
                                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                    <input type="hidden" name="action" value="confirm">
                                                    <button type="submit" class="btn btn-sm btn-success">Potrdi</button>
                                                </form>
                                                <form method="POST" action="" style="display: inline;">
                                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                    <input type="hidden" name="action" value="cancel">
                                                    <button type="submit" class="btn btn-sm btn-danger">Prekliči</button>
                                                </form>
                                            <?php elseif ($order['status'] === 'potrjeno'): ?>
                                                <form method="POST" action="" style="display: inline;">
                                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                    <input type="hidden" name="action" value="storno">
                                                    <button type="submit" class="btn btn-sm btn-warning">Storniraj</button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($orders)): ?>
                                <tr>
                                    <td colspan="6" class="text-center">Ni najdenih naročil s tem statusom.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

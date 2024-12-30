<?php
require_once '../config.php';

// Check if seller is logged in
if (!isset($_SESSION['seller_id'])) {
    header('Location: login.php');
    exit;
}

// Get order details
$order_id = $_GET['id'] ?? null;
if (!$order_id) {
    header('Location: narocila.php');
    exit;
}

// Get order information
$stmt = $pdo->prepare("
    SELECT n.*, s.ime, s.priimek, s.email, s.ulica, s.hisna_stevilka, s.posta, s.postna_stevilka
    FROM narocilo n
    JOIN stranka s ON n.stranka_id = s.id
    WHERE n.id = ?
");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order) {
    header('Location: narocila.php');
    exit;
}

// Get order items
$stmt = $pdo->prepare("
    SELECT p.*, a.naziv
    FROM postavka_narocila p
    JOIN artikel a ON p.artikel_id = a.id
    WHERE p.narocilo_id = ?
");
$stmt->execute([$order_id]);
$items = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Podrobnosti Naročila #<?php echo $order_id; ?></title>
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
        <div class="row">
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h3>Naročilo #<?php echo $order_id; ?></h3>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-sm-6">
                                <h6 class="mb-3">Podatki o stranki:</h6>
                                <div>
                                    <strong>Ime in priimek:</strong> <?php echo htmlspecialchars($order['ime'] . ' ' . $order['priimek']); ?><br>
                                    <strong>Email:</strong> <?php echo htmlspecialchars($order['email']); ?><br>
                                    <strong>Naslov:</strong><br>
                                    <?php echo htmlspecialchars($order['ulica'] . ' ' . $order['hisna_stevilka']); ?><br>
                                    <?php echo htmlspecialchars($order['postna_stevilka'] . ' ' . $order['posta']); ?>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <h6 class="mb-3">Podatki o naročilu:</h6>
                                <div>
                                    <strong>Status:</strong> <?php echo ucfirst($order['status']); ?><br>
                                    <strong>Datum naročila:</strong> <?php echo date('d.m.Y H:i', strtotime($order['created_at'])); ?><br>
                                    <strong>Zadnja sprememba:</strong> <?php echo date('d.m.Y H:i', strtotime($order['updated_at'])); ?>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Artikel</th>
                                        <th class="text-end">Cena na enoto</th>
                                        <th class="text-center">Količina</th>
                                        <th class="text-end">Skupaj</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $total = 0;
                                    foreach ($items as $item): 
                                        $subtotal = $item['kolicina'] * $item['cena_na_enoto'];
                                        $total += $subtotal;
                                    ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($item['naziv']); ?></td>
                                            <td class="text-end"><?php echo number_format($item['cena_na_enoto'], 2, ',', '.'); ?> €</td>
                                            <td class="text-center"><?php echo $item['kolicina']; ?></td>
                                            <td class="text-end"><?php echo number_format($subtotal, 2, ',', '.'); ?> €</td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3" class="text-end"><strong>Skupaj:</strong></td>
                                        <td class="text-end"><strong><?php echo number_format($total, 2, ',', '.'); ?> €</strong></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h3>Akcije</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($order['status'] === 'neobdelano'): ?>
                            <form method="POST" action="narocila.php" class="mb-2">
                                <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                                <input type="hidden" name="action" value="confirm">
                                <button type="submit" class="btn btn-success w-100">Potrdi naročilo</button>
                            </form>
                            <form method="POST" action="narocila.php">
                                <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                                <input type="hidden" name="action" value="cancel">
                                <button type="submit" class="btn btn-danger w-100">Prekliči naročilo</button>
                            </form>
                        <?php elseif ($order['status'] === 'potrjeno'): ?>
                            <form method="POST" action="narocila.php">
                                <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                                <input type="hidden" name="action" value="storno">
                                <button type="submit" class="btn btn-warning w-100">Storniraj naročilo</button>
                            </form>
                        <?php else: ?>
                            <div class="alert alert-info mb-0">
                                Naročilo je v statusu "<?php echo ucfirst($order['status']); ?>" in ga ni mogoče več spreminjati.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

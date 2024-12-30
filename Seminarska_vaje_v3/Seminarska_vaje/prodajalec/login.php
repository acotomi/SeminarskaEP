<?php
require_once '../config.php';

// Verify SSL client certificate
$client_cert_info = [];
if (!empty($_SERVER['SSL_CLIENT_CERT'])) {
    $cert = openssl_x509_parse($_SERVER['SSL_CLIENT_CERT']);
    if ($cert) {
        $client_cert_info = $cert;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';

    if ($email && $password) {
        $stmt = $pdo->prepare("SELECT * FROM prodajalec WHERE email = ? AND aktiven = 1");
        $stmt->execute([$email]);
        $seller = $stmt->fetch();

        if ($seller && password_verify($password, $seller['geslo'])) {
            $_SESSION['seller_id'] = $seller['id'];
            $_SESSION['seller_email'] = $seller['email'];
            header('Location: dashboard.php');
            exit;
        } else {
            $error = "Napačni podatki za prijavo ali račun ni aktiven.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prodajalec Prijava</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-center">Prodajalec Prijava</h3>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($client_cert_info)): ?>
                            <form method="POST" action="">
                                <div class="mb-3">
                                    <label for="email" class="form-label">E-poštni naslov</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                                <div class="mb-3">
                                    <label for="password" class="form-label">Geslo</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                                <?php if (isset($error)): ?>
                                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                                <?php endif; ?>
                                <button type="submit" class="btn btn-primary w-100">Prijava</button>
                            </form>
                        <?php else: ?>
                            <div class="alert alert-danger">
                                Za dostop potrebujete veljaven SSL certifikat.
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

<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/secure_redirect.php';

// Zahtevaj HTTPS za prijavo
requireSSL();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Log all POST data
    error_log("POST data: " . print_r($_POST, true));
    
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $geslo = $_POST['geslo'] ?? '';
    $tip_uporabnika = $_POST['tip_uporabnika'] ?? 'stranka';

    // Log sanitized inputs
    error_log("Sanitized email: " . $email);
    error_log("Password length: " . strlen($geslo));
    error_log("User type: " . $tip_uporabnika);

    if (empty($email) || empty($geslo)) {
        $error = 'Prosim izpolnite vsa polja.';
        error_log("Empty fields detected");
    } else {
        if ($tip_uporabnika === 'administrator') {
            error_log("Attempting admin login");
            
            try {
                $stmt = $pdo->prepare("SELECT * FROM administrator WHERE email = ?");
                $stmt->execute([$email]);
                $user = $stmt->fetch();
                
                error_log("SQL query executed successfully");
                error_log("User found: " . ($user ? 'Yes' : 'No'));
                
                if ($user) {
                    error_log("Admin user details: " . print_r($user, true));
                    error_log("Stored password hash: " . $user['geslo']);
                    error_log("Password verification result: " . (password_verify($geslo, $user['geslo']) ? 'True' : 'False'));
                    
                    if (password_verify($geslo, $user['geslo'])) {
                        error_log("Password verified successfully");
                        $_SESSION['user_type'] = 'administrator';
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['user_name'] = $user['ime'] . ' ' . $user['priimek'];
                        header('Location: ' . BASE_URL . '/admin/index.php');
                        exit;
                    } else {
                        error_log("Password verification failed");
                    }
                }
            } catch (PDOException $e) {
                error_log("Database error: " . $e->getMessage());
                $error = 'Prišlo je do napake pri povezavi z bazo.';
            }
        } else if ($tip_uporabnika === 'prodajalec') {
            error_log("Attempting seller login");
            
            try {
                $stmt = $pdo->prepare("SELECT * FROM prodajalec WHERE email = ?");
                $stmt->execute([$email]);
                $user = $stmt->fetch();
                
                error_log("SQL query executed successfully");
                error_log("User found: " . ($user ? 'Yes' : 'No'));
                
                if ($user) {
                    error_log("Seller user details: " . print_r($user, true));
                    error_log("Stored password hash: " . $user['geslo']);
                    error_log("Password verification result: " . (password_verify($geslo, $user['geslo']) ? 'True' : 'False'));
                    
                    if (password_verify($geslo, $user['geslo'])) {
                        error_log("Password verified successfully");
                        $_SESSION['user_type'] = 'prodajalec';
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['user_name'] = $user['ime'] . ' ' . $user['priimek'];
                        header('Location: ' . BASE_URL . '/seller/index.php');
                        exit;
                    } else {
                        error_log("Password verification failed");
                    }
                }
            } catch (PDOException $e) {
                error_log("Database error: " . $e->getMessage());
                $error = 'Prišlo je do napake pri povezavi z bazo.';
            }
        } else if ($tip_uporabnika === 'stranka') {
            error_log("Attempting customer login");
            
            try {
                $stmt = $pdo->prepare("SELECT * FROM stranka WHERE email = ?");
                $stmt->execute([$email]);
                $user = $stmt->fetch();
                
                error_log("SQL query executed successfully");
                error_log("User found: " . ($user ? 'Yes' : 'No'));
                
                if ($user) {
                    error_log("Customer user details: " . print_r($user, true));
                    error_log("Stored password hash: " . $user['geslo']);
                    error_log("Password verification result: " . (password_verify($geslo, $user['geslo']) ? 'True' : 'False'));
                    
                    if (password_verify($geslo, $user['geslo'])) {
                        error_log("Password verified successfully");
                        $_SESSION['user_type'] = 'stranka';
                        $_SESSION['stranka_id'] = $user['id'];
                        $_SESSION['user_name'] = $user['ime'] . ' ' . $user['priimek'];
                        header('Location: ' . BASE_URL . '/index.php');
                        exit;
                    } else {
                        error_log("Password verification failed");
                    }
                }
            } catch (PDOException $e) {
                error_log("Database error: " . $e->getMessage());
                $error = 'Prišlo je do napake pri povezavi z bazo.';
            }
        }
        
        $error = 'Napačen email ali geslo.';
        error_log("Authentication failed - invalid credentials");
    }
}
?>

<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prijava - Spletna Prodajalna</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="<?= BASE_URL ?>/index.php">Spletna Prodajalna</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link active" href="<?= BASE_URL ?>/login.php">Prijava</a>
                <a class="nav-link" href="<?= BASE_URL ?>/registracija.php">Registracija</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">Prijava</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Tip uporabnika</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="tip_uporabnika" id="stranka" value="stranka" checked>
                                    <label class="form-check-label" for="stranka">
                                        Stranka
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="tip_uporabnika" id="prodajalec" value="prodajalec">
                                    <label class="form-check-label" for="prodajalec">
                                        Prodajalec
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="tip_uporabnika" id="administrator" value="administrator">
                                    <label class="form-check-label" for="administrator">
                                        Administrator
                                    </label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email naslov</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>

                            <div class="mb-3">
                                <label for="geslo" class="form-label">Geslo</label>
                                <input type="password" class="form-control" id="geslo" name="geslo" required>
                            </div>

                            <button type="submit" class="btn btn-primary">Prijava</button>
                        </form>

                        <div class="mt-3">
                            <p>Še nimate računa? <a href="<?= BASE_URL ?>/registracija.php">Registrirajte se</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

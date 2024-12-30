<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/secure_redirect.php';

// Javna stran naj bo dostopna preko HTTP
requireNoSSL();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Pridobi vse aktivne izdelke
$stmt = $pdo->prepare("SELECT id, naziv, opis, cena FROM artikel WHERE aktiven = TRUE ORDER BY naziv");
$stmt->execute();
$izdelki = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Spletna Prodajalna</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">Spletna Prodajalna</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if (isset($_SESSION['stranka_id'])): ?>
                        <li class="nav-item">
                            <span class="nav-link">Pozdravljen/a, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../stranka/narocila.php">Moja Naročila</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../stranka/profil.php">Moj Profil</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="kosarica.php">Košarica</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="odjava.php">Odjava</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Prijava</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="registracija.php">Registracija</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>Naši izdelki</h2>
        <div class="row">
            <?php foreach ($izdelki as $izdelek): ?>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($izdelek['naziv']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars($izdelek['opis']); ?></p>
                            <p class="card-text"><strong>Cena: <?php echo number_format($izdelek['cena'], 2); ?> €</strong></p>
                            <?php if (isset($_SESSION['stranka_id'])): ?>
                                <form class="add-to-cart-form" action="dodaj_v_kosarico.php" method="POST">
                                    <input type="hidden" name="artikel_id" value="<?php echo $izdelek['id']; ?>">
                                    <div class="input-group mb-3">
                                        <input type="number" name="kolicina" class="form-control" value="1" min="1" max="99">
                                        <button type="submit" class="btn btn-primary">Dodaj v košarico</button>
                                    </div>
                                    <div class="alert alert-danger d-none" role="alert"></div>
                                </form>
                            <?php else: ?>
                                <a href="login.php" class="btn btn-primary">Prijava za nakup</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const forms = document.querySelectorAll('.add-to-cart-form');
        
        forms.forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const errorDiv = form.querySelector('.alert-danger');
                const submitBtn = form.querySelector('button[type="submit"]');
                
                // Disable the submit button
                submitBtn.disabled = true;
                
                fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        errorDiv.textContent = data.error;
                        errorDiv.classList.remove('d-none');
                    } else {
                        // Success - hide any previous error
                        errorDiv.classList.add('d-none');
                        
                        // Show success message
                        const successDiv = document.createElement('div');
                        successDiv.className = 'alert alert-success';
                        successDiv.textContent = data.message;
                        form.appendChild(successDiv);
                        
                        // Remove success message after 3 seconds
                        setTimeout(() => {
                            successDiv.remove();
                        }, 3000);
                    }
                })
                .catch(error => {
                    errorDiv.textContent = 'Prišlo je do napake. Prosimo, poskusite ponovno.';
                    errorDiv.classList.remove('d-none');
                })
                .finally(() => {
                    // Re-enable the submit button
                    submitBtn.disabled = false;
                });
            });
        });
    });
    </script>
</body>
</html>

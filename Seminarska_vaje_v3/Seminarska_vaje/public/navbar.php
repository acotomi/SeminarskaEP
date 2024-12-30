<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="index.php">Spletna Prodajalna</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">Domov</a>
                </li>
                <?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'stranka'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="kosarica.php">Košarica</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="narocila.php">Moja naročila</a>
                    </li>
                <?php endif; ?>
            </ul>
            <ul class="navbar-nav">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item">
                        <span class="nav-link">
                            Pozdravljeni, <?= htmlspecialchars($_SESSION['ime'] ?? '') ?>
                        </span>
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

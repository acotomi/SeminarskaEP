<?php
require_once __DIR__ . '/../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Preveri, če je uporabnik prijavljen
if (!isset($_SESSION['stranka_id'])) {
    header('Location: login.php');
    exit;
}

$success = '';
$error = '';

// Obdelaj akcije
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update') {
        $artikel_id = $_POST['artikel_id'] ?? 0;
        $kolicina = (int)($_POST['kolicina'] ?? 0);

        if ($kolicina > 0) {
            try {
                $stmt = $pdo->prepare("UPDATE kosarica SET kolicina = ? WHERE stranka_id = ? AND artikel_id = ?");
                $stmt->execute([$kolicina, $_SESSION['stranka_id'], $artikel_id]);
                $success = 'Količina je bila posodobljena.';
            } catch (PDOException $e) {
                $error = 'Napaka pri posodabljanju količine.';
            }
        } else {
            try {
                $stmt = $pdo->prepare("DELETE FROM kosarica WHERE stranka_id = ? AND artikel_id = ?");
                $stmt->execute([$_SESSION['stranka_id'], $artikel_id]);
                $success = 'Artikel je bil odstranjen iz košarice.';
            } catch (PDOException $e) {
                $error = 'Napaka pri odstranjevanju artikla.';
            }
        }
    } elseif ($action === 'checkout') {
        try {
            // Začni transakcijo
            $pdo->beginTransaction();

            // Pridobi artikle v košarici
            $stmt = $pdo->prepare("
                SELECT k.artikel_id, k.kolicina, a.cena, a.naziv
                FROM kosarica k
                JOIN artikel a ON k.artikel_id = a.id
                WHERE k.stranka_id = ?
            ");
            $stmt->execute([$_SESSION['stranka_id']]);
            $artikli = $stmt->fetchAll();

            if (empty($artikli)) {
                throw new Exception('Košarica je prazna.');
            }

            // Izračunaj skupno ceno
            $skupna_cena = 0;
            foreach ($artikli as $artikel) {
                $skupna_cena += $artikel['cena'] * $artikel['kolicina'];
            }

            // Ustvari novo naročilo - prilagojeno obstoječi strukturi tabele
            $stmt = $pdo->prepare("
                INSERT INTO narocilo (stranka_id, status)
                VALUES (?, 'neobdelano')
            ");
            $stmt->execute([$_SESSION['stranka_id']]);
            $narocilo_id = $pdo->lastInsertId();

            // Dodaj artikle v naročilo
            $stmt = $pdo->prepare("
                INSERT INTO narocilo_artikel (narocilo_id, artikel_id, kolicina, cena_na_kos)
                VALUES (?, ?, ?, ?)
            ");
            foreach ($artikli as $artikel) {
                $stmt->execute([
                    $narocilo_id,
                    $artikel['artikel_id'],
                    $artikel['kolicina'],
                    $artikel['cena']
                ]);
            }

            // Izprazni košarico
            $stmt = $pdo->prepare("DELETE FROM kosarica WHERE stranka_id = ?");
            $stmt->execute([$_SESSION['stranka_id']]);

            // Potrdi transakcijo
            $pdo->commit();
            
            header('Location: stranka/narocila.php');
            exit;

        } catch (Exception $e) {
            $pdo->rollBack();
            $error = $e->getMessage();
        }
    }
}

// Pridobi izdelke v košarici
try {
    $stmt = $pdo->prepare("
        SELECT k.id as kosarica_id, k.kolicina, a.id as artikel_id, a.naziv, a.cena
        FROM kosarica k
        JOIN artikel a ON k.artikel_id = a.id
        WHERE k.stranka_id = ?
    ");
    $stmt->execute([$_SESSION['stranka_id']]);
    $izdelki = $stmt->fetchAll();
} catch (PDOException $e) {
    $izdelki = [];
}
?>

<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Košarica - Spletna Prodajalna</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        // Funkcija za posodobitev cene
        function posodobiCeno(input) {
            const vrstica = input.closest('tr');
            const kolicina = parseInt(input.value);
            const cena = parseFloat(vrstica.querySelector('.cena-na-kos').dataset.cena);
            const skupajCena = kolicina * cena;
            
            // Posodobi ceno v vrstici
            const skupajElement = vrstica.querySelector('.skupaj-cena');
            skupajElement.textContent = skupajCena.toFixed(2) + ' €';
            skupajElement.dataset.skupaj = skupajCena;

            // Posodobi skupno ceno
            const vseSkupajCene = document.querySelectorAll('.skupaj-cena');
            let skupnaCena = 0;
            vseSkupajCene.forEach(el => {
                skupnaCena += parseFloat(el.dataset.skupaj || 0);
            });

            const skupnaCenaElement = document.querySelector('.skupna-cena-kosarice');
            if (skupnaCenaElement) {
                skupnaCenaElement.textContent = skupnaCena.toFixed(2) + ' €';
                skupnaCenaElement.dataset.skupna = skupnaCena;
            }

            // Pošlji spremembo na strežnik
            fetch('../api/kosarica_api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'update',
                    kosarica_id: input.dataset.kosaricaId,
                    kolicina: kolicina
                })
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    alert(data.error || 'Napaka pri posodabljanju količine.');
                    location.reload();
                }
            })
            .catch(error => {
                console.error('Napaka:', error);
                alert('Prišlo je do napake pri posodabljanju količine.');
                location.reload();
            });
        }

        // Funkcija za odstranitev izdelka
        function odstraniIzdelek(button) {
            if (confirm('Ali ste prepričani, da želite odstraniti ta izdelek iz košarice?')) {
                const kosaricaId = button.dataset.kosaricaId;
                
                fetch('../api/kosarica_api.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'remove',
                        kosarica_id: kosaricaId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.error || 'Napaka pri odstranjevanju izdelka.');
                    }
                })
                .catch(error => {
                    console.error('Napaka:', error);
                    alert('Prišlo je do napake pri odstranjevanju izdelka.');
                });
            }
        }

        // Funkcija za oddajo naročila
        function oddajNarocilo() {
            if (confirm('Ali ste prepričani, da želite oddati naročilo?')) {
                fetch('../api/kosarica_api.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'checkout'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Naročilo je bilo uspešno oddano!');
                        location.reload();
                    } else {
                        alert(data.error || 'Napaka pri oddaji naročila.');
                    }
                })
                .catch(error => {
                    console.error('Napaka:', error);
                    alert('Prišlo je do napake pri oddaji naročila.');
                });
            }
        }
    </script>
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
                    <li class="nav-item">
                        <a class="nav-link active" href="kosarica.php">Košarica</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="odjava.php">Odjava</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2 class="mb-4">Vaša košarica</h2>
        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if (empty($izdelki)): ?>
            <div class="alert alert-info">
                Vaša košarica je prazna. <a href="index.php" class="alert-link">Nadaljujte z nakupovanjem</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Izdelek</th>
                            <th>Količina</th>
                            <th>Cena na kos</th>
                            <th>Skupaj</th>
                            <th>Akcije</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $skupna_cena = 0;
                        foreach ($izdelki as $izdelek): 
                            $cena_skupaj = $izdelek['cena'] * $izdelek['kolicina'];
                            $skupna_cena += $cena_skupaj;
                        ?>
                            <tr>
                                <td><?= htmlspecialchars($izdelek['naziv']) ?></td>
                                <td>
                                    <input type="number" class="form-control" 
                                           value="<?= $izdelek['kolicina'] ?>" min="1"
                                           data-kosarica-id="<?= $izdelek['kosarica_id'] ?>"
                                           style="width: 100px"
                                           onchange="posodobiCeno(this)">
                                </td>
                                <td class="cena-na-kos" data-cena="<?= $izdelek['cena'] ?>">
                                    <?= number_format($izdelek['cena'], 2) ?> €
                                </td>
                                <td>
                                    <span class="skupaj-cena" data-skupaj="<?= $cena_skupaj ?>">
                                        <?= number_format($cena_skupaj, 2) ?> €
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-danger" 
                                            data-kosarica-id="<?= $izdelek['kosarica_id'] ?>"
                                            onclick="odstraniIzdelek(this)">
                                        Odstrani
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-end"><strong>Skupaj:</strong></td>
                            <td>
                                <strong class="skupna-cena-kosarice" data-skupna="<?= $skupna_cena ?>">
                                    <?= number_format($skupna_cena, 2) ?> €
                                </strong>
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="mt-3">
                <button class="btn btn-success" onclick="oddajNarocilo()">Oddaj naročilo</button>
                <a href="index.php" class="btn btn-primary">Nadaljuj z nakupovanjem</a>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

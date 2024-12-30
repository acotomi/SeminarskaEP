<?php
require_once __DIR__ . '/../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Preveri, če je uporabnik prijavljen
if (!isset($_SESSION['stranka_id'])) {
    echo json_encode(['success' => false, 'error' => 'Uporabnik ni prijavljen']);
    exit;
}

// Preberi JSON podatke iz zahtevka
$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

try {
    switch ($action) {
        case 'add':
            $artikel_id = $input['artikel_id'] ?? 0;
            $kolicina = $input['kolicina'] ?? 1;

            // Preveri, če artikel obstaja v košarici
            $stmt = $pdo->prepare("SELECT id, kolicina FROM kosarica WHERE stranka_id = ? AND artikel_id = ?");
            $stmt->execute([$_SESSION['stranka_id'], $artikel_id]);
            $obstojeciIzdelek = $stmt->fetch();

            if ($obstojeciIzdelek) {
                // Posodobi količino
                $stmt = $pdo->prepare("UPDATE kosarica SET kolicina = kolicina + ? WHERE id = ?");
                $stmt->execute([$kolicina, $obstojeciIzdelek['id']]);
            } else {
                // Dodaj nov izdelek
                $stmt = $pdo->prepare("INSERT INTO kosarica (stranka_id, artikel_id, kolicina) VALUES (?, ?, ?)");
                $stmt->execute([$_SESSION['stranka_id'], $artikel_id, $kolicina]);
            }

            echo json_encode(['success' => true]);
            break;

        case 'update':
            $kosarica_id = $input['kosarica_id'] ?? 0;
            $kolicina = $input['kolicina'] ?? 1;

            // Preveri, če je izdelek v košarici tega uporabnika
            $stmt = $pdo->prepare("SELECT id FROM kosarica WHERE id = ? AND stranka_id = ?");
            $stmt->execute([$kosarica_id, $_SESSION['stranka_id']]);
            if (!$stmt->fetch()) {
                throw new Exception('Nedovoljen dostop do košarice');
            }

            if ($kolicina <= 0) {
                // Če je količina 0 ali manj, odstrani izdelek
                $stmt = $pdo->prepare("DELETE FROM kosarica WHERE id = ?");
                $stmt->execute([$kosarica_id]);
            } else {
                // Posodobi količino
                $stmt = $pdo->prepare("UPDATE kosarica SET kolicina = ? WHERE id = ?");
                $stmt->execute([$kolicina, $kosarica_id]);
            }

            echo json_encode(['success' => true]);
            break;

        case 'remove':
            $kosarica_id = $input['kosarica_id'] ?? 0;

            // Preveri, če je izdelek v košarici tega uporabnika
            $stmt = $pdo->prepare("SELECT id FROM kosarica WHERE id = ? AND stranka_id = ?");
            $stmt->execute([$kosarica_id, $_SESSION['stranka_id']]);
            if (!$stmt->fetch()) {
                throw new Exception('Nedovoljen dostop do košarice');
            }

            // Odstrani izdelek
            $stmt = $pdo->prepare("DELETE FROM kosarica WHERE id = ?");
            $stmt->execute([$kosarica_id]);

            echo json_encode(['success' => true]);
            break;

        case 'checkout':
            $pdo->beginTransaction();

            try {
                // Pridobi vse izdelke iz košarice
                $stmt = $pdo->prepare("
                    SELECT k.artikel_id, k.kolicina, a.cena
                    FROM kosarica k
                    JOIN artikel a ON k.artikel_id = a.id
                    WHERE k.stranka_id = ?
                ");
                $stmt->execute([$_SESSION['stranka_id']]);
                $izdelki = $stmt->fetchAll();

                if (empty($izdelki)) {
                    throw new Exception('Košarica je prazna');
                }

                // Izračunaj skupno ceno
                $skupnaCena = 0;
                foreach ($izdelki as $izdelek) {
                    $skupnaCena += $izdelek['kolicina'] * $izdelek['cena'];
                }

                // Ustvari novo naročilo
                $stmt = $pdo->prepare("
                    INSERT INTO narocilo (stranka_id, status, skupna_cena)
                    VALUES (?, 'oddano', ?)
                ");
                $stmt->execute([$_SESSION['stranka_id'], $skupnaCena]);
                $narocilo_id = $pdo->lastInsertId();

                // Dodaj izdelke v naročilo
                $stmt = $pdo->prepare("
                    INSERT INTO narocilo_artikel (narocilo_id, artikel_id, kolicina, cena_na_kos)
                    VALUES (?, ?, ?, ?)
                ");
                foreach ($izdelki as $izdelek) {
                    $stmt->execute([
                        $narocilo_id,
                        $izdelek['artikel_id'],
                        $izdelek['kolicina'],
                        $izdelek['cena']
                    ]);
                }

                // Izprazni košarico
                $stmt = $pdo->prepare("DELETE FROM kosarica WHERE stranka_id = ?");
                $stmt->execute([$_SESSION['stranka_id']]);

                $pdo->commit();
                echo json_encode(['success' => true]);
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }
            break;

        default:
            throw new Exception('Neveljavna akcija');
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

<?php
require_once __DIR__ . '/../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Preveri, če je uporabnik prijavljen
if (!isset($_SESSION['stranka_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Prosimo, prijavite se za dodajanje v košarico.']);
    exit;
}

// Preveri, če je zahteva POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Napačna metoda zahteve.']);
    exit;
}

// Pridobi podatke
$artikel_id = filter_input(INPUT_POST, 'artikel_id', FILTER_VALIDATE_INT);
$kolicina = filter_input(INPUT_POST, 'kolicina', FILTER_VALIDATE_INT) ?? 1;

if (!$artikel_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Neveljaven artikel.']);
    exit;
}

if ($kolicina < 1) {
    http_response_code(400);
    echo json_encode(['error' => 'Neveljavna količina.']);
    exit;
}

try {
    // Preveri, če artikel obstaja in je aktiven
    $stmt = $pdo->prepare("SELECT id FROM artikel WHERE id = ? AND aktiven = TRUE");
    $stmt->execute([$artikel_id]);
    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode(['error' => 'Artikel ne obstaja ali ni na voljo.']);
        exit;
    }

    // Preveri, če je artikel že v košarici
    $stmt = $pdo->prepare("SELECT kolicina FROM kosarica WHERE stranka_id = ? AND artikel_id = ?");
    $stmt->execute([$_SESSION['stranka_id'], $artikel_id]);
    $existing = $stmt->fetch();

    if ($existing) {
        // Posodobi količino
        $stmt = $pdo->prepare("UPDATE kosarica SET kolicina = kolicina + ? WHERE stranka_id = ? AND artikel_id = ?");
        $stmt->execute([$kolicina, $_SESSION['stranka_id'], $artikel_id]);
    } else {
        // Dodaj nov zapis
        $stmt = $pdo->prepare("INSERT INTO kosarica (stranka_id, artikel_id, kolicina) VALUES (?, ?, ?)");
        $stmt->execute([$_SESSION['stranka_id'], $artikel_id, $kolicina]);
    }

    // Pridobi novo skupno količino v košarici
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM kosarica WHERE stranka_id = ?");
    $stmt->execute([$_SESSION['stranka_id']]);
    $total = $stmt->fetch()['total'];

    echo json_encode([
        'success' => true,
        'message' => 'Artikel je bil dodan v košarico.',
        'total_items' => $total
    ]);

} catch (PDOException $e) {
    error_log("Napaka pri dodajanju v košarico: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Prišlo je do napake pri dodajanju v košarico.']);
}

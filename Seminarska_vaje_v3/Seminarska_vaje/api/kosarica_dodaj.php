<?php
header('Content-Type: application/json');
require_once '../config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$response = ['success' => false];

// Preveri, če je uporabnik prijavljen
if (!isset($_SESSION['stranka_id'])) {
    $response['error'] = 'Prosim, prijavite se za dodajanje v košarico.';
    echo json_encode($response);
    exit;
}

// Preveri, če so podatki poslani
if (!isset($_POST['artikel_id']) || !isset($_POST['kolicina'])) {
    $response['error'] = 'Manjkajoči podatki.';
    echo json_encode($response);
    exit;
}

$artikel_id = (int)$_POST['artikel_id'];
$kolicina = (int)$_POST['kolicina'];
$stranka_id = $_SESSION['stranka_id'];

try {
    // Preveri, če artikel obstaja in je aktiven
    $stmt = $pdo->prepare("SELECT id FROM artikel WHERE id = ? AND aktiven = TRUE");
    $stmt->execute([$artikel_id]);
    if (!$stmt->fetch()) {
        $response['error'] = 'Artikel ne obstaja ali ni na voljo.';
        echo json_encode($response);
        exit;
    }

    // Preveri, če je artikel že v košarici
    $stmt = $pdo->prepare("SELECT kolicina FROM kosarica WHERE stranka_id = ? AND artikel_id = ?");
    $stmt->execute([$stranka_id, $artikel_id]);
    $existing = $stmt->fetch();

    if ($existing) {
        // Posodobi količino
        $stmt = $pdo->prepare("UPDATE kosarica SET kolicina = kolicina + ? WHERE stranka_id = ? AND artikel_id = ?");
        $stmt->execute([$kolicina, $stranka_id, $artikel_id]);
    } else {
        // Dodaj nov zapis
        $stmt = $pdo->prepare("INSERT INTO kosarica (stranka_id, artikel_id, kolicina) VALUES (?, ?, ?)");
        $stmt->execute([$stranka_id, $artikel_id, $kolicina]);
    }

    $response['success'] = true;

} catch (PDOException $e) {
    $response['error'] = 'Napaka pri dodajanju v košarico.';
    $response['debug'] = $e->getMessage(); // Samo za razvoj
}

echo json_encode($response);

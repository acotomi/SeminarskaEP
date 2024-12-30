<?php
require_once '../config.php';
require_once '../includes/auth.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

if (!isset($_SESSION['stranka_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Prosim, prijavite se']);
    exit;
}

$kosarica_id = $_POST['kosarica_id'] ?? null;

if (!$kosarica_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Manjka ID košarice']);
    exit;
}

try {
    // Preveri, če je izdelek v košarici od prijavljene stranke
    $stmt = $pdo->prepare("SELECT id FROM kosarica WHERE id = ? AND stranka_id = ?");
    $stmt->execute([$kosarica_id, $_SESSION['stranka_id']]);
    if (!$stmt->fetch()) {
        http_response_code(403);
        echo json_encode(['error' => 'Nimate dostopa do tega izdelka']);
        exit;
    }

    // Odstrani izdelek iz košarice
    $stmt = $pdo->prepare("DELETE FROM kosarica WHERE id = ?");
    $stmt->execute([$kosarica_id]);

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Napaka pri odstranjevanju izdelka']);
}

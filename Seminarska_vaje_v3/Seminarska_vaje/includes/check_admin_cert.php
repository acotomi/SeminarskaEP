<?php
require_once __DIR__ . '/../config/config.php';

function checkAdminCertificate() {
    if (!isset($_SERVER['SSL_CLIENT_VERIFY']) || $_SERVER['SSL_CLIENT_VERIFY'] !== 'SUCCESS') {
        header('HTTP/1.1 403 Forbidden');
        die('Neveljaven certifikat');
    }

    $certSubjectDN = $_SERVER['SSL_CLIENT_S_DN'];

    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM administrator WHERE cert_subject_dn = ? AND aktiven = TRUE");
    $stmt->execute([$certSubjectDN]);
    $admin = $stmt->fetch();

    if (!$admin) {
        header('HTTP/1.1 403 Forbidden');
        die('Nepooblaščen administratorski certifikat');
    }

    return $admin;
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in as admin
function requireAdmin() {
    if (!isset($_SESSION['admin_id'])) {
        header('Location: /admin/login.php');
        exit();
    }
    
    // Preveri certifikat
    $admin = checkAdminCertificate();
    if ($admin['id'] !== $_SESSION['admin_id']) {
        session_destroy();
        header('Location: /admin/login.php');
        exit();
    }
    
    return true;
}

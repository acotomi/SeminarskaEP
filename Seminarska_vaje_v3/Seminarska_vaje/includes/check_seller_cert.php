<?php
require_once __DIR__ . '/../config/config.php';

function checkSellerCertificate() {
    /* Certificate check temporarily disabled for testing
    if (!isset($_SERVER['SSL_CLIENT_VERIFY']) || $_SERVER['SSL_CLIENT_VERIFY'] !== 'SUCCESS') {
        header('HTTP/1.1 403 Forbidden');
        die('Invalid certificate');
    }

    $certSubject = $_SERVER['SSL_CLIENT_S_DN'];
    $certIssuer = $_SERVER['SSL_CLIENT_I_DN'];
    $certSerial = $_SERVER['SSL_CLIENT_M_SERIAL'];

    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM prodajalec WHERE 
        certificate_subject = ? AND 
        certificate_issuer = ? AND 
        certificate_serial = ? AND 
        aktiven = TRUE");
    
    $stmt->execute([$certSubject, $certIssuer, $certSerial]);
    $seller = $stmt->fetch();

    if (!$seller) {
        header('HTTP/1.1 403 Forbidden');
        die('Unauthorized seller certificate');
    }

    return $seller;
    */
    return true;
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in as seller
function requireSeller() {
    if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'prodajalec') {
        header('Location: /login.php');
        exit();
    }
    return true;
}

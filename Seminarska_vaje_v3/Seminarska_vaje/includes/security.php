<?php

/**
 * Varno filtriranje vhodnih podatkov
 */
function clean_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Preveri veljavnost email naslova
 */
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Preveri veljavnost poštne številke (4 številke)
 */
function validate_postal_code($code) {
    return preg_match('/^[0-9]{4}$/', $code);
}

/**
 * Generira CSRF žeton
 */
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Preveri CSRF žeton
 */
function verify_csrf_token($token) {
    if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        http_response_code(403);
        die('Neveljavna seja. Prosimo, poskusite ponovno.');
    }
}

/**
 * Varno zgradi SQL poizvedbo z uporabo PDO
 */
function build_query($pdo, $sql, $params = []) {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

/**
 * Preveri certifikat uporabnika
 */
function verify_certificate() {
    if (empty($_SERVER['SSL_CLIENT_CERT'])) {
        return false;
    }
    
    $cert = openssl_x509_parse($_SERVER['SSL_CLIENT_CERT']);
    if (!$cert) {
        return false;
    }
    
    // Preveri veljavnost certifikata
    $current_time = time();
    if ($current_time < $cert['validFrom_time_t'] || $current_time > $cert['validTo_time_t']) {
        return false;
    }
    
    return $cert;
}

/**
 * Preveri pravice uporabnika
 */
function check_user_role($required_role) {
    $cert = verify_certificate();
    if (!$cert) {
        return false;
    }
    
    $cn = $cert['subject']['CN'] ?? '';
    switch ($required_role) {
        case 'admin':
            return $cn === 'Admin';
        case 'seller':
            return $cn === 'Seller';
        default:
            return false;
    }
}

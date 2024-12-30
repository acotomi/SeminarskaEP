<?php
function checkUserRole() {
    if (isset($_SESSION['admin_id'])) {
        return 'admin';
    } elseif (isset($_SESSION['prodajalec_id'])) {
        return 'prodajalec';
    } elseif (isset($_SESSION['stranka_id'])) {
        return 'stranka';
    } else {
        return 'guest';
    }
}

function requireLogin() {
    if (!isset($_SESSION['stranka_id'])) {
        header('Location: /Seminarska_vaje/login.php');
        exit;
    }
}

function loginStranka($user) {
    $_SESSION['stranka_id'] = $user['id'];
    $_SESSION['stranka_name'] = $user['ime'] . ' ' . $user['priimek'];
}

function loginProdajalec($user) {
    $_SESSION['prodajalec_id'] = $user['id'];
    $_SESSION['prodajalec_name'] = $user['ime'] . ' ' . $user['priimek'];
}

function loginAdmin($user) {
    $_SESSION['admin_id'] = $user['id'];
    $_SESSION['admin_name'] = $user['ime'] . ' ' . $user['priimek'];
}

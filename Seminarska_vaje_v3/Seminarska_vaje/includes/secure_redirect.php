<?php
function requireSSL() {
    if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
        $redirectURL = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        header("Location: " . $redirectURL);
        exit();
    }
}

function requireNoSSL() {
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        $redirectURL = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        header("Location: " . $redirectURL);
        exit();
    }
}

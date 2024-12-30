<?php
require_once '../config/config.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => '', 'errors' => []];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Preveri reCAPTCHA
    $recaptcha_secret = "YOUR_SECRET_KEY";
    $recaptcha_response = $_POST['g-recaptcha-response'] ?? '';
    
    $verify_response = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=' . $recaptcha_secret . '&response=' . $recaptcha_response);
    $response_data = json_decode($verify_response);
    
    if (!$response_data->success) {
        $response['errors']['recaptcha'] = 'Prosimo, potrdite da niste robot.';
        echo json_encode($response);
        exit;
    }

    $ime = trim(htmlspecialchars($_POST['ime'] ?? ''));
    $priimek = trim(htmlspecialchars($_POST['priimek'] ?? ''));
    $email = trim($_POST['email'] ?? '');
    $geslo = $_POST['geslo'] ?? '';
    $potrdi_geslo = $_POST['potrdi_geslo'] ?? '';
    $tip_uporabnika = $_POST['tip_uporabnika'] ?? 'stranka';
    
    // Dodatna polja za stranko
    $ulica = trim(htmlspecialchars($_POST['ulica'] ?? ''));
    $hisna_stevilka = trim(htmlspecialchars($_POST['hisna_stevilka'] ?? ''));
    $posta = trim(htmlspecialchars($_POST['posta'] ?? ''));
    $postna_stevilka = trim($_POST['postna_stevilka'] ?? '');

    // Validacija
    if (empty($ime)) {
        $response['errors']['ime'] = 'Ime je obvezno.';
    }
    if (empty($priimek)) {
        $response['errors']['priimek'] = 'Priimek je obvezno.';
    }
    if (empty($email)) {
        $response['errors']['email'] = 'E-poštni naslov je obvezen.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['errors']['email'] = 'Vnesite veljaven e-poštni naslov.';
    }
    if (empty($geslo)) {
        $response['errors']['geslo'] = 'Geslo je obvezno.';
    } elseif (strlen($geslo) < 6) {
        $response['errors']['geslo'] = 'Geslo mora biti dolgo vsaj 6 znakov.';
    }
    if ($geslo !== $potrdi_geslo) {
        $response['errors']['potrdi_geslo'] = 'Gesli se ne ujemata.';
    }

    // Dodatna validacija za stranko
    if ($tip_uporabnika === 'stranka') {
        if (empty($ulica)) {
            $response['errors']['ulica'] = 'Ulica je obvezna.';
        }
        if (empty($hisna_stevilka)) {
            $response['errors']['hisna_stevilka'] = 'Hišna številka je obvezna.';
        }
        if (empty($posta)) {
            $response['errors']['posta'] = 'Pošta je obvezna.';
        }
        if (empty($postna_stevilka)) {
            $response['errors']['postna_stevilka'] = 'Poštna številka je obvezna.';
        } elseif (!preg_match('/^[0-9]{4}$/', $postna_stevilka)) {
            $response['errors']['postna_stevilka'] = 'Poštna številka mora vsebovati 4 številke.';
        }
    }

    // Če ni napak, nadaljujemo z registracijo
    if (empty($response['errors'])) {
        try {
            // Preveri, če e-pošta že obstaja
            $table = $tip_uporabnika === 'stranka' ? 'stranka' : 'prodajalec';
            $stmt = $pdo->prepare("SELECT id FROM $table WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->fetch()) {
                $response['errors']['email'] = 'Ta e-poštni naslov je že registriran.';
                $response['message'] = 'Ta e-poštni naslov je že registriran.';
            } else {
                // Zakodiraj geslo
                $hash = password_hash($geslo, PASSWORD_DEFAULT);
                
                // Vstavi novega uporabnika
                if ($tip_uporabnika === 'stranka') {
                    $stmt = $pdo->prepare('INSERT INTO stranka (ime, priimek, email, geslo, ulica, hisna_stevilka, posta, postna_stevilka) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
                    $stmt->execute([$ime, $priimek, $email, $hash, $ulica, $hisna_stevilka, $posta, $postna_stevilka]);
                } else {
                    $stmt = $pdo->prepare('INSERT INTO prodajalec (ime, priimek, email, geslo) VALUES (?, ?, ?, ?)');
                    $stmt->execute([$ime, $priimek, $email, $hash]);
                }
                
                $response['success'] = true;
                $response['message'] = 'Registracija uspešna! Preusmerjanje na prijavo...';
            }
        } catch (PDOException $e) {
            error_log("Napaka pri registraciji: " . $e->getMessage());
            $response['message'] = 'Prišlo je do napake pri registraciji. Prosimo, poskusite ponovno.';
        }
    }
}

echo json_encode($response);

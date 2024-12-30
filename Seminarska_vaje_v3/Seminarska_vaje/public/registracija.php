<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/secure_redirect.php';

// Zahtevaj HTTPS za registracijo
requireSSL();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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

    // Preveri, če so vsa polja izpolnjena
    if (empty($ime) || empty($priimek) || empty($email) || empty($geslo) || empty($potrdi_geslo) || 
        ($tip_uporabnika === 'stranka' && (empty($ulica) || empty($hisna_stevilka) || empty($posta) || empty($postna_stevilka)))) {
        $error = 'Prosimo, izpolnite vsa obvezna polja.';
    }
    // Preveri, če se gesli ujemata
    elseif ($geslo !== $potrdi_geslo) {
        $error = 'Gesli se ne ujemata.';
    }
    // Preveri dolžino gesla
    elseif (strlen($geslo) < 6) {
        $error = 'Geslo mora biti dolgo vsaj 6 znakov.';
    }
    // Preveri veljavnost e-pošte
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Prosimo, vnesite veljaven e-poštni naslov.';
    }
    // Preveri format poštne številke
    elseif ($tip_uporabnika === 'stranka' && !preg_match('/^[0-9]{4}$/', $postna_stevilka)) {
        $error = 'Poštna številka mora vsebovati 4 številke.';
    }
    else {
        try {
            // Preveri, če e-pošta že obstaja v izbrani tabeli
            $table = $tip_uporabnika === 'stranka' ? 'stranka' : 'prodajalec';
            $stmt = $pdo->prepare("SELECT id FROM $table WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->fetch()) {
                $error = 'Ta e-poštni naslov je že registriran.';
            } else {
                // Zakodiraj geslo
                $hash = password_hash($geslo, PASSWORD_DEFAULT);
                
                // Vstavi novega uporabnika v ustrezno tabelo
                if ($tip_uporabnika === 'stranka') {
                    try {
                        $stmt = $pdo->prepare('INSERT INTO stranka (ime, priimek, email, geslo, ulica, hisna_stevilka, posta, postna_stevilka) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
                        $stmt->execute([$ime, $priimek, $email, $hash, $ulica, $hisna_stevilka, $posta, $postna_stevilka]);
                        $success = 'Registracija uspešna! Zdaj se lahko prijavite.';
                        header('refresh:2;url=login.php');
                    } catch (PDOException $e) {
                        error_log("Napaka pri registraciji: " . $e->getMessage());
                        $error = 'Prišlo je do napake pri registraciji. Prosimo, poskusite ponovno.';
                    }
                } else {
                    $stmt = $pdo->prepare('INSERT INTO prodajalec (ime, priimek, email, geslo) VALUES (?, ?, ?, ?)');
                    $stmt->execute([$ime, $priimek, $email, $hash]);
                    $success = 'Registracija uspešna! Zdaj se lahko prijavite.';
                    header('refresh:2;url=login.php');
                }
            }
        } catch (PDOException $e) {
            error_log("Napaka pri registraciji: " . $e->getMessage());
            $error = 'Prišlo je do napake pri registraciji. Prosimo, poskusite ponovno.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registracija - Spletna Prodajalna</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .field-error {
            border-color: #dc3545;
        }
        .error-feedback {
            display: none;
            color: #dc3545;
            font-size: 0.875em;
            margin-top: 0.25rem;
        }
    </style>
</head>
<body>
    <?php require_once 'navbar.php'; ?>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-center mb-0">Registracija</h3>
                    </div>
                    <div class="card-body">
                        <div id="success-message" class="alert alert-success d-none"><?= htmlspecialchars($success) ?></div>
                        <div id="error-message" class="alert alert-danger d-none"><?= htmlspecialchars($error) ?></div>

                        <form id="registration-form" method="POST" novalidate>
                            <div class="mb-3">
                                <label for="ime" class="form-label">Ime *</label>
                                <input type="text" class="form-control" id="ime" name="ime" required>
                                <div class="error-feedback" id="ime-error"></div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="priimek" class="form-label">Priimek *</label>
                                <input type="text" class="form-control" id="priimek" name="priimek" required>
                                <div class="error-feedback" id="priimek-error"></div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">E-poštni naslov *</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                                <div class="error-feedback" id="email-error"></div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="geslo" class="form-label">Geslo *</label>
                                <input type="password" class="form-control" id="geslo" name="geslo" required>
                                <div class="error-feedback" id="geslo-error"></div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="potrdi_geslo" class="form-label">Potrdi geslo *</label>
                                <input type="password" class="form-control" id="potrdi_geslo" name="potrdi_geslo" required>
                                <div class="error-feedback" id="potrdi_geslo-error"></div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Tip uporabnika *</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="tip_uporabnika" id="stranka" value="stranka" checked>
                                    <label class="form-check-label" for="stranka">Stranka</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="tip_uporabnika" id="prodajalec" value="prodajalec">
                                    <label class="form-check-label" for="prodajalec">Prodajalec</label>
                                </div>
                            </div>

                            <div id="stranka-fields">
                                <div class="mb-3">
                                    <label for="ulica" class="form-label">Ulica *</label>
                                    <input type="text" class="form-control" id="ulica" name="ulica">
                                    <div class="error-feedback" id="ulica-error"></div>
                                </div>
                                <div class="mb-3">
                                    <label for="hisna_stevilka" class="form-label">Hišna številka *</label>
                                    <input type="text" class="form-control" id="hisna_stevilka" name="hisna_stevilka">
                                    <div class="error-feedback" id="hisna_stevilka-error"></div>
                                </div>
                                <div class="mb-3">
                                    <label for="posta" class="form-label">Pošta *</label>
                                    <input type="text" class="form-control" id="posta" name="posta">
                                    <div class="error-feedback" id="posta-error"></div>
                                </div>
                                <div class="mb-3">
                                    <label for="postna_stevilka" class="form-label">Poštna številka *</label>
                                    <input type="text" class="form-control" id="postna_stevilka" name="postna_stevilka" 
                                           pattern="[0-9]{4}" maxlength="4">
                                    <div class="error-feedback" id="postna_stevilka-error"></div>
                                    <div class="form-text">Vnesite 4-mestno poštno številko.</div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="g-recaptcha" data-sitekey="YOUR_SITE_KEY"></div>
                                <div class="error-feedback" id="recaptcha-error"></div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">Registracija</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('registration-form');
        const strankaFields = document.getElementById('stranka-fields');
        const tipUporabnika = document.getElementsByName('tip_uporabnika');
        const successMessage = document.getElementById('success-message');
        const errorMessage = document.getElementById('error-message');

        // Toggle stranka fields visibility
        function toggleStrankaFields() {
            const isStranka = document.getElementById('stranka').checked;
            strankaFields.style.display = isStranka ? 'block' : 'none';
            
            // Toggle required attribute
            const fields = strankaFields.querySelectorAll('input');
            fields.forEach(field => {
                field.required = isStranka;
            });
        }

        // Add event listeners for radio buttons
        tipUporabnika.forEach(radio => {
            radio.addEventListener('change', toggleStrankaFields);
        });

        // Initial toggle
        toggleStrankaFields();

        // Reset error states
        function resetErrors() {
            document.querySelectorAll('.error-feedback').forEach(el => {
                el.style.display = 'none';
            });
            document.querySelectorAll('.field-error').forEach(el => {
                el.classList.remove('field-error');
            });
            errorMessage.classList.add('d-none');
            successMessage.classList.add('d-none');
        }

        // Show error for specific field
        function showError(fieldName, message) {
            const field = document.getElementById(fieldName);
            const errorDiv = document.getElementById(fieldName + '-error');
            if (field && errorDiv) {
                field.classList.add('field-error');
                errorDiv.textContent = message;
                errorDiv.style.display = 'block';
            }
        }

        // Handle form submission
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            resetErrors();

            // Preveri reCAPTCHA
            const recaptchaResponse = grecaptcha.getResponse();
            if (!recaptchaResponse) {
                showError('recaptcha', 'Prosimo, potrdite da niste robot.');
                return;
            }

            const formData = new FormData(form);
            const submitButton = form.querySelector('button[type="submit"]');
            submitButton.disabled = true;

            fetch('register_ajax.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    successMessage.textContent = data.message;
                    successMessage.classList.remove('d-none');
                    form.reset();
                    grecaptcha.reset();
                    setTimeout(() => {
                        window.location.href = 'login.php';
                    }, 2000);
                } else {
                    if (data.errors) {
                        Object.keys(data.errors).forEach(field => {
                            showError(field, data.errors[field]);
                        });
                    }
                    if (data.message) {
                        errorMessage.textContent = data.message;
                        errorMessage.classList.remove('d-none');
                    }
                    grecaptcha.reset();
                }
            })
            .catch(error => {
                errorMessage.textContent = 'Prišlo je do napake pri komunikaciji s strežnikom.';
                errorMessage.classList.remove('d-none');
                grecaptcha.reset();
            })
            .finally(() => {
                submitButton.disabled = false;
            });
        });
    });
    </script>
</body>
</html>

<?php
/**
 * TrendHunter Brasil - Registration Page with Firebase Auth
 */

declare(strict_types=1);

spl_autoload_register(function ($class) {
    $prefix = 'TrendHunter\\';
    $baseDir = __DIR__ . '/src/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    if (file_exists($file)) require $file;
});

use TrendHunter\Auth;

if (Auth::isLoggedIn()) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - TrendHunter Brasil</title>
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <style>
        :root {
            --bg-color: #0b0c16;
            --card-bg: rgba(18, 20, 38, 0.65);
            --primary-glow: rgba(93, 89, 235, 0.4);
            --accent-purple: #745df7;
            --accent-turquoise: #06e1cc;
            --text-muted: #8c8ea7;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-color);
            background-image: 
                radial-gradient(at 10% 20%, rgba(116, 93, 247, 0.15) 0px, transparent 50%),
                radial-gradient(at 90% 80%, rgba(6, 225, 204, 0.1) 0px, transparent 50%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow-x: hidden;
            position: relative;
        }

        .register-card {
            background: var(--card-bg);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.4);
            width: 100%;
            max-width: 440px;
            z-index: 10;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .logo-title {
            font-weight: 800;
            background: linear-gradient(135deg, #ffffff 30%, #a5a6f6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: -0.5px;
        }

        .logo-icon {
            background: linear-gradient(135deg, var(--accent-purple), var(--accent-turquoise));
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            color: #0b0c16;
            margin-bottom: 20px;
            box-shadow: 0 0 20px rgba(116, 93, 247, 0.4);
        }

        .form-label {
            font-weight: 500;
            font-size: 13px;
            color: #d1d2dc;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-control {
            background-color: rgba(9, 10, 20, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #fff;
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            background-color: rgba(9, 10, 20, 0.9);
            border-color: var(--accent-purple);
            box-shadow: 0 0 0 4px var(--primary-glow);
            color: #fff;
        }

        .btn-submit {
            background: linear-gradient(135deg, var(--accent-purple), #5d59eb);
            border: none;
            color: #fff;
            border-radius: 10px;
            padding: 12px;
            font-size: 14px;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(116, 93, 247, 0.3);
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(116, 93, 247, 0.5);
        }

        .link-login {
            color: var(--accent-turquoise);
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
        }

        .footer-text {
            color: var(--text-muted);
            font-size: 12px;
            margin-top: 30px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="register-card">
        <div class="d-flex flex-column align-items-center text-center">
            <div class="logo-icon">
                <i class="fa-solid fa-square-trend-up"></i>
            </div>
            <h3 class="logo-title mb-1">TrendHunter</h3>
            <p style="color: var(--accent-turquoise); font-size: 13px; font-weight: 600; letter-spacing: 1px;" class="mb-4">BRASIL</p>
        </div>

        <!-- Feedback Alerts -->
        <div id="error-alert" class="alert alert-danger border-0 py-2 px-3 mb-4 rounded-3 d-flex align-items-center" style="display: none; background-color: rgba(220, 53, 69, 0.15); color: #f87272; font-size: 13px;" role="alert">
            <i class="fa-solid fa-triangle-exclamation me-2"></i>
            <div id="error-text"></div>
        </div>

        <div id="success-alert" class="alert alert-success border-0 py-2 px-3 mb-4 rounded-3 d-flex align-items-center" style="display: none; background-color: rgba(25, 135, 84, 0.15); color: #72f8a8; font-size: 13px;" role="alert">
            <i class="fa-regular fa-circle-check me-2"></i>
            <div id="success-text"></div>
        </div>

        <form id="register-form" autocomplete="off">
            <div class="mb-3">
                <label for="name" class="form-label mb-1">Nome Completo</label>
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-end-0 border-light-subtle text-muted" style="border-radius: 10px 0 0 10px; border-right: none !important;"><i class="fa-regular fa-user"></i></span>
                    <input type="text" class="form-control border-start-0" id="name" placeholder="Ex: Fernando Paiva" required style="border-radius: 0 10px 10px 0;">
                </div>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label mb-1">E-mail</label>
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-end-0 border-light-subtle text-muted" style="border-radius: 10px 0 0 10px; border-right: none !important;"><i class="fa-regular fa-envelope"></i></span>
                    <input type="email" class="form-control border-start-0" id="email" placeholder="nome@provedor.com" required style="border-radius: 0 10px 10px 0;">
                </div>
            </div>

            <div class="mb-4">
                <label for="password" class="form-label mb-1">Senha (Mín. 6 caracteres)</label>
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-end-0 border-light-subtle text-muted" style="border-radius: 10px 0 0 10px; border-right: none !important;"><i class="fa-solid fa-lock"></i></span>
                    <input type="password" class="form-control border-start-0" id="password" placeholder="••••••••" required style="border-radius: 0 10px 10px 0;">
                </div>
            </div>

            <button type="submit" id="btn-submit" class="btn btn-submit w-100 mb-3">
                <span id="btn-text">Registrar com Firebase</span>
                <span id="btn-spinner" class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="display: none;"></span>
            </button>
        </form>

        <div class="text-center mt-3">
            <span style="color: var(--text-muted); font-size: 14px;">Já tem uma conta? </span>
            <a href="login.php" class="link-login">Faça Login</a>
        </div>

        <div class="footer-text">
            &copy; 2026 TrendHunter Brasil.
        </div>
    </div>

    <!-- Firebase App & Auth SDK Loader -->
    <script src="https://www.gstatic.com/firebasejs/10.8.0/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/10.8.0/firebase-auth-compat.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <script>
        // User's Firebase Configuration
        const firebaseConfig = {
            apiKey: "AIzaSyDzhKsZeowjo1aiTQvMJCKqktWsD-LS17w",
            authDomain: "mafekidsbr.firebaseapp.com",
            projectId: "mafekidsbr",
            storageBucket: "mafekidsbr.firebasestorage.app",
            messagingSenderId: "16456704349",
            appId: "1:16456704349:web:4b1d742210ef0f0f4448b4",
            measurementId: "G-FWQ5PBLPYS"
        };

        // Initialize Firebase
        firebase.initializeApp(firebaseConfig);
        const auth = firebase.auth();

        // Intercept registration form submission
        $('#register-form').on('submit', function(e) {
            e.preventDefault();

            const name = $('#name').val().trim();
            const email = $('#email').val().trim();
            const password = $('#password').val();

            if (password.length < 6) {
                showError('A senha deve conter no mínimo 6 caracteres.');
                return;
            }

            // Show loader
            $('#error-alert').hide();
            $('#success-alert').hide();
            $('#btn-text').hide();
            $('#btn-spinner').show();
            $('#btn-submit').prop('disabled', true);

            // Register on Firebase Auth
            auth.createUserWithEmailAndPassword(email, password)
                .then((userCredential) => {
                    // Update user Profile display name in Firebase
                    return userCredential.user.updateProfile({
                        displayName: name
                    }).then(() => {
                        return userCredential.user.getIdToken();
                    });
                })
                .then((idToken) => {
                    // Send Token to PHP API to register/login local user session
                    return $.post('api.php?action=firebase_register', { 
                        idToken: idToken,
                        name: name
                    });
                })
                .then((response) => {
                    if (response.success) {
                        $('#btn-spinner').hide();
                        $('#success-text').text('Registro efetuado com sucesso! Redirecionando...');
                        $('#success-alert').show();
                        setTimeout(() => {
                            window.location.href = 'index.php';
                        }, 1200);
                    } else {
                        showError(response.error || 'Falha ao sincronizar registro local.');
                    }
                })
                .catch((error) => {
                    let userMsg = error.message;
                    if (error.code === 'auth/email-already-in-use') {
                        userMsg = 'Este endereço de e-mail já está cadastrado no Firebase.';
                    } else if (error.code === 'auth/invalid-email') {
                        userMsg = 'Formato de e-mail inválido.';
                    } else if (error.code === 'auth/weak-password') {
                        userMsg = 'A senha fornecida é muito fraca.';
                    } else if (error.code === 'auth/unauthorized-domain') {
                        userMsg = 'O domínio sam.fernandopaiva.com.br ainda não foi liberado em "Authorized Domains" no painel do seu Firebase!';
                    }
                    showError(userMsg);
                });
        });

        function showError(msg) {
            $('#btn-spinner').hide();
            $('#btn-text').show();
            $('#btn-submit').prop('disabled', false);
            $('#error-text').text(msg);
            $('#error-alert').css('display', 'flex');
        }
    </script>
</body>
</html>

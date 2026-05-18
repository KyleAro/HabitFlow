<?php
session_start();
require_once __DIR__ . '/../includes/bootstrap.php';
habitflow_require('auth.php');

if (AuthHandler::isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - HabitFlow</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">
    <link rel="stylesheet" href="<?php echo habitflow_asset('css/theme.css'); ?>">
    <script src="<?php echo habitflow_asset('js/theme.js'); ?>"></script>
</head>
<body class="auth-body">

    <div class="bg-blob blob-1"></div>
    <div class="bg-blob blob-2"></div>
    <div class="bg-blob blob-3"></div>

    <nav class="navbar">
        <div class="nav-container">
            <a href="index.php" class="logo">
                <i class="ti ti-target"></i>
                <span>HabitFlow</span>
            </a>
            <div class="nav-links">
                <button type="button" class="theme-toggle" aria-label="Toggle theme">
                    <i class="ti ti-sun icon-sun"></i>
                    <i class="ti ti-moon icon-moon"></i>
                </button>
                <a href="index.php" class="btn btn-outline">Back to home</a>
            </div>
        </div>
    </nav>

    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1>Welcome back</h1>
                <p>Log in to continue tracking your habits</p>
            </div>

            <div id="alertBox"></div>

            <form id="loginForm" class="auth-form">
                <div class="form-group">
                    <label for="email">Email</label>
                    <div class="input-wrapper">
                        <i class="ti ti-mail input-icon"></i>
                        <input type="email" id="email" name="email" placeholder="you@example.com" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrapper">
                        <i class="ti ti-lock input-icon"></i>
                        <input type="password" id="password" name="password" placeholder="Enter your password" required>
                    </div>
                </div>

                <button type="submit" id="submitBtn" class="btn btn-primary btn-block">
                    <span id="btnText">Log in</span>
                    <i class="ti ti-arrow-right" id="btnIcon"></i>
                </button>
            </form>

            <div class="auth-footer">
                Don't have an account?
                <a href="register.php">Sign up free</a>
            </div>
        </div>
    </div>

    <script type="module" src="<?php echo habitflow_asset('js/firebase-config.js'); ?>"></script>
    <script type="module">
        const form = document.getElementById('loginForm');
        const alertBox = document.getElementById('alertBox');
        const submitBtn = document.getElementById('submitBtn');
        const btnText = document.getElementById('btnText');
        const btnIcon = document.getElementById('btnIcon');

        function showAlert(message, type = 'error') {
            const icon = type === 'error' ? 'ti-alert-circle' : 'ti-check';
            alertBox.innerHTML = `
                <div class="alert alert-${type}">
                    <i class="ti ${icon}"></i>
                    ${message}
                </div>
            `;
        }

        function setLoading(loading) {
            submitBtn.disabled = loading;
            btnText.textContent = loading ? 'Logging in...' : 'Log in';
            btnIcon.className = loading ? 'ti ti-loader' : 'ti ti-arrow-right';
            if (loading) {
                btnIcon.style.animation = 'spin 1s linear infinite';
            } else {
                btnIcon.style.animation = '';
            }
        }

        function waitForFirebase() {
            return new Promise((resolve) => {
                if (window.firebaseAuth) {
                    resolve();
                } else {
                    const interval = setInterval(() => {
                        if (window.firebaseAuth) {
                            clearInterval(interval);
                            resolve();
                        }
                    }, 50);
                }
            });
        }

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            alertBox.innerHTML = '';

            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;

            if (!email || !password) {
                showAlert('Please fill in all fields');
                return;
            }

            setLoading(true);

            try {
                await waitForFirebase();

                const userCredential = await window.firebaseSignIn(
                    window.firebaseAuth,
                    email,
                    password
                );

                const user = userCredential.user;

                const response = await fetch('<?php echo habitflow_api('firebase-auth.php'); ?>', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'login',
                        uid: user.uid,
                        email: user.email,
                        username: user.displayName || email.split('@')[0]
                    })
                });

                const data = await response.json();

                if (data.success) {
                    showAlert('Login successful! Redirecting...', 'success');
                    setTimeout(() => {
                        window.location.href = data.redirect || 'dashboard.php';
                    }, 500);
                } else {
                    showAlert(data.error || 'Login failed');
                    setLoading(false);
                }
            } catch (error) {
                console.error('Login error:', error);
                let errorMessage = 'Login failed';

                switch (error.code) {
                    case 'auth/invalid-email':
                        errorMessage = 'Invalid email address';
                        break;
                    case 'auth/user-not-found':
                    case 'auth/wrong-password':
                    case 'auth/invalid-credential':
                        errorMessage = 'Invalid email or password';
                        break;
                    case 'auth/too-many-requests':
                        errorMessage = 'Too many attempts. Try again later.';
                        break;
                    case 'auth/network-request-failed':
                        errorMessage = 'Network error. Check your connection.';
                        break;
                    default:
                        errorMessage = error.message || 'Login failed';
                }

                showAlert(errorMessage);
                setLoading(false);
            }
        });
    </script>

    <style>
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
    </style>

</body>
</html>
<?php
session_start();
require_once 'auth.php';

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
    <title>Sign Up - HabitFlow</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">
    <link rel="stylesheet" href="theme.css">
    <script src="theme.js"></script>
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
                <h1>Create your account</h1>
                <p>Start building better habits today</p>
            </div>

            <div id="alertBox"></div>

            <form id="registerForm" class="auth-form">
                <div class="form-group">
                    <label for="username">Username</label>
                    <div class="input-wrapper">
                        <i class="ti ti-user input-icon"></i>
                        <input type="text" id="username" name="username" placeholder="johndoe" required minlength="3">
                    </div>
                </div>

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
                        <input type="password" id="password" name="password" placeholder="At least 6 characters" required minlength="6">
                    </div>
                </div>

                <div class="form-group">
                    <label for="password_confirm">Confirm Password</label>
                    <div class="input-wrapper">
                        <i class="ti ti-lock input-icon"></i>
                        <input type="password" id="password_confirm" name="password_confirm" placeholder="Re-enter your password" required>
                    </div>
                </div>

                <button type="submit" id="submitBtn" class="btn btn-primary btn-block">
                    <span id="btnText">Create account</span>
                    <i class="ti ti-arrow-right" id="btnIcon"></i>
                </button>
            </form>

            <div class="auth-footer">
                Already have an account?
                <a href="login.php">Log in</a>
            </div>
        </div>
    </div>

    <script type="module" src="firebase-config.js"></script>
    <script type="module">
        const form = document.getElementById('registerForm');
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
            btnText.textContent = loading ? 'Creating account...' : 'Create account';
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

            const username = document.getElementById('username').value.trim();
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            const passwordConfirm = document.getElementById('password_confirm').value;

            if (!username || !email || !password) {
                showAlert('Please fill in all fields');
                return;
            }

            if (username.length < 3) {
                showAlert('Username must be at least 3 characters');
                return;
            }

            if (password !== passwordConfirm) {
                showAlert('Passwords do not match');
                return;
            }

            if (password.length < 6) {
                showAlert('Password must be at least 6 characters');
                return;
            }

            setLoading(true);

            try {
                await waitForFirebase();

                const userCredential = await window.firebaseCreateUser(
                    window.firebaseAuth,
                    email,
                    password
                );

                const user = userCredential.user;

                try {
                    await window.firebaseUpdateProfile(user, {
                        displayName: username
                    });
                } catch (profileError) {
                    console.warn('Could not update profile:', profileError);
                }

                const response = await fetch('firebase-auth.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'register',
                        uid: user.uid,
                        email: user.email,
                        username: username
                    })
                });

                const data = await response.json();

                if (data.success) {
                    showAlert('Account created! Redirecting...', 'success');
                    setTimeout(() => {
                        window.location.href = data.redirect || 'dashboard.php';
                    }, 500);
                } else {
                    showAlert(data.error || 'Registration failed');
                    setLoading(false);
                }
            } catch (error) {
                console.error('Registration error:', error);
                let errorMessage = 'Registration failed';

                switch (error.code) {
                    case 'auth/email-already-in-use':
                        errorMessage = 'This email is already registered. Try logging in instead.';
                        break;
                    case 'auth/invalid-email':
                        errorMessage = 'Invalid email address';
                        break;
                    case 'auth/weak-password':
                        errorMessage = 'Password is too weak (min 6 characters)';
                        break;
                    case 'auth/operation-not-allowed':
                        errorMessage = 'Email/password sign up is not enabled. Enable it in Firebase Console.';
                        break;
                    case 'auth/network-request-failed':
                        errorMessage = 'Network error. Check your connection.';
                        break;
                    default:
                        errorMessage = error.message || 'Registration failed';
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
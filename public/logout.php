<?php
session_start();
require_once __DIR__ . '/../includes/bootstrap.php';
habitflow_require('auth.php');

// Server-side logout immediately (don't rely only on JavaScript)
AuthHandler::logout();

$homeUrl = habitflow_page('index');
$authUrl = habitflow_ai_endpoint('firebase-auth');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="refresh" content="2;url=<?php echo htmlspecialchars($homeUrl); ?>">
    <title>Logging out...</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">
    <link rel="stylesheet" href="<?php echo habitflow_asset('css/theme.css'); ?>">
</head>
<body class="auth-body">

    <div class="auth-container">
        <div class="auth-card" style="text-align: center;">
            <i class="ti ti-loader" style="font-size: 48px; color: var(--purple-600); animation: spin 1s linear infinite;"></i>
            <h1 style="margin-top: 16px;">Logging out...</h1>
            <p style="color: var(--text-secondary); margin-top: 8px;">Please wait</p>
        </div>
    </div>

    <script type="module" src="<?php echo habitflow_api('firebase-config.php'); ?>"></script>
    <script type="module">
        const HOME_URL = <?php echo json_encode($homeUrl); ?>;
        const AUTH_URL = <?php echo json_encode($authUrl); ?>;

        function waitForFirebase(maxMs = 3000) {
            return new Promise((resolve) => {
                if (window.firebaseAuth) {
                    resolve();
                    return;
                }
                const deadline = Date.now() + maxMs;
                const interval = setInterval(() => {
                    if (window.firebaseAuth || Date.now() >= deadline) {
                        clearInterval(interval);
                        resolve();
                    }
                }, 50);
            });
        }

        (async () => {
            try {
                await waitForFirebase();
                if (window.firebaseAuth) {
                    await window.firebaseSignOut(window.firebaseAuth);
                }
            } catch (e) {
                console.warn('Firebase signout:', e);
            }

            try {
                await fetch(AUTH_URL, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'logout' }),
                });
            } catch (e) {
                console.warn('Session logout:', e);
            }

            window.location.replace(HOME_URL);
        })();
    </script>

    <style>
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
    </style>

</body>
</html>

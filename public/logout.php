<?php
session_start();
require_once __DIR__ . '/../includes/bootstrap.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Logging out...</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">
    <link rel="stylesheet" href="<?php echo habitflow_asset('css/style.css'); ?>">
</head>
<body class="auth-body">

    <div class="auth-container">
        <div class="auth-card" style="text-align: center;">
            <i class="ti ti-loader" style="font-size: 48px; color: var(--purple-600); animation: spin 1s linear infinite;"></i>
            <h1 style="margin-top: 16px;">Logging out...</h1>
            <p style="color: var(--text-secondary); margin-top: 8px;">Please wait</p>
        </div>
    </div>

    <script type="module" src="<?php echo habitflow_asset('js/firebase-config.js'); ?>"></script>
    <script type="module">
        async function logout() {
            try {
                if (window.firebaseAuth) {
                    await window.firebaseSignOut(window.firebaseAuth);
                }
            } catch (error) {
                console.warn('Firebase signout error:', error);
            }

            try {
                await fetch('<?php echo habitflow_api('firebase-auth.php'); ?>', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'logout' })
                });
            } catch (error) {
                console.warn('Session logout error:', error);
            }

            window.location.href = '<?php echo habitflow_url('public/index.php'); ?>';
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

        (async () => {
            await waitForFirebase();
            await logout();
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

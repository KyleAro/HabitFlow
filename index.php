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
    <title>HabitFlow - Build Better Habits with AI</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">
    <link rel="stylesheet" href="theme.css">
    <script src="theme.js"></script>
</head>
<body class="home-body">

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
                <a href="#features">Features</a>
                <a href="#how-it-works">How it works</a>
                <a href="#about">About</a>
                <button type="button" class="theme-toggle" aria-label="Toggle theme">
                    <i class="ti ti-sun icon-sun"></i>
                    <i class="ti ti-moon icon-moon"></i>
                </button>
                <a href="login.php" class="btn btn-outline">Login</a>
                <a href="register.php" class="btn btn-primary">Get started</a>
            </div>
            <button class="mobile-menu-btn" onclick="toggleMobileMenu()">
                <i class="ti ti-menu-2"></i>
            </button>
        </div>
        <div class="mobile-menu" id="mobileMenu">
            <a href="#features">Features</a>
            <a href="#how-it-works">How it works</a>
            <a href="#about">About</a>
            <a href="login.php">Login</a>
            <a href="register.php" class="btn btn-primary">Get started</a>
        </div>
    </nav>

    <section class="hero">
        <div class="container">
            <div class="hero-badge">
                <i class="ti ti-sparkles"></i>
                <span>Powered by Qwen AI</span>
            </div>
            <h1>Build better habits,<br>one day at a time</h1>
            <p class="hero-subtitle">
                Track your daily habits, build streaks, and get personalized AI motivation to keep you going.
            </p>
            <div class="hero-buttons">
                <a href="register.php" class="btn btn-primary btn-large">Start tracking free</a>
                <a href="#how-it-works" class="btn btn-outline btn-large">See how it works</a>
            </div>
        </div>
    </section>

    <section class="preview-section">
        <div class="container">
            <div class="app-preview">
                <div class="preview-header">Your habits today</div>
                <div class="preview-habits">
                    <div class="preview-habit">
                        <div class="habit-left">
                            <i class="ti ti-check check-icon"></i>
                            <span>Drink 8 glasses of water</span>
                        </div>
                        <span class="streak-badge">12 day streak</span>
                    </div>
                    <div class="preview-habit">
                        <div class="habit-left">
                            <i class="ti ti-check check-icon"></i>
                            <span>Morning workout</span>
                        </div>
                        <span class="streak-badge">7 day streak</span>
                    </div>
                    <div class="preview-habit">
                        <div class="habit-left">
                            <i class="ti ti-circle empty-icon"></i>
                            <span>Read for 30 minutes</span>
                        </div>
                        <span class="streak-badge">3 day streak</span>
                    </div>
                </div>
                <div class="ai-message">
                    <i class="ti ti-sparkles"></i>
                    <span>Amazing work! Your 12-day water streak is incredible!</span>
                </div>
            </div>
        </div>
    </section>

    <section class="features-section" id="features">
        <div class="container">
            <h2 class="section-title">Why HabitFlow?</h2>
            <p class="section-subtitle">Everything you need to build lasting habits</p>

            <div class="features-grid">
                <div class="feature-card">
                    <i class="ti ti-flame feature-icon coral"></i>
                    <h3>Track streaks</h3>
                    <p>Build momentum with daily streaks that motivate you to keep going.</p>
                </div>
                <div class="feature-card">
                    <i class="ti ti-sparkles feature-icon purple"></i>
                    <h3>AI motivation</h3>
                    <p>Personalized encouragement from Qwen AI after every completion.</p>
                </div>
                <div class="feature-card">
                    <i class="ti ti-chart-bar feature-icon teal"></i>
                    <h3>See progress</h3>
                    <p>Visual insights into your patterns and improvement over time.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="how-it-works" id="how-it-works">
        <div class="container">
            <h2 class="section-title">How it works</h2>
            <p class="section-subtitle">Get started in 3 simple steps</p>

            <div class="steps-grid">
                <div class="step">
                    <div class="step-number">1</div>
                    <h3>Sign up</h3>
                    <p>Create your free account in seconds</p>
                </div>
                <div class="step">
                    <div class="step-number">2</div>
                    <h3>Add habits</h3>
                    <p>Choose what you want to track daily</p>
                </div>
                <div class="step">
                    <div class="step-number">3</div>
                    <h3>Check in</h3>
                    <p>Mark completion and get AI feedback</p>
                </div>
            </div>
        </div>
    </section>

    <section class="cta-section">
        <div class="container">
            <h2>Ready to start?</h2>
            <p>Join others building better habits today.</p>
            <a href="register.php" class="btn btn-primary btn-large">Get started free</a>
        </div>
    </section>

    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-logo">
                    <i class="ti ti-target"></i>
                    <span>HabitFlow</span>
                </div>
                <p class="footer-text">&copy; 2026 HabitFlow. Made with Qwen AI.</p>
            </div>
        </div>
    </footer>

    <script>
        function toggleMobileMenu() {
            const menu = document.getElementById('mobileMenu');
            menu.classList.toggle('open');
        }

        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    document.getElementById('mobileMenu').classList.remove('open');
                }
            });
        });
    </script>
</body>
</html>
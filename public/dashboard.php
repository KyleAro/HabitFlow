<?php
session_start();
require_once __DIR__ . '/../includes/bootstrap.php';
habitflow_require('auth.php');

if (!AuthHandler::isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$userId = AuthHandler::getUserId();
$username = AuthHandler::getUsername();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - HabitFlow</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">
    <link rel="stylesheet" href="<?php echo habitflow_asset('css/theme.css'); ?>">
    <script src="<?php echo habitflow_asset('js/theme.js'); ?>"></script>
</head>
<body class="dashboard-body">

    <div class="bg-blob blob-1"></div>
    <div class="bg-blob blob-2"></div>
    <div class="bg-blob blob-3"></div>

    <nav class="navbar">
        <div class="nav-container">
            <a href="dashboard.php" class="logo">
                <i class="ti ti-target"></i>
                <span>HabitFlow</span>
            </a>
            <div class="nav-links">
                <span class="user-greeting">
                    <i class="ti ti-user"></i>
                    <?php echo htmlspecialchars($username); ?>
                </span>
                <button type="button" class="theme-toggle" aria-label="Toggle theme">
                    <i class="ti ti-sun icon-sun"></i>
                    <i class="ti ti-moon icon-moon"></i>
                </button>
                <a href="logout.php" class="btn btn-outline">
                    <i class="ti ti-logout"></i>
                    Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="dashboard-container">

        <div class="dashboard-header">
            <div>
                <h1>Hello, <?php echo htmlspecialchars($username); ?></h1>
                <p class="dashboard-date"><?php echo date('l, F j, Y'); ?></p>
            </div>
        </div>

        <div id="briefingContainer"></div>
        <div id="aiFeedback"></div>
        <div id="alertBox"></div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon-wrapper purple">
                    <i class="ti ti-target"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-number" id="statTotal">0</div>
                    <div class="stat-label">Total habits</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon-wrapper teal">
                    <i class="ti ti-check"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-number" id="statCompleted">0/0</div>
                    <div class="stat-label">Completed today</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon-wrapper coral">
                    <i class="ti ti-flame"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-number" id="statStreak">0</div>
                    <div class="stat-label">Total streak days</div>
                </div>
            </div>
        </div>

        <div class="dashboard-section">
            <div class="section-header">
                <h2>Add new habit</h2>
                <div class="header-actions">
                    <button type="button" id="aiGenerateBtn" class="btn btn-ai btn-small">
                        <i class="ti ti-sparkles"></i>
                        <span>AI generate</span>
                    </button>
                    <button type="button" id="toggleFormBtn" class="btn btn-outline btn-small">
                        <i class="ti ti-plus" id="toggleIcon"></i>
                        <span id="toggleText">Manual</span>
                    </button>
                </div>
            </div>

            <div id="aiGenerateBox" class="ai-generate-box" style="display: none;">
                <div class="ai-generate-header">
                    <i class="ti ti-sparkles"></i>
                    <span>Tell AI your goal, get a habit</span>
                </div>
                <div class="ai-input-row">
                    <input type="text" id="goalInput" placeholder="e.g., I want to wake up early, lose weight, read more..." maxlength="200">
                    <button type="button" id="generateBtn" class="btn btn-primary">
                        <i class="ti ti-wand"></i>
                        Generate
                    </button>
                </div>
                <div class="ai-suggestions">
                    <span class="suggestion-label">Try:</span>
                    <button type="button" class="suggestion-chip" data-goal="I want to be more energetic">More energy</button>
                    <button type="button" class="suggestion-chip" data-goal="I want better sleep">Better sleep</button>
                    <button type="button" class="suggestion-chip" data-goal="I want to learn coding">Learn coding</button>
                    <button type="button" class="suggestion-chip" data-goal="I want to reduce stress">Less stress</button>
                </div>
                <div id="aiGenerateResult"></div>
            </div>

            <form id="addHabitForm" class="schedule-form" style="display: none;">

                <div class="form-field">
                    <label class="field-label">Habit name</label>
                    <div class="input-wrapper">
                        <i class="ti ti-target input-icon"></i>
                        <input type="text" id="habitName" placeholder="e.g., Morning workout" required maxlength="100">
                    </div>
                </div>

                <div class="form-field">
                    <label class="field-label">Category</label>
                    <div class="category-grid">
                        <label class="category-option">
                            <input type="radio" name="category" value="health" required>
                            <div class="category-card">
                                <i class="ti ti-heart" style="color: #D85A30;"></i>
                                <span>Health</span>
                            </div>
                        </label>
                        <label class="category-option">
                            <input type="radio" name="category" value="fitness">
                            <div class="category-card">
                                <i class="ti ti-barbell" style="color: #3C3489;"></i>
                                <span>Fitness</span>
                            </div>
                        </label>
                        <label class="category-option">
                            <input type="radio" name="category" value="learning">
                            <div class="category-card">
                                <i class="ti ti-book" style="color: #1D9E75;"></i>
                                <span>Learning</span>
                            </div>
                        </label>
                        <label class="category-option">
                            <input type="radio" name="category" value="mindfulness">
                            <div class="category-card">
                                <i class="ti ti-brain" style="color: #534AB7;"></i>
                                <span>Mindfulness</span>
                            </div>
                        </label>
                        <label class="category-option">
                            <input type="radio" name="category" value="productivity">
                            <div class="category-card">
                                <i class="ti ti-briefcase" style="color: #185FA5;"></i>
                                <span>Productivity</span>
                            </div>
                        </label>
                        <label class="category-option">
                            <input type="radio" name="category" value="other">
                            <div class="category-card">
                                <i class="ti ti-star" style="color: #BA7517;"></i>
                                <span>Other</span>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="form-field">
                    <label class="field-label">Frequency</label>
                    <div class="frequency-buttons">
                        <button type="button" class="freq-btn active" data-freq="daily">Daily</button>
                        <button type="button" class="freq-btn" data-freq="specific">Specific days</button>
                        <button type="button" class="freq-btn" data-freq="weekly">X times/week</button>
                    </div>
                </div>

                <div class="form-field" id="daysField" style="display: none;">
                    <label class="field-label">Days of the week</label>
                    <div class="days-grid">
                        <label class="day-option"><input type="checkbox" name="day" value="mon" checked><div class="day-card">Mon</div></label>
                        <label class="day-option"><input type="checkbox" name="day" value="tue" checked><div class="day-card">Tue</div></label>
                        <label class="day-option"><input type="checkbox" name="day" value="wed" checked><div class="day-card">Wed</div></label>
                        <label class="day-option"><input type="checkbox" name="day" value="thu" checked><div class="day-card">Thu</div></label>
                        <label class="day-option"><input type="checkbox" name="day" value="fri" checked><div class="day-card">Fri</div></label>
                        <label class="day-option"><input type="checkbox" name="day" value="sat"><div class="day-card">Sat</div></label>
                        <label class="day-option"><input type="checkbox" name="day" value="sun"><div class="day-card">Sun</div></label>
                    </div>
                </div>

                <div class="form-field" id="weeklyField" style="display: none;">
                    <label class="field-label">Times per week</label>
                    <div class="input-wrapper">
                        <i class="ti ti-repeat input-icon"></i>
                        <input type="number" id="timesPerWeek" min="1" max="7" value="3">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-field">
                        <label class="field-label">Time <span class="optional">(optional)</span></label>
                        <div class="input-wrapper">
                            <i class="ti ti-clock input-icon"></i>
                            <input type="time" id="habitTime">
                        </div>
                    </div>

                    <div class="form-field">
                        <label class="field-label">Duration <span class="optional">(optional)</span></label>
                        <div class="input-wrapper">
                            <i class="ti ti-hourglass input-icon"></i>
                            <input type="number" id="habitDuration" placeholder="30" min="1" max="1440">
                            <span class="input-suffix">min</span>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" id="cancelBtn" class="btn btn-outline">Cancel</button>
                    <button type="submit" id="addBtn" class="btn btn-primary">
                        <i class="ti ti-plus"></i>
                        Add habit
                    </button>
                </div>
            </form>
        </div>

        <div class="dashboard-section">
            <div class="section-header">
                <h2>Today's habits</h2>
                <span class="section-count" id="habitCount">0 total</span>
            </div>

            <div id="loadingState" class="empty-state">
                <i class="ti ti-loader empty-icon" style="animation: spin 1s linear infinite;"></i>
                <h3>Loading your habits...</h3>
            </div>

            <div id="emptyState" class="empty-state" style="display: none;">
                <i class="ti ti-clipboard-list empty-icon"></i>
                <h3>No habits yet</h3>
                <p>Click "AI generate" or "Manual" above to add your first habit!</p>
            </div>

            <div id="habitsList" class="habits-list" style="display: none;"></div>
        </div>

    </div>

    <button type="button" id="chatBubble" class="chat-bubble" aria-label="Open AI coach chat">
        <i class="ti ti-message-chatbot"></i>
        <span class="chat-bubble-pulse"></span>
    </button>

    <div id="chatWindow" class="chat-window" style="display: none;">
        <div class="chat-header">
            <div class="chat-header-info">
                <div class="chat-avatar">
                    <i class="ti ti-sparkles"></i>
                </div>
                <div>
                    <div class="chat-name">Flow</div>
                    <div class="chat-status">AI Habit Coach</div>
                </div>
            </div>
            <button type="button" id="chatCloseBtn" class="chat-close" aria-label="Close chat">
                <i class="ti ti-x"></i>
            </button>
        </div>
        <div id="chatMessages" class="chat-messages">
            <div class="chat-message ai">
                <div class="chat-avatar-small">
                    <i class="ti ti-sparkles"></i>
                </div>
                <div class="chat-bubble-content">
                    Hi! I'm Flow, your habit coach. Ask me anything about building habits, getting motivated, or improving your routine!
                </div>
            </div>
        </div>
        <div class="chat-input-area">
            <textarea id="chatInput" placeholder="Ask me anything..." rows="1" maxlength="500"></textarea>
            <button type="button" id="chatSendBtn" class="chat-send" aria-label="Send message">
                <i class="ti ti-send"></i>
            </button>
        </div>
    </div>

    <script type="module" src="<?php echo habitflow_api('firebase-config.php'); ?>"></script>
    <script type="module" src="<?php echo habitflow_asset('js/habits-db.js'); ?>"></script>
    <script>window.HABITFLOW_API = '<?php echo habitflow_api(''); ?>';</script>
    <script type="module">
        const userId = <?php echo json_encode($userId); ?>;
        const username = <?php echo json_encode($username); ?>;

        const aiFeedback = document.getElementById('aiFeedback');
        const alertBox = document.getElementById('alertBox');
        const briefingContainer = document.getElementById('briefingContainer');
        const statTotal = document.getElementById('statTotal');
        const statCompleted = document.getElementById('statCompleted');
        const statStreak = document.getElementById('statStreak');
        const habitCount = document.getElementById('habitCount');
        const loadingState = document.getElementById('loadingState');
        const emptyState = document.getElementById('emptyState');
        const habitsList = document.getElementById('habitsList');
        const addHabitForm = document.getElementById('addHabitForm');
        const toggleFormBtn = document.getElementById('toggleFormBtn');
        const aiGenerateBtn = document.getElementById('aiGenerateBtn');
        const aiGenerateBox = document.getElementById('aiGenerateBox');
        const cancelBtn = document.getElementById('cancelBtn');
        const addBtn = document.getElementById('addBtn');
        const goalInput = document.getElementById('goalInput');
        const generateBtn = document.getElementById('generateBtn');
        const aiGenerateResult = document.getElementById('aiGenerateResult');

        const chatBubble = document.getElementById('chatBubble');
        const chatWindow = document.getElementById('chatWindow');
        const chatCloseBtn = document.getElementById('chatCloseBtn');
        const chatMessages = document.getElementById('chatMessages');
        const chatInput = document.getElementById('chatInput');
        const chatSendBtn = document.getElementById('chatSendBtn');

        let chatHistory = [];
        let cachedHabits = [];

        const CATEGORY_ICONS = {
            health: { icon: 'ti-heart', color: '#D85A30' },
            fitness: { icon: 'ti-barbell', color: '#534AB7' },
            learning: { icon: 'ti-book', color: '#1D9E75' },
            mindfulness: { icon: 'ti-brain', color: '#534AB7' },
            productivity: { icon: 'ti-briefcase', color: '#185FA5' },
            other: { icon: 'ti-star', color: '#BA7517' }
        };

        const DAY_LABELS = { mon: 'Mon', tue: 'Tue', wed: 'Wed', thu: 'Thu', fri: 'Fri', sat: 'Sat', sun: 'Sun' };

        let selectedFrequency = 'daily';

        function showAlert(message, type = 'error') {
            const icon = type === 'error' ? 'ti-alert-circle' : 'ti-check';
            alertBox.innerHTML = `<div class="alert alert-${type}"><i class="ti ${icon}"></i>${message}</div>`;
            setTimeout(() => { alertBox.innerHTML = ''; }, 4000);
        }

        function showAIMessage(message) {
            aiFeedback.innerHTML = `
                <div class="ai-feedback-banner">
                    <i class="ti ti-sparkles"></i>
                    <div>
                        <div class="ai-label">AI Coach</div>
                        <div class="ai-text">${escapeHtml(message)}</div>
                    </div>
                </div>
            `;
        }

        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function formatTime(time24) {
            if (!time24) return '';
            const [hours, minutes] = time24.split(':');
            const h = parseInt(hours);
            const period = h >= 12 ? 'PM' : 'AM';
            const displayHour = h === 0 ? 12 : h > 12 ? h - 12 : h;
            return `${displayHour}:${minutes} ${period}`;
        }

        function formatDays(days) {
            if (!days || days.length === 7) return 'Every day';
            const order = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];
            const sorted = days.slice().sort((a, b) => order.indexOf(a) - order.indexOf(b));
            return sorted.map(d => DAY_LABELS[d]).join(', ');
        }

        function habitsToSimpleList(habits) {
            return habits.map(h => ({
                name: h.habitName,
                category: h.category,
                streak: h.currentStreak
            }));
        }

        async function loadDailyBriefing(habits) {
            try {
                const habitSummary = habitsToSimpleList(habits.slice(0, 5));
                const response = await fetch(window.HABITFLOW_API + 'ai-api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'daily_briefing',
                        username: username,
                        habits: habitSummary
                    })
                });
                const data = await response.json();
                if (data.success && data.message) {
                    briefingContainer.innerHTML = `
                        <div class="briefing-banner">
                            <i class="ti ti-sparkles"></i>
                            <div>
                                <div class="briefing-label">Daily briefing</div>
                                <div class="briefing-text">${escapeHtml(data.message)}</div>
                            </div>
                            <button class="briefing-close" onclick="this.parentElement.remove()" aria-label="Close">
                                <i class="ti ti-x"></i>
                            </button>
                        </div>
                    `;
                }
            } catch (error) {
                console.warn('Briefing error:', error);
            }
        }

        async function getAIMessage(habitName, streak) {
            try {
                const response = await fetch(window.HABITFLOW_API + 'ai-api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'motivation', habitName, currentStreak: streak })
                });
                const data = await response.json();
                if (data.success && data.message) return data.message;
            } catch (error) {
                console.warn('Qwen API error, using fallback:', error);
            }
            const fallbacks = [
                `Amazing work on "${habitName}"! ${streak} days strong!`,
                `Great job! Your ${streak}-day streak is impressive!`,
                `You're building something incredible with "${habitName}"!`
            ];
            return fallbacks[Math.floor(Math.random() * fallbacks.length)];
        }

        function renderHabits(habits) {
            cachedHabits = habits;
            loadingState.style.display = 'none';

            if (habits.length === 0) {
                emptyState.style.display = 'block';
                habitsList.style.display = 'none';
                return;
            }

            emptyState.style.display = 'none';
            habitsList.style.display = 'flex';

            habitsList.innerHTML = habits.map(habit => {
                const cat = CATEGORY_ICONS[habit.category] || CATEGORY_ICONS.other;
                const scheduledClass = habit.scheduledToday ? '' : 'not-today';
                const completedClass = habit.todayCompleted ? 'completed' : '';

                const pills = [];
                pills.push(`<span class="meta-pill streak"><i class="ti ti-flame"></i>${habit.currentStreak} days</span>`);

                if (habit.frequency === 'daily') {
                    pills.push(`<span class="meta-pill schedule"><i class="ti ti-calendar"></i>Daily</span>`);
                } else if (habit.frequency === 'specific') {
                    pills.push(`<span class="meta-pill schedule"><i class="ti ti-calendar"></i>${formatDays(habit.scheduledDays)}</span>`);
                } else if (habit.frequency === 'weekly') {
                    pills.push(`<span class="meta-pill schedule"><i class="ti ti-repeat"></i>${habit.timesPerWeek}x/week</span>`);
                }

                if (habit.time) pills.push(`<span class="meta-pill time"><i class="ti ti-clock"></i>${formatTime(habit.time)}</span>`);
                if (habit.duration) pills.push(`<span class="meta-pill duration"><i class="ti ti-hourglass"></i>${habit.duration} min</span>`);
                if (!habit.scheduledToday) pills.push(`<span class="meta-pill not-scheduled"><i class="ti ti-calendar-off"></i>Not today</span>`);
                if (habit.todayCompleted) pills.push(`<span class="meta-pill done"><i class="ti ti-check"></i>Done today</span>`);

                const checkBtn = habit.todayCompleted
                    ? `<div class="check-circle done"><i class="ti ti-check"></i></div>`
                    : habit.scheduledToday
                        ? `<button class="check-circle" data-action="complete" data-habit-id="${habit.id}" aria-label="Mark complete"><i class="ti ti-circle"></i></button>`
                        : `<div class="check-circle disabled" title="Not scheduled for today"><i class="ti ti-circle-dashed"></i></div>`;

                return `
                    <div class="habit-card ${completedClass} ${scheduledClass}" data-habit-id="${habit.id}">
                        <div class="habit-content">
                            <div class="habit-check-wrapper">${checkBtn}</div>
                            <div class="habit-info">
                                <div class="habit-name">
                                    <i class="ti ${cat.icon} category-icon" style="color: ${cat.color};"></i>
                                    ${escapeHtml(habit.habitName)}
                                </div>
                                <div class="habit-meta">${pills.join('')}</div>
                            </div>
                        </div>
                        <button class="btn-icon" data-action="delete" data-habit-id="${habit.id}" aria-label="Delete habit">
                            <i class="ti ti-trash"></i>
                        </button>
                    </div>
                `;
            }).join('');

            habitsList.querySelectorAll('[data-action="complete"]').forEach(btn => {
                btn.addEventListener('click', () => handleComplete(btn.dataset.habitId));
            });
            habitsList.querySelectorAll('[data-action="delete"]').forEach(btn => {
                btn.addEventListener('click', () => handleDelete(btn.dataset.habitId));
            });
        }

        function updateStats(habits) {
            const scheduledToday = habits.filter(h => h.scheduledToday);
            statTotal.textContent = habits.length;
            statCompleted.textContent = `${scheduledToday.filter(h => h.todayCompleted).length}/${scheduledToday.length}`;
            statStreak.textContent = habits.reduce((sum, h) => sum + (h.currentStreak || 0), 0);
            habitCount.textContent = `${habits.length} total`;
        }

        async function loadHabits(showBriefing = false) {
            try {
                const habits = await window.habitDB.getUserHabits(userId);
                renderHabits(habits);
                updateStats(habits);
                if (showBriefing) loadDailyBriefing(habits);
            } catch (error) {
                console.error('Load error:', error);
                loadingState.style.display = 'none';
                showAlert('Failed to load habits. Please refresh the page.');
            }
        }

        function closeAllForms() {
            addHabitForm.style.display = 'none';
            aiGenerateBox.style.display = 'none';
            aiGenerateResult.innerHTML = '';
            document.getElementById('toggleIcon').className = 'ti ti-plus';
            document.getElementById('toggleText').textContent = 'Manual';
            addHabitForm.reset();
            document.querySelectorAll('.freq-btn').forEach(b => b.classList.toggle('active', b.dataset.freq === 'daily'));
            selectedFrequency = 'daily';
            document.getElementById('daysField').style.display = 'none';
            document.getElementById('weeklyField').style.display = 'none';
        }

        toggleFormBtn.addEventListener('click', () => {
            const isOpen = addHabitForm.style.display !== 'none';
            closeAllForms();
            if (!isOpen) {
                addHabitForm.style.display = 'flex';
                document.getElementById('toggleIcon').className = 'ti ti-x';
                document.getElementById('toggleText').textContent = 'Close';
            }
        });

        aiGenerateBtn.addEventListener('click', () => {
            const isOpen = aiGenerateBox.style.display !== 'none';
            closeAllForms();
            if (!isOpen) {
                aiGenerateBox.style.display = 'block';
                goalInput.focus();
            }
        });

        cancelBtn.addEventListener('click', closeAllForms);

        document.querySelectorAll('.freq-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.freq-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                selectedFrequency = btn.dataset.freq;
                document.getElementById('daysField').style.display = selectedFrequency === 'specific' ? 'block' : 'none';
                document.getElementById('weeklyField').style.display = selectedFrequency === 'weekly' ? 'block' : 'none';
            });
        });

        document.querySelectorAll('.suggestion-chip').forEach(chip => {
            chip.addEventListener('click', () => {
                goalInput.value = chip.dataset.goal;
                goalInput.focus();
            });
        });

        async function handleGenerateHabit() {
            const goal = goalInput.value.trim();
            if (!goal) {
                showAlert('Please describe your goal');
                return;
            }

            generateBtn.disabled = true;
            generateBtn.innerHTML = '<i class="ti ti-loader" style="animation: spin 1s linear infinite;"></i> Thinking...';
            aiGenerateResult.innerHTML = '<div class="ai-thinking">Flow is creating a habit for you...</div>';

            try {
                const existingHabits = cachedHabits.map(h => h.habitName);
                const response = await fetch(window.HABITFLOW_API + 'ai-api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'generate_habit',
                        goal: goal,
                        existingHabits: existingHabits
                    })
                });
                const data = await response.json();

                if (!data.success) {
                    aiGenerateResult.innerHTML = `<div class="alert alert-error"><i class="ti ti-alert-circle"></i>${escapeHtml(data.error || 'Failed to generate')}</div>`;
                    return;
                }

                const h = data.habit;
                const cat = CATEGORY_ICONS[h.category] || CATEGORY_ICONS.other;

                const pills = [];
                if (h.frequency === 'daily') pills.push(`<span class="meta-pill schedule"><i class="ti ti-calendar"></i>Daily</span>`);
                else if (h.frequency === 'specific') pills.push(`<span class="meta-pill schedule"><i class="ti ti-calendar"></i>${formatDays(h.scheduledDays)}</span>`);
                else if (h.frequency === 'weekly') pills.push(`<span class="meta-pill schedule"><i class="ti ti-repeat"></i>${h.timesPerWeek}x/week</span>`);
                if (h.time) pills.push(`<span class="meta-pill time"><i class="ti ti-clock"></i>${formatTime(h.time)}</span>`);
                if (h.duration) pills.push(`<span class="meta-pill duration"><i class="ti ti-hourglass"></i>${h.duration} min</span>`);

                aiGenerateResult.innerHTML = `
                    <div class="generated-habit">
                        <div class="generated-header">
                            <i class="ti ti-sparkles"></i>
                            <span>Flow suggests:</span>
                        </div>
                        <div class="generated-card">
                            <div class="generated-name">
                                <i class="ti ${cat.icon}" style="color: ${cat.color}; font-size: 22px;"></i>
                                <span>${escapeHtml(h.habitName)}</span>
                            </div>
                            <div class="generated-pills">${pills.join('')}</div>
                            ${h.reasoning ? `<div class="generated-reasoning"><i class="ti ti-bulb"></i>${escapeHtml(h.reasoning)}</div>` : ''}
                            <div class="generated-actions">
                                <button type="button" id="rejectHabitBtn" class="btn btn-outline btn-small">Discard</button>
                                <button type="button" id="acceptHabitBtn" class="btn btn-primary btn-small">
                                    <i class="ti ti-check"></i>
                                    Add this habit
                                </button>
                            </div>
                        </div>
                    </div>
                `;

                document.getElementById('acceptHabitBtn').addEventListener('click', () => acceptGeneratedHabit(h));
                document.getElementById('rejectHabitBtn').addEventListener('click', () => {
                    aiGenerateResult.innerHTML = '';
                    goalInput.value = '';
                    goalInput.focus();
                });
            } catch (error) {
                console.error('Generate error:', error);
                aiGenerateResult.innerHTML = '<div class="alert alert-error"><i class="ti ti-alert-circle"></i>Failed to connect to AI</div>';
            } finally {
                generateBtn.disabled = false;
                generateBtn.innerHTML = '<i class="ti ti-wand"></i> Generate';
            }
        }

        async function acceptGeneratedHabit(habit) {
            try {
                await window.habitDB.addHabit(userId, {
                    habitName: habit.habitName,
                    category: habit.category,
                    frequency: habit.frequency,
                    scheduledDays: habit.scheduledDays,
                    timesPerWeek: habit.timesPerWeek,
                    time: habit.time,
                    duration: habit.duration
                });
                showAlert('AI-generated habit added!', 'success');
                closeAllForms();
                goalInput.value = '';
                await loadHabits();
            } catch (error) {
                console.error('Accept error:', error);
                showAlert('Failed to add habit. Please try again.');
            }
        }

        generateBtn.addEventListener('click', handleGenerateHabit);
        goalInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                handleGenerateHabit();
            }
        });

        async function handleAddHabit(e) {
            e.preventDefault();
            const habitName = document.getElementById('habitName').value.trim();
            const category = document.querySelector('input[name="category"]:checked')?.value;

            if (!habitName) { showAlert('Please enter a habit name'); return; }
            if (!category) { showAlert('Please select a category'); return; }

            let scheduledDays = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];
            let timesPerWeek = null;

            if (selectedFrequency === 'specific') {
                scheduledDays = Array.from(document.querySelectorAll('input[name="day"]:checked')).map(c => c.value);
                if (scheduledDays.length === 0) { showAlert('Please select at least one day'); return; }
            } else if (selectedFrequency === 'weekly') {
                timesPerWeek = parseInt(document.getElementById('timesPerWeek').value) || 3;
            }

            const time = document.getElementById('habitTime').value || null;
            const duration = parseInt(document.getElementById('habitDuration').value) || null;

            addBtn.disabled = true;
            addBtn.innerHTML = '<i class="ti ti-loader" style="animation: spin 1s linear infinite;"></i> Adding...';

            try {
                await window.habitDB.addHabit(userId, { habitName, category, frequency: selectedFrequency, scheduledDays, timesPerWeek, time, duration });
                showAlert('Habit added successfully!', 'success');
                closeAllForms();
                await loadHabits();
            } catch (error) {
                console.error('Add habit error:', error);
                showAlert('Failed to add habit. Please try again.');
            } finally {
                addBtn.disabled = false;
                addBtn.innerHTML = '<i class="ti ti-plus"></i> Add habit';
            }
        }

        async function handleComplete(habitId) {
            try {
                const result = await window.habitDB.logHabitCompletion(habitId, userId);
                if (result.alreadyCompleted) {
                    showAlert('You already completed this habit today!', 'success');
                    return;
                }
                const aiMessage = await getAIMessage(result.habitName, result.currentStreak);
                showAIMessage(aiMessage);
                await loadHabits();
            } catch (error) {
                console.error('Complete error:', error);
                showAlert('Failed to mark complete. Please try again.');
            }
        }

        async function handleDelete(habitId) {
            if (!confirm('Are you sure you want to delete this habit?')) return;
            try {
                await window.habitDB.deleteHabit(habitId);
                showAlert('Habit deleted', 'success');
                await loadHabits();
            } catch (error) {
                console.error('Delete error:', error);
                showAlert('Failed to delete habit.');
            }
        }

        addHabitForm.addEventListener('submit', handleAddHabit);

        function openChat() {
            chatWindow.style.display = 'flex';
            chatBubble.classList.add('hidden');
            setTimeout(() => chatInput.focus(), 100);
        }

        function closeChat() {
            chatWindow.style.display = 'none';
            chatBubble.classList.remove('hidden');
        }

        function addChatMessage(content, role = 'ai') {
            const div = document.createElement('div');
            div.className = `chat-message ${role}`;

            if (role === 'ai') {
                div.innerHTML = `
                    <div class="chat-avatar-small"><i class="ti ti-sparkles"></i></div>
                    <div class="chat-bubble-content">${escapeHtml(content)}</div>
                `;
            } else {
                div.innerHTML = `<div class="chat-bubble-content">${escapeHtml(content)}</div>`;
            }

            chatMessages.appendChild(div);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        function addTypingIndicator() {
            const div = document.createElement('div');
            div.className = 'chat-message ai typing';
            div.id = 'typingIndicator';
            div.innerHTML = `
                <div class="chat-avatar-small"><i class="ti ti-sparkles"></i></div>
                <div class="chat-bubble-content">
                    <span class="typing-dot"></span>
                    <span class="typing-dot"></span>
                    <span class="typing-dot"></span>
                </div>
            `;
            chatMessages.appendChild(div);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        function removeTypingIndicator() {
            const indicator = document.getElementById('typingIndicator');
            if (indicator) indicator.remove();
        }

        async function sendChatMessage() {
            const message = chatInput.value.trim();
            if (!message) return;

            addChatMessage(message, 'user');
            chatInput.value = '';
            chatInput.style.height = 'auto';
            chatSendBtn.disabled = true;

            addTypingIndicator();

            try {
                const response = await fetch(window.HABITFLOW_API + 'ai-api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'chat',
                        message: message,
                        habits: habitsToSimpleList(cachedHabits),
                        history: chatHistory
                    })
                });
                const data = await response.json();

                removeTypingIndicator();

                if (data.success && data.reply) {
                    addChatMessage(data.reply, 'ai');
                    chatHistory.push({ role: 'user', content: message });
                    chatHistory.push({ role: 'assistant', content: data.reply });
                    if (chatHistory.length > 20) chatHistory = chatHistory.slice(-20);
                } else {
                    addChatMessage("I'm having trouble responding. Try again in a moment!", 'ai');
                }
            } catch (error) {
                console.error('Chat error:', error);
                removeTypingIndicator();
                addChatMessage("Connection issue. Please check your internet.", 'ai');
            } finally {
                chatSendBtn.disabled = false;
                chatInput.focus();
            }
        }

        chatBubble.addEventListener('click', openChat);
        chatCloseBtn.addEventListener('click', closeChat);
        chatSendBtn.addEventListener('click', sendChatMessage);

        chatInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendChatMessage();
            }
        });

        chatInput.addEventListener('input', () => {
            chatInput.style.height = 'auto';
            chatInput.style.height = Math.min(chatInput.scrollHeight, 100) + 'px';
        });

        loadHabits(true);
    </script>

    <style>
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
    </style>

</body>
</html>
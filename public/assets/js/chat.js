/**
 * HabitFlow AI Habit Coach Chat
 * Floating chat bubble with Qwen AI integration
 */

class HabitCoachChat {
    constructor(options = {}) {
        this.isOpen = false;
        this.messages = [];
        this.isLoading = false;
        this.unreadCount = 0;
        this.habits = options.habits || [];
        this.userId = options.userId || null;

        this.init();
    }

    init() {
        this.createHTML();
        this.cacheElements();
        this.attachEventListeners();
        this.addWelcomeMessage();
    }

    createHTML() {
        const overlay = document.createElement('div');
        overlay.id = 'chatOverlay';
        overlay.className = 'chat-overlay';
        document.body.appendChild(overlay);

        const chatWindow = document.createElement('div');
        chatWindow.id = 'chatWindow';
        chatWindow.className = 'chat-window';
        chatWindow.innerHTML = `
            <div class="chat-header">
                <div class="chat-header-title">
                    <i class="ti ti-sparkles"></i>
                    <span>Habit Coach</span>
                </div>
                <button class="chat-close-btn" id="chatCloseBtn">
                    <i class="ti ti-x"></i>
                </button>
            </div>
            <div class="chat-messages" id="chatMessages"></div>
            <div class="chat-input-area">
                <textarea
                    class="chat-input"
                    id="chatInput"
                    placeholder="Ask about habits..."
                    rows="1"
                    maxlength="500"
                ></textarea>
                <button class="chat-send-btn" id="chatSendBtn">
                    <i class="ti ti-send"></i>
                </button>
            </div>
        `;
        document.body.appendChild(chatWindow);

        const chatBtn = document.createElement('button');
        chatBtn.id = 'chatBubbleBtn';
        chatBtn.className = 'chat-bubble-btn';
        chatBtn.innerHTML = `
            <i class="ti ti-message-circle-2"></i>
            <span class="chat-bubble-badge" id="chatBadge" style="display: none;">0</span>
        `;
        document.body.appendChild(chatBtn);
    }

    cacheElements() {
        this.chatBubbleBtn = document.getElementById('chatBubbleBtn');
        this.chatWindow    = document.getElementById('chatWindow');
        this.chatOverlay   = document.getElementById('chatOverlay');
        this.chatMessages  = document.getElementById('chatMessages');
        this.chatInput     = document.getElementById('chatInput');
        this.chatSendBtn   = document.getElementById('chatSendBtn');
        this.chatCloseBtn  = document.getElementById('chatCloseBtn');
        this.chatBadge     = document.getElementById('chatBadge');
    }

    attachEventListeners() {
        this.chatBubbleBtn.addEventListener('click', () => this.toggle());
        this.chatCloseBtn.addEventListener('click', () => this.close());
        this.chatOverlay.addEventListener('click', () => this.close());
        this.chatSendBtn.addEventListener('click', () => this.sendMessage());
        this.chatInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                this.sendMessage();
            }
        });

        this.chatInput.addEventListener('input', () => {
            this.chatInput.style.height = 'auto';
            this.chatInput.style.height = Math.min(this.chatInput.scrollHeight, 80) + 'px';
        });

        this.chatWindow.addEventListener('click', () => {
            this.unreadCount = 0;
            this.updateBadge();
        });
    }

    toggle() { this.isOpen ? this.close() : this.open(); }

    open() {
        this.isOpen = true;
        this.chatWindow.classList.add('open');
        this.chatOverlay.classList.add('visible');
        this.chatInput.focus();
        this.unreadCount = 0;
        this.updateBadge();
    }

    close() {
        this.isOpen = false;
        this.chatWindow.classList.remove('open');
        this.chatOverlay.classList.remove('visible');
    }

    addWelcomeMessage() {
        setTimeout(() => {
            const welcome = `Hi! 👋 I'm your Habit Coach. Ask me anything about building better habits!\n\nTry:\n• "How do I build better sleep habits?"\n• "Why do I skip Mondays?"\n• "What habits should I add?"\n• "Motivate me!"`;
            this.addMessage(welcome, 'ai');
        }, 500);
    }

    addMessage(text, sender = 'user') {
        const messageDiv = document.createElement('div');
        messageDiv.className = `chat-message ${sender}`;

        const bubble = document.createElement('div');
        bubble.className = 'chat-message-bubble';
        bubble.textContent = text;

        messageDiv.appendChild(bubble);
        this.chatMessages.appendChild(messageDiv);
        this.scrollToBottom();

        if (sender === 'ai' && !this.isOpen) {
            this.unreadCount++;
            this.updateBadge();
        }

        this.messages.push({ text, sender, timestamp: new Date() });
    }

    addTypingIndicator() {
        const messageDiv = document.createElement('div');
        messageDiv.className = 'chat-message ai';
        messageDiv.id = 'typingIndicator';

        const typing = document.createElement('div');
        typing.className = 'chat-typing';
        typing.innerHTML = `
            <div class="typing-dot"></div>
            <div class="typing-dot"></div>
            <div class="typing-dot"></div>
        `;

        messageDiv.appendChild(typing);
        this.chatMessages.appendChild(messageDiv);
        this.scrollToBottom();
    }

    removeTypingIndicator() {
        const indicator = document.getElementById('typingIndicator');
        if (indicator) indicator.remove();
    }

    scrollToBottom() {
        this.chatMessages.scrollTop = this.chatMessages.scrollHeight;
    }

    updateBadge() {
        if (this.unreadCount > 0) {
            this.chatBadge.textContent = Math.min(this.unreadCount, 9) + (this.unreadCount > 9 ? '+' : '');
            this.chatBadge.style.display = 'flex';
        } else {
            this.chatBadge.style.display = 'none';
        }
    }

    async sendMessage() {
        const message = this.chatInput.value.trim();
        if (!message || this.isLoading) return;

        this.addMessage(message, 'user');
        this.chatInput.value = '';
        this.chatInput.style.height = 'auto';
        this.isLoading = true;
        this.chatSendBtn.disabled = true;
        this.addTypingIndicator();

        try {
            const habitNames = Array.isArray(this.habits)
                ? this.habits.map(h => h.habitName || h.name).filter(Boolean)
                : [];

            const apiBase = window.HABITFLOW_API || '../api/';
            const response = await fetch(apiBase + 'qwen-chat.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message, habits: habitNames })
            });

            const data = await response.json();
            this.removeTypingIndicator();

            if (data.success) {
                this.addMessage(data.message, 'ai');
            } else {
                this.addMessage(data.error || 'Sorry, I encountered an error. Please try again.', 'ai');
            }
        } catch (error) {
            console.error('Chat error:', error);
            this.removeTypingIndicator();
            this.addMessage('Oops! Something went wrong. Please try again.', 'ai');
        } finally {
            this.isLoading = false;
            this.chatSendBtn.disabled = false;
            this.chatInput.focus();
        }
    }

    updateHabits(habits) { this.habits = habits; }
    clearChat() { this.messages = []; this.chatMessages.innerHTML = ''; this.addWelcomeMessage(); }
}

// ─── Auto-init ───────────────────────────────────────────────────────────────
// Waits for habitDB (loaded by habits-db.js module) then boots the chat.
// This avoids the module vs non-module timing race entirely.

function _bootChat() {
    // Grab userId from the data attribute we set on <body>
    const userId = document.body.dataset.userId || null;

    // Try to get habits if habitDB is already ready, otherwise start empty
    // (updateHabits() is called later from Dashboard.php when habits load)
    const habits = [];

    window.habitCoachChat = new HabitCoachChat({ habits, userId });
    console.log('Habit Coach Chat initialized');
}

// Boot as soon as the DOM is ready (chat.js loads at end of <body>)
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', _bootChat);
} else {
    _bootChat();
}

// Keep these for external use
window.HabitCoachChat = HabitCoachChat;
window.initHabitCoachChat = function(options) {
    if (window.habitCoachChat) {
        // Already running — just update with fresh data
        if (options.habits)  window.habitCoachChat.updateHabits(options.habits);
        if (options.userId)  window.habitCoachChat.userId = options.userId;
        return window.habitCoachChat;
    }
    window.habitCoachChat = new HabitCoachChat(options);
    return window.habitCoachChat;
};
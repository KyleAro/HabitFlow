async function waitForFirebase() {
    return new Promise((resolve) => {
        if (window.firebaseDB && window.firebaseAuth) {
            resolve();
        } else {
            const interval = setInterval(() => {
                if (window.firebaseDB && window.firebaseAuth) {
                    clearInterval(interval);
                    resolve();
                }
            }, 50);
        }
    });
}

function getTodayDate() {
    const now = new Date();
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const day = String(now.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

function getYesterdayDate() {
    const now = new Date();
    now.setDate(now.getDate() - 1);
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const day = String(now.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

function getTodayDayName() {
    const days = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'];
    return days[new Date().getDay()];
}

function isScheduledForToday(habit) {
    if (!habit.frequency || habit.frequency === 'daily') return true;
    if (habit.frequency === 'specific') {
        const today = getTodayDayName();
        return habit.scheduledDays && habit.scheduledDays.includes(today);
    }
    if (habit.frequency === 'weekly') {
        return true;
    }
    return true;
}

async function addHabit(userId, habitData) {
    await waitForFirebase();
    const db = window.firebaseDB;

    const data = {
        userId: userId,
        habitName: String(habitData.habitName || '').trim(),
        category: habitData.category || 'other',
        frequency: habitData.frequency || 'daily',
        scheduledDays: habitData.scheduledDays || ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'],
        timesPerWeek: habitData.timesPerWeek || null,
        time: habitData.time || null,
        duration: habitData.duration || null,
        currentStreak: 0,
        longestStreak: 0,
        lastCompletedDate: null,
        isActive: true,
        createdAt: window.firestoreServerTimestamp()
    };

    const habitsRef = window.firestoreCollection(db, 'habits');
    const docRef = await window.firestoreAddDoc(habitsRef, data);

    return {
        id: docRef.id,
        ...data,
        todayCompleted: false
    };
}

async function getUserHabits(userId) {
    await waitForFirebase();
    const db = window.firebaseDB;

    const habitsRef = window.firestoreCollection(db, 'habits');
    const q = window.firestoreQuery(
        habitsRef,
        window.firestoreWhere('userId', '==', userId),
        window.firestoreWhere('isActive', '==', true)
    );

    const snapshot = await window.firestoreGetDocs(q);
    const habits = [];

    snapshot.forEach((docSnap) => {
        const data = docSnap.data();
        habits.push({
            id: docSnap.id,
            habitName: data.habitName,
            category: data.category || 'other',
            frequency: data.frequency || 'daily',
            scheduledDays: data.scheduledDays || ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'],
            timesPerWeek: data.timesPerWeek || null,
            time: data.time || null,
            duration: data.duration || null,
            currentStreak: data.currentStreak || 0,
            longestStreak: data.longestStreak || 0,
            lastCompletedDate: data.lastCompletedDate,
            todayCompleted: data.lastCompletedDate === getTodayDate(),
            scheduledToday: false,
            createdAt: data.createdAt
        });
    });

    habits.forEach(h => {
        h.scheduledToday = isScheduledForToday(h);
    });

    habits.sort((a, b) => {
        if (a.scheduledToday !== b.scheduledToday) {
            return a.scheduledToday ? -1 : 1;
        }
        if (a.time && b.time) {
            return a.time.localeCompare(b.time);
        }
        if (a.time) return -1;
        if (b.time) return 1;
        const aTime = a.createdAt?.seconds || 0;
        const bTime = b.createdAt?.seconds || 0;
        return bTime - aTime;
    });

    return habits;
}

async function deleteHabit(habitId) {
    await waitForFirebase();
    const db = window.firebaseDB;

    const habitRef = window.firestoreDoc(db, 'habits', habitId);
    await window.firestoreUpdateDoc(habitRef, { isActive: false });

    return true;
}

async function logHabitCompletion(habitId, userId) {
    await waitForFirebase();
    const db = window.firebaseDB;

    const habitRef = window.firestoreDoc(db, 'habits', habitId);
    const habitSnap = await window.firestoreGetDoc(habitRef);

    if (!habitSnap.exists()) {
        throw new Error('Habit not found');
    }

    const habitData = habitSnap.data();
    const today = getTodayDate();
    const yesterday = getYesterdayDate();

    if (habitData.lastCompletedDate === today) {
        return {
            alreadyCompleted: true,
            habitName: habitData.habitName,
            currentStreak: habitData.currentStreak
        };
    }

    let newStreak = 1;
    if (habitData.lastCompletedDate === yesterday) {
        newStreak = (habitData.currentStreak || 0) + 1;
    }

    const newLongest = Math.max(habitData.longestStreak || 0, newStreak);

    await window.firestoreUpdateDoc(habitRef, {
        currentStreak: newStreak,
        longestStreak: newLongest,
        lastCompletedDate: today
    });

    const logsRef = window.firestoreCollection(db, 'dailyLogs');
    await window.firestoreAddDoc(logsRef, {
        userId: userId,
        habitId: habitId,
        logDate: today,
        completed: true,
        createdAt: window.firestoreServerTimestamp()
    });

    return {
        alreadyCompleted: false,
        habitName: habitData.habitName,
        currentStreak: newStreak,
        longestStreak: newLongest
    };
}

window.habitDB = {
    addHabit,
    getUserHabits,
    deleteHabit,
    logHabitCompletion,
    getTodayDate,
    getTodayDayName,
    isScheduledForToday
};

console.log("Habit DB module loaded (with scheduling)");
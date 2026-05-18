/**
 * Optional static fallback — copy to firebase-config.js if not using api/firebase-config.php.
 * Recommended: set FIREBASE_* vars in .env (see .env.example); pages load habitflow_api('firebase-config.php').
 */
const firebaseConfig = {
    apiKey: "YOUR_FIREBASE_API_KEY",
    authDomain: "your-project.firebaseapp.com",
    projectId: "your-project-id",
    storageBucket: "your-project.firebasestorage.app",
    messagingSenderId: "000000000000",
    appId: "1:000000000000:web:xxxxxxxxxxxxxxxx",
    measurementId: "G-XXXXXXXXXX"
};

// Same imports/exports as api/firebase-config.php — copy from that file if using this approach.

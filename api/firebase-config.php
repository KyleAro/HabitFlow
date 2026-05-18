<?php
/**
 * Serves Firebase client config as an ES module (values from .env, never committed).
 */
require_once __DIR__ . '/../includes/bootstrap.php';
habitflow_load_env();

header('Content-Type: application/javascript; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');

$env = static function (string $key): string {
    $v = getenv($key);
    return ($v !== false && $v !== '') ? $v : '';
};

$config = [
    'apiKey'            => $env('FIREBASE_API_KEY'),
    'authDomain'        => $env('FIREBASE_AUTH_DOMAIN'),
    'projectId'         => $env('FIREBASE_PROJECT_ID'),
    'storageBucket'     => $env('FIREBASE_STORAGE_BUCKET'),
    'messagingSenderId' => $env('FIREBASE_MESSAGING_SENDER_ID'),
    'appId'             => $env('FIREBASE_APP_ID'),
    'measurementId'     => $env('FIREBASE_MEASUREMENT_ID'),
];

$configJson = json_encode($config, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
?>
const firebaseConfig = <?= $configJson ?>;

import { initializeApp } from "https://www.gstatic.com/firebasejs/10.7.1/firebase-app.js";
import {
    getAuth,
    createUserWithEmailAndPassword,
    signInWithEmailAndPassword,
    signOut,
    onAuthStateChanged,
    updateProfile
} from "https://www.gstatic.com/firebasejs/10.7.1/firebase-auth.js";
import {
    getFirestore,
    collection,
    doc,
    addDoc,
    setDoc,
    getDoc,
    getDocs,
    updateDoc,
    deleteDoc,
    query,
    where,
    orderBy,
    serverTimestamp,
    Timestamp
} from "https://www.gstatic.com/firebasejs/10.7.1/firebase-firestore.js";

const app = initializeApp(firebaseConfig);
const auth = getAuth(app);
const db = getFirestore(app);

window.firebaseAuth = auth;
window.firebaseDB = db;

window.firebaseCreateUser = createUserWithEmailAndPassword;
window.firebaseSignIn = signInWithEmailAndPassword;
window.firebaseSignOut = signOut;
window.firebaseOnAuthChange = onAuthStateChanged;
window.firebaseUpdateProfile = updateProfile;

window.firestoreCollection = collection;
window.firestoreDoc = doc;
window.firestoreAddDoc = addDoc;
window.firestoreSetDoc = setDoc;
window.firestoreGetDoc = getDoc;
window.firestoreGetDocs = getDocs;
window.firestoreUpdateDoc = updateDoc;
window.firestoreDeleteDoc = deleteDoc;
window.firestoreQuery = query;
window.firestoreWhere = where;
window.firestoreOrderBy = orderBy;
window.firestoreServerTimestamp = serverTimestamp;
window.firestoreTimestamp = Timestamp;

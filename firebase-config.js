const firebaseConfig = {
    apiKey: "AIzaSyCy9fTdbqG4k34bBPHbxV2rxxWViP8ixTA",
    authDomain: "habit-7e279.firebaseapp.com",
    projectId: "habit-7e279",
    storageBucket: "habit-7e279.firebasestorage.app",
    messagingSenderId: "964989177103",
    appId: "1:964989177103:web:c0f0cd3a569d7f5366427a",
    measurementId: "G-CRJ9MY3B01"
};

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

console.log("Firebase + Firestore initialized");
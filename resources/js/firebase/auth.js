// Firebase configuration - thay thế với config thực tế của bạn từ Firebase Console
const firebaseConfig = {
    apiKey: "your-api-key",
    authDomain: "your-project.firebaseapp.com",
    projectId: "your-project-id",
    storageBucket: "your-project.appspot.com",
    messagingSenderId: "your-sender-id",
    appId: "your-app-id"
};

// Import Firebase modules
import { initializeApp } from 'firebase/app';
import { 
    getAuth, 
    GoogleAuthProvider, 
    signInWithPopup, 
    signOut,
    onAuthStateChanged 
} from 'firebase/auth';

// Initialize Firebase
const app = initializeApp(firebaseConfig);
const auth = getAuth(app);
const googleProvider = new GoogleAuthProvider();

// Configure Google Provider
googleProvider.addScope('profile');
googleProvider.addScope('email');

export class FirebaseAuthService {
    constructor() {
        this.auth = auth;
        this.googleProvider = googleProvider;
    }

    /**
     * Sign in with Google
     */
    async signInWithGoogle() {
        try {
            const result = await signInWithPopup(this.auth, this.googleProvider);
            const user = result.user;
            
            // Get Firebase ID token
            const idToken = await user.getIdToken();
            
            // Send token to Laravel backend
            return await this.sendTokenToBackend(idToken);
            
        } catch (error) {
            console.error('Google sign-in error:', error);
            throw error;
        }
    }

    /**
     * Send Firebase token to Laravel backend
     */
    async sendTokenToBackend(firebaseToken) {
        try {
            const response = await fetch('/api/auth/google', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    firebase_token: firebaseToken
                })
            });

            const data = await response.json();
            
            if (data.status) {
                // Lưu Laravel token vào localStorage hoặc sessionStorage
                localStorage.setItem('auth_token', data.data.token);
                localStorage.setItem('user', JSON.stringify(data.data.user));
                
                return data;
            } else {
                throw new Error(data.message || 'Authentication failed');
            }
            
        } catch (error) {
            console.error('Backend authentication error:', error);
            throw error;
        }
    }

    /**
     * Sign out
     */
    async signOut() {
        try {
            // Sign out from Firebase
            await signOut(this.auth);
            
            // Clear Laravel token
            const token = localStorage.getItem('auth_token');
            if (token) {
                await fetch('/api/logout', {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json',
                    }
                });
            }
            
            // Clear local storage
            localStorage.removeItem('auth_token');
            localStorage.removeItem('user');
            
        } catch (error) {
            console.error('Sign-out error:', error);
            throw error;
        }
    }

    /**
     * Get current user
     */
    getCurrentUser() {
        return this.auth.currentUser;
    }

    /**
     * Listen to auth state changes
     */
    onAuthStateChanged(callback) {
        return onAuthStateChanged(this.auth, callback);
    }

    /**
     * Get stored Laravel token
     */
    getStoredToken() {
        return localStorage.getItem('auth_token');
    }

    /**
     * Get stored user data
     */
    getStoredUser() {
        const user = localStorage.getItem('user');
        return user ? JSON.parse(user) : null;
    }

    /**
     * Check if user is authenticated
     */
    isAuthenticated() {
        return !!this.getStoredToken();
    }
}

// Export singleton instance
export const firebaseAuthService = new FirebaseAuthService();
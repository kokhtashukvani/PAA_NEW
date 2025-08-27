import { defineStore } from 'pinia';
import apiClient from '@/services/api';
import router from '@/router';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    // Initialize state from localStorage to enable session persistence
    token: localStorage.getItem('token') || null,
    user: JSON.parse(localStorage.getItem('user')) || null,
  }),
  getters: {
    isAuthenticated: (state) => !!state.token,
    userRole: (state) => (state.user ? state.user.role : null),
  },
  actions: {
    async login(credentials) {
      try {
        const response = await apiClient.post('/login.php', credentials);
        const { token, user } = response.data;

        // Update state
        this.token = token;
        this.user = user;

        // Store in localStorage
        localStorage.setItem('token', token);
        localStorage.setItem('user', JSON.stringify(user));

        // Redirect to home page after login
        router.push('/');
      } catch (error) {
        // Let the component handle the error display
        throw error;
      }
    },
    async register(credentials) {
        try {
            // We don't automatically log in the user after registration
            // They will be redirected to the login page to sign in
            await apiClient.post('/register.php', credentials);
            router.push('/login');
        } catch (error) {
            throw error;
        }
    },
    logout() {
      // Clear state
      this.token = null;
      this.user = null;

      // Clear localStorage
      localStorage.removeItem('token');
      localStorage.removeItem('user');

      // Redirect to login page
      router.push('/login');
    },
    async fetchUser() {
      if (!this.token) {
        return; // No token, no need to fetch
      }
      try {
        const response = await apiClient.get('/user.php');
        this.user = response.data;
        // Also update localStorage if needed
        localStorage.setItem('user', JSON.stringify(this.user));
      } catch (error) {
        // If token is invalid (e.g., expired), log the user out
        console.error('Failed to fetch user, token might be invalid.', error);
        this.logout();
      }
    },
  },
});

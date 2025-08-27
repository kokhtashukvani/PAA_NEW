import axios from 'axios';
import { useAuthStore } from '@/stores/auth';

// Create a new Axios instance with a base URL
const apiClient = axios.create({
  baseURL: 'http://localhost/api', // Adjust this to your actual API URL
  headers: {
    'Content-Type': 'application/json',
  },
});

// Add a request interceptor to include the JWT token in the headers
apiClient.interceptors.request.use(
  (config) => {
    const authStore = useAuthStore();
    const token = authStore.token;
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

export default apiClient;

import axios from 'axios';

// Create axios instance with base URL
// Assuming Laravel is running on port 8000
const API_URL = 'http://localhost:8000/api';

const axiosInstance = axios.create({
    baseURL: API_URL,
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    },
    withCredentials: true // Important for Sanctum/Cookies
});

// Interceptor to add auth token to requests
axiosInstance.interceptors.request.use(
    (config) => {
        const token = localStorage.getItem('auth_token');
        if (token) {
            config.headers['Authorization'] = `Bearer ${token}`;
        }
        return config;
    },
    (error) => {
        return Promise.reject(error);
    }
);

const authService = {
    // Register
    register: async (userData) => {
        try {
            const response = await axiosInstance.post('/auth/register', userData);
            if (response.data.data.token) {
                localStorage.setItem('auth_token', response.data.data.token);
            }
            return response.data;
        } catch (error) {
            throw error.response?.data || error.message;
        }
    },

    // Login
    login: async (email, password) => {
        try {
            const response = await axiosInstance.post('/auth/login', { email, password });
            if (response.data.data.token) {
                localStorage.setItem('auth_token', response.data.data.token);
            }
            return response.data;
        } catch (error) {
            throw error.response?.data || error.message;
        }
    },

    // Logout
    logout: async () => {
        try {
            await axiosInstance.post('/auth/logout');
        } catch (error) {
            console.error('Logout error:', error);
        } finally {
            localStorage.removeItem('auth_token');
        }
    },

    // Get current user profile
    getProfile: async () => {
        try {
            const response = await axiosInstance.get('/user/profile');
            return response.data;
        } catch (error) {
            throw error.response?.data || error.message;
        }
    }
};

export default authService;

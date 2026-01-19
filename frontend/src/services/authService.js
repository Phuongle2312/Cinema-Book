import api from './api';

const authService = {
    // Register
    register: async (userData) => {
        try {
            const response = await api.post('/auth/register', userData);
            if (response.data.success && response.data.data.token) {
                sessionStorage.setItem('auth_token', response.data.data.token);
                sessionStorage.setItem('user', JSON.stringify(response.data.data.user));
            }
            return response.data;
        } catch (error) {
            console.error('Register error:', error);
            return {
                success: false,
                message: error.response?.data?.message || 'Đăng ký thất bại',
                errors: error.response?.data?.errors || {}
            };
        }
    },

    // Login
    login: async (email, password) => {
        try {
            const response = await api.post('/auth/login', { email, password });
            if (response.data.success && response.data.data.token) {
                sessionStorage.setItem('auth_token', response.data.data.token);
                sessionStorage.setItem('user', JSON.stringify(response.data.data.user));
            }
            return response.data;
        } catch (error) {
            console.error('Login error:', error);
            return {
                success: false,
                message: error.response?.data?.message || 'Đăng nhập thất bại',
                errors: error.response?.data?.errors || {}
            };
        }
    },

    // Logout
    logout: async () => {
        try {
            await api.post('/logout');
        } catch (error) {
            console.error('Logout error:', error);
        } finally {
            // Always clear local storage
            sessionStorage.removeItem('auth_token');
            sessionStorage.removeItem('user');
        }
    },

    // Get current user profile
    getProfile: async () => {
        try {
            const response = await api.get('/user/profile');
            return response.data;
        } catch (error) {
            console.error('Get profile error:', error);
            return {
                success: false,
                message: error.response?.data?.message || 'Không thể lấy thông tin người dùng'
            };
        }
    },

    // Update user profile
    updateProfile: async (userData) => {
        try {
            let response;
            // Check if userData is FormData (for file upload)
            if (userData instanceof FormData) {
                userData.append('_method', 'PUT');
                response = await api.post('/user/profile', userData, {
                    headers: {
                        'Content-Type': 'multipart/form-data',
                    },
                });
            } else {
                response = await api.put('/user/profile', userData);
            }

            if (response.data.success && response.data.data) {
                sessionStorage.setItem('user', JSON.stringify(response.data.data));
            }
            return response.data;
        } catch (error) {
            console.error('Update profile error:', error);
            return {
                success: false,
                message: error.response?.data?.message || 'Cập nhật thông tin thất bại',
                errors: error.response?.data?.errors || {}
            };
        }
    },

    // Check if user is authenticated
    isAuthenticated: () => {
        return !!sessionStorage.getItem('auth_token');
    },

    // Get current user from sessionStorage
    getCurrentUser: () => {
        const userStr = sessionStorage.getItem('user');
        return userStr ? JSON.parse(userStr) : null;
    }
};

export default authService;

const authService = {
    // Register
    register: async (userData) => {
        localStorage.setItem('auth_token', 'mock_token');
        return {
            success: true,
            data: {
                user: { id: 1, name: userData.name, email: userData.email },
                token: 'mock_token'
            }
        };
    },

    // Login
    login: async (email, password) => {
        localStorage.setItem('auth_token', 'mock_token');
        return {
            success: true,
            data: {
                user: { id: 1, name: "Mock User", email: email },
                token: 'mock_token'
            }
        };
    },

    // Logout
    logout: async () => {
        localStorage.removeItem('auth_token');
    },

    // Get current user profile
    getProfile: async () => {
        return {
            success: true,
            data: { id: 1, name: "Mock User", email: "mock@example.com" }
        };
    }
};

export default authService;

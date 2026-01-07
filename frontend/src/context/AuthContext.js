import React, { createContext, useState, useEffect, useContext } from 'react';
import authService from '../services/authService';

const AuthContext = createContext(null);

export const AuthProvider = ({ children }) => {
    const [user, setUser] = useState(null);
    const [isLoading, setIsLoading] = useState(true);
    const [isAuthenticated, setIsAuthenticated] = useState(false);

    useEffect(() => {
        const checkAuth = async () => {
            const token = localStorage.getItem('auth_token');
            if (token) {
                try {
                    const userData = await authService.getProfile();
                    setUser(userData.data);
                    setIsAuthenticated(true);
                } catch (error) {
                    console.error('Auth verification failed:', error);
                    localStorage.removeItem('auth_token');
                    setUser(null);
                    setIsAuthenticated(false);
                }
            }
            setIsLoading(false);
        };

        checkAuth();
    }, []);

    const login = async (email, password) => {
        const response = await authService.login(email, password);
        if (response.success && response.data) {
            setUser(response.data.user);
            setIsAuthenticated(true);
            return response;
        } else {
            throw response; // Throw error response to be caught in component
        }
    };

    const register = async (userData) => {
        const response = await authService.register(userData);
        if (response.success && response.data) {
            setUser(response.data.user);
            setIsAuthenticated(true);
            return response;
        } else {
            throw response; // Throw error response to be caught in component
        }
    };

    const logout = async () => {
        await authService.logout();
        setUser(null);
        setIsAuthenticated(false);
    };

    const value = {
        user,
        isAuthenticated,
        isLoading,
        login,
        register,
        logout
    };

    return (
        <AuthContext.Provider value={value}>
            {!isLoading && children}
        </AuthContext.Provider>
    );
};

export const useAuth = () => {
    const context = useContext(AuthContext);
    if (!context) {
        throw new Error('useAuth must be used within an AuthProvider');
    }
    return context;
};

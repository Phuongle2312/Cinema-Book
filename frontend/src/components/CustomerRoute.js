import React from 'react';
import { Navigate, Outlet } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';

const CustomerRoute = ({ children }) => {
    const { user, isLoading } = useAuth();

    if (isLoading) {
        return <div className="loading-screen">Loading...</div>; // Or a proper Loader component
    }

    // Checking if user is logged in AND is an admin
    if (user && user.role === 'admin') {
        return <Navigate to="/admin/dashboard" replace />;
    }

    // If not admin (guest or customer), allow access
    return children ? children : <Outlet />;
};

export default CustomerRoute;

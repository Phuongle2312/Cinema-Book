import React, { useContext } from 'react';
import { Navigate, useLocation } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import { Loader2 } from 'lucide-react';

const PrivateRoute = ({ children }) => {
    const { user, loading } = useAuth();
    const location = useLocation();

    if (loading) {
        return (
            <div className="flex justify-center items-center h-screen bg-[#0a0a0a]">
                <Loader2 className="animate-spin text-red-600" size={48} />
            </div>
        );
    }

    if (!user) {
        // Redirect to login but save the attempted url
        return <Navigate to="/login" state={{ from: location }} replace />;
    }

    return children;
};

export default PrivateRoute;

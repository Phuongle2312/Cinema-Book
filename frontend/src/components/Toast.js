import React, { useEffect, useState } from 'react';
import { X, CheckCircle, AlertCircle, Info, AlertTriangle } from 'lucide-react';
import './Toast.css';

const ICONS = {
    success: <CheckCircle size={20} />,
    error: <AlertCircle size={20} />,
    info: <Info size={20} />,
    warning: <AlertTriangle size={20} />
};

const Toast = ({ id, type, message, duration = 3000, onClose }) => {
    const [isClosing, setIsClosing] = useState(false);

    useEffect(() => {
        const timer = setTimeout(() => {
            handleClose();
        }, duration);

        return () => clearTimeout(timer);
    }, [duration]);

    const handleClose = () => {
        setIsClosing(true);
        setTimeout(() => {
            onClose(id);
        }, 300); // Match CSS animation
    };

    return (
        <div className={`toast ${type} ${isClosing ? 'closing' : ''}`}>
            <div className="toast-icon">
                {ICONS[type]}
            </div>
            <div className="toast-message">
                {message}
            </div>
            <button className="toast-close" onClick={handleClose}>
                <X size={16} />
            </button>
        </div>
    );
};

export default Toast;

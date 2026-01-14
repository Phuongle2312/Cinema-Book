import React from 'react';
import { X, Check, AlertTriangle, Info } from 'lucide-react';
import './Popup.css';

const Popup = ({ isOpen, type, title, message, onConfirm, onCancel, confirmText = "OK", cancelText = "Cancel" }) => {
    if (!isOpen) return null;

    const getIcon = () => {
        switch (type) {
            case 'success':
                return <Check size={32} />;
            case 'error':
                return <X size={32} />;
            case 'warning':
                return <AlertTriangle size={32} />;
            case 'info':
            default:
                return <Info size={32} />;
        }
    };

    return (
        <div className="popup-overlay" onClick={onCancel || onConfirm}>
            <div className="popup-container" onClick={(e) => e.stopPropagation()}>
                <div className="popup-header">
                    <div className={`popup-icon ${type}`}>
                        {getIcon()}
                    </div>
                </div>

                <h3 className="popup-title">{title}</h3>
                <p className="popup-message">{message}</p>

                <div className="popup-actions">
                    {onCancel && (
                        <button className="popup-btn cancel" onClick={onCancel}>
                            {cancelText}
                        </button>
                    )}
                    <button className="popup-btn confirm" onClick={onConfirm}>
                        {confirmText}
                    </button>
                </div>
            </div>
        </div>
    );
};

export default Popup;

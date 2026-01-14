import React, { createContext, useContext, useState, useCallback } from 'react';
import Popup from '../components/Popup';

const PopupContext = createContext();

export const PopupProvider = ({ children }) => {
    const [popupState, setPopupState] = useState({
        isOpen: false,
        type: 'info', // success, error, warning, info
        title: '',
        message: '',
        onConfirm: null,
        onCancel: null,
        confirmText: 'OK',
        cancelText: 'Cancel'
    });

    const showPopup = useCallback(({ type = 'info', title, message, onConfirm, onCancel, confirmText = 'OK', cancelText = 'Cancel' }) => {
        setPopupState({
            isOpen: true,
            type,
            title,
            message,
            onConfirm: () => {
                if (onConfirm) onConfirm();
                closePopup();
            },
            onCancel: onCancel ? () => {
                onCancel();
                closePopup();
            } : null,
            confirmText,
            cancelText
        });
    }, []);

    const closePopup = useCallback(() => {
        setPopupState((prev) => ({ ...prev, isOpen: false }));
    }, []);

    // Helper functions for common popups
    const showError = (message, title = 'Error') => showPopup({ type: 'error', title, message });
    const showSuccess = (message, title = 'Success') => showPopup({ type: 'success', title, message });
    const showWarning = (message, title = 'Warning') => showPopup({ type: 'warning', title, message });
    const showInfo = (message, title = 'Info') => showPopup({ type: 'info', title, message });

    return (
        <PopupContext.Provider value={{ showPopup, closePopup, showError, showSuccess, showWarning, showInfo }}>
            {children}
            <Popup {...popupState} onConfirm={popupState.onConfirm || closePopup} />
        </PopupContext.Provider>
    );
};

export const usePopup = () => {
    const context = useContext(PopupContext);
    if (!context) {
        throw new Error('usePopup must be used within a PopupProvider');
    }
    return context;
};

import React, { useState, useEffect } from 'react';
// import axios from 'axios'; // Removed in favor of service
import offerService from '../services/offerService';
import { X, Gift } from 'lucide-react';
import { useNavigate } from 'react-router-dom';

const OfferPopup = () => {
    const [show, setShow] = useState(false);
    const [offer, setOffer] = useState(null);
    const navigate = useNavigate();

    useEffect(() => {
        const checkAndFetchOffer = async () => {
            // Check LocalStorage
            const lastPopup = localStorage.getItem('last_offer_popup');
            const today = new Date().toISOString().split('T')[0];

            console.log('Popup Debug:', { lastPopup, today });

            if (lastPopup === today) {
                console.log('Popup already shown today');
                return; // Already shown today
            }

            try {
                // Fetch System Offers via Service
                const response = await offerService.getOffers();

                if (response.success && response.data.length > 0) {
                    // Get the latest or most relevant offer
                    // Ideally check for 'is_system_wide' or custom logic
                    const latestOffer = response.data[0];
                    setOffer(latestOffer);

                    // Show popup with a slight delay
                    setTimeout(() => setShow(true), 1000);
                }
            } catch (error) {
                console.error("Failed to fetch offers for popup", error);
            }
        };

        checkAndFetchOffer();
    }, []);

    const handleClose = () => {
        setShow(false);
        const today = new Date().toISOString().split('T')[0];
        localStorage.setItem('last_offer_popup', today);
    };

    const handleBookNow = () => {
        navigate('/movies');
        handleClose();
    };

    if (!show || !offer) return null;

    return (
        <div style={overlayStyle}>
            <div style={modalStyle}>
                <button style={closeBtnStyle} onClick={handleClose}>
                    <X size={24} color="#fff" />
                </button>

                <div style={headerStyle}>
                    <Gift size={48} color="#f59e0b" style={{ marginBottom: '10px' }} />
                    <h2 style={{ margin: 0, fontSize: '1.5rem', color: '#fff' }}>What's Hot Today?</h2>
                    <p style={{ margin: '5px 0 0', opacity: 0.8, fontSize: '0.9rem' }}>Special offer just for you</p>
                </div>

                <div style={bodyStyle}>
                    <h3 style={{ color: '#f59e0b', margin: '10px 0', fontSize: '1.25rem' }}>{offer.title}</h3>

                    {offer.description && (
                        <p style={{ color: '#ccc', fontSize: '0.95rem', lineHeight: '1.5' }}>
                            {offer.description}
                        </p>
                    )}

                    {offer.code && (
                        <div style={codeBoxStyle}>
                            <span style={{ fontSize: '0.85rem', color: '#aaa', display: 'block', marginBottom: '4px' }}>CODE:</span>
                            <span style={{ fontSize: '1.5rem', fontWeight: 'bold', letterSpacing: '2px', color: '#fff' }}>{offer.code}</span>
                        </div>
                    )}

                    {offer.discount && (
                        <div style={{ marginTop: '10px', fontWeight: 'bold', color: '#22c55e' }}>
                            {offer.discount}
                        </div>
                    )}

                    <button style={ctaBtnStyle} onClick={handleBookNow}>
                        Book Now
                    </button>
                </div>
            </div>
        </div>
    );
};

// Styles
const overlayStyle = {
    position: 'fixed',
    top: 0,
    left: 0,
    right: 0,
    bottom: 0,
    backgroundColor: 'rgba(0, 0, 0, 0.8)',
    backdropFilter: 'blur(4px)',
    display: 'flex',
    justifyContent: 'center',
    alignItems: 'center',
    zIndex: 9999,
    animation: 'fadeIn 0.3s ease-out'
};

const modalStyle = {
    backgroundColor: 'var(--bg-card)',
    width: '90%',
    maxWidth: '450px',
    borderRadius: '24px',
    overflow: 'hidden',
    boxShadow: '0 25px 50px -12px rgba(0, 0, 0, 0.5)',
    border: '1px solid var(--border-glass)',
    animation: 'scaleUp 0.4s cubic-bezier(0.16, 1, 0.3, 1)'
};

const closeBtnStyle = {
    position: 'absolute',
    top: '15px',
    right: '15px',
    background: 'rgba(0,0,0,0.5)',
    border: '1px solid rgba(255,255,255,0.1)',
    borderRadius: '50%',
    width: '36px',
    height: '36px',
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
    cursor: 'pointer',
    zIndex: 10,
    transition: 'all 0.2s ease'
};

const headerStyle = {
    background: 'linear-gradient(135deg, var(--primary) 0%, #8a040b 100%)',
    padding: '40px 30px',
    textAlign: 'center',
    color: 'white',
    position: 'relative',
    display: 'flex',
    flexDirection: 'column',
    alignItems: 'center'
};

const bodyStyle = {
    padding: '30px',
    textAlign: 'center',
    color: 'var(--text-main)',
    background: 'var(--bg-card)'
};

const codeBoxStyle = {
    background: 'rgba(255, 255, 255, 0.03)',
    border: '1px dashed var(--accent)',
    borderRadius: '12px',
    padding: '15px',
    margin: '20px 0',
    display: 'flex',
    flexDirection: 'column',
    alignItems: 'center',
    justifyContent: 'center'
};

const ctaBtnStyle = {
    backgroundColor: 'var(--primary)',
    color: 'white',
    border: 'none',
    padding: '16px 32px',
    borderRadius: '12px',
    fontSize: '1rem',
    fontWeight: '600',
    cursor: 'pointer',
    width: '100%',
    transition: 'all 0.3s ease',
    marginTop: '15px',
    boxShadow: '0 10px 20px -10px rgba(229, 9, 20, 0.5)',
    textTransform: 'uppercase',
    letterSpacing: '0.5px'
};

export default OfferPopup;

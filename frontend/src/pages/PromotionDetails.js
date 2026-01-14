import React, { useEffect, useState } from 'react';
import { usePopup } from '../context/PopupContext';
import { useParams, useNavigate } from 'react-router-dom';
import Navbar from '../components/Navbar';
import Footer from '../components/Footer';
import eventsData from '../data/events.json';
import { Calendar, Tag, ArrowLeft, Share2, Clock } from 'lucide-react';
import './PromotionDetails.css';

const PromotionDetails = () => {
    const { id } = useParams();
    const navigate = useNavigate();
    const [promotion, setPromotion] = useState(null);
    const [actionState, setActionState] = useState({ loading: false, completed: false });

    const { showSuccess } = usePopup();

    const handleAction = () => {
        setActionState({ loading: true, completed: false });

        // Simulate API call
        setTimeout(() => {
            setActionState({ loading: false, completed: true });

            if (promotion.type === 'offer') {
                const realCode = promotion.title.toUpperCase().replace(/[^A-Z0-9]/g, '_');
                showSuccess(
                    `Your Code: ${realCode}\n\nUse this code at the checkout page to get your discount.`,
                    'Offer Claimed Successfully!'
                );
            } else {
                showSuccess(
                    "We have sent a confirmation email to your inbox.",
                    "Registration Successful!"
                );
            }
        }, 1500);
    };

    useEffect(() => {
        // Find the promotion by ID
        const foundPromotion = eventsData.find(item => item.id === parseInt(id));
        if (foundPromotion) {
            setPromotion(foundPromotion);
        } else {
            // Redirect to offers if not found (or handle error)
            navigate('/offers');
        }
        window.scrollTo(0, 0);
    }, [id, navigate]);

    if (!promotion) return null;

    return (
        <div className="promotion-details-page">
            <Navbar />

            <div className="promotion-banner">
                <img src={promotion.image} alt={promotion.title} className="banner-bg" />
                <div className="banner-overlay"></div>
                <div className="container banner-content">
                    <button className="back-btn" onClick={() => navigate(-1)}>
                        <ArrowLeft size={20} />
                        Back
                    </button>
                    <div className="promo-badges">
                        <span className={`type-badge ${promotion.type}`}>
                            {promotion.type === 'offer' ? 'Special Offer' : 'Event'}
                        </span>
                        <span className="tag-badge">
                            <Tag size={14} />
                            {promotion.tag}
                        </span>
                    </div>
                    <h1>{promotion.title}</h1>
                    <div className="promo-meta">
                        <div className="meta-item">
                            <Calendar size={18} />
                            <span>{promotion.date}</span>
                        </div>
                        {promotion.type === 'event' && (
                            <div className="meta-item">
                                <Clock size={18} />
                                <span>18:00 - 21:00</span>
                            </div>
                        )}
                    </div>
                </div>
            </div>

            <div className="container promotion-body">
                <div className="content-wrapper">
                    <div className="main-content">
                        <div className="section-title">Description</div>
                        <p className="description-text">{promotion.description}</p>

                        <div className="section-title">Terms & Conditions</div>
                        <ul className="terms-list">
                            <li>Valid only at participating CineBook locations.</li>
                            <li>Cannot be combined with other promotions or discounts.</li>
                            <li>Subject to availability.</li>
                            <li>See cinema counter for more details.</li>
                            {promotion.type === 'offer' ? (
                                <li>Offer valid until stocks last.</li>
                            ) : (
                                <li>Please arrive 30 minutes before the event starts.</li>
                            )}
                        </ul>
                    </div>


                </div>
            </div>

            <Footer />
        </div>
    );
};

export default PromotionDetails;

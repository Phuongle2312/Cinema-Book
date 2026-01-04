import React from 'react';
import Navbar from '../components/Navbar';
import Footer from '../components/Footer';
import eventsData from '../data/events.json';
import { Tag, Calendar, TicketPercent } from 'lucide-react';
import { useNavigate } from 'react-router-dom';
import './Promotions.css';

const Offers = () => {
    const navigate = useNavigate();
    // Filter for offers
    const offers = eventsData.filter(item => item.type === 'offer');

    return (
        <div className="promotions-page">
            <Navbar />

            <div className="promotions-hero" style={{ backgroundImage: "url('https://images.unsplash.com/photo-1517604931442-7e0c8ed2963c?q=80&w=2070&auto=format&fit=crop')" }}>
                <div className="container">
                    <h1>Special Offers</h1>
                    <p>Unlock exclusive deals, member discounts, and limited-time promotions for your favorite movies.</p>
                </div>
            </div>

            <div className="container">
                <div className="promotions-grid">
                    {offers.length > 0 ? (
                        offers.map(offer => (
                            <div key={offer.id} className="promo-card" onClick={() => navigate(`/promotion/${offer.id}`)} style={{ cursor: 'pointer' }}>
                                <div className="promo-image">
                                    <img src={offer.image} alt={offer.title} />
                                    <div className="promo-tag">
                                        <TicketPercent size={14} className="inline mr-1" />
                                        {offer.tag}
                                    </div>
                                </div>
                                <div className="promo-content">
                                    <div className="promo-date">
                                        <Calendar size={16} />
                                        <span>{offer.date}</span>
                                    </div>
                                    <h3 className="promo-title">{offer.title}</h3>
                                    <p className="promo-description">{offer.description}</p>
                                    <button className="promo-btn" onClick={(e) => {
                                        e.stopPropagation();
                                        navigate(`/promotion/${offer.id}`);
                                    }}>Get Offer</button>
                                </div>
                            </div>
                        ))
                    ) : (
                        <div className="promo-empty">
                            No active offers at the moment. Check back soon!
                        </div>
                    )}
                </div>
            </div>

            <Footer />
        </div>
    );
};

export default Offers;

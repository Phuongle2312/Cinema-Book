import React from 'react';
import Navbar from '../components/Navbar';
import Footer from '../components/Footer';
import eventsData from '../data/events.json';
import { Calendar, Star, Sparkles } from 'lucide-react';
import { useNavigate } from 'react-router-dom';
import './Promotions.css';

const Events = () => {
    const navigate = useNavigate();
    // Filter for events
    const events = eventsData.filter(item => item.type === 'event');

    return (
        <div className="promotions-page">
            <Navbar />

            <div className="promotions-hero" style={{ backgroundImage: "url('https://images.unsplash.com/photo-1478720568477-152d9b164e26?q=80&w=2070&auto=format&fit=crop')" }}>
                <div className="container">
                    <h1>Upcoming Events</h1>
                    <p>Join us for movie premieres, fan meetups, and exclusive screenings. Be part of the magic.</p>
                </div>
            </div>

            <div className="container">
                <div className="promotions-grid">
                    {events.length > 0 ? (
                        events.map(event => (
                            <div key={event.id} className="promo-card" onClick={() => navigate(`/promotion/${event.id}`)} style={{ cursor: 'pointer' }}>
                                <div className="promo-image">
                                    <img src={event.image} alt={event.title} />
                                    <div className="promo-tag">
                                        <Sparkles size={14} className="inline mr-1" />
                                        {event.tag}
                                    </div>
                                </div>
                                <div className="promo-content">
                                    <div className="promo-date">
                                        <Calendar size={16} />
                                        <span>{event.date}</span>
                                    </div>
                                    <h3 className="promo-title">{event.title}</h3>
                                    <p className="promo-description">{event.description}</p>
                                    <button className="promo-btn" onClick={(e) => {
                                        e.stopPropagation();
                                        navigate(`/promotion/${event.id}`);
                                    }}>Join Event</button>
                                </div>
                            </div>
                        ))
                    ) : (
                        <div className="promo-empty">
                            No upcoming events scheduled. Stay tuned!
                        </div>
                    )}
                </div>
            </div>

            <Footer />
        </div>
    );
};

export default Events;

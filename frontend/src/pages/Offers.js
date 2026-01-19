import React from 'react';
import Navbar from '../components/Navbar';
import Footer from '../components/Footer';
import eventsData from '../data/events.json';
import { Calendar, TicketPercent, ChevronRight } from 'lucide-react';
import { useNavigate } from 'react-router-dom';
import './Offers.css';

const Offers = () => {
    const navigate = useNavigate();
    // Filter for offers
    const offers = eventsData.filter(item => item.type === 'offer');

    return (
        <div className="offers-page bg-[#0a0a0a] min-h-screen text-white">
            <Navbar />

            <div className="offers-hero">
                <div className="absolute inset-0 bg-red-600/10 blur-[120px] rounded-full -translate-x-1/2 -translate-y-1/2"></div>
                <h1>Exclusive Offers</h1>
                <p>
                    Discover attractive offers, special discount codes, and exclusive programs for CineBook members.
                </p>
            </div>

            <div className="offers-container">
                <div className="offers-grid">
                    {offers.length > 0 ? (
                        offers.map(offer => (
                            <div
                                key={offer.id}
                                className="offer-card"
                                onClick={() => navigate(`/offers/${offer.id}`)}
                            >
                                <div className="offer-image-wrapper">
                                    <img src={offer.image} alt={offer.title} />
                                    <div className="offer-tag">
                                        <TicketPercent size={14} />
                                        {offer.tag}
                                    </div>
                                </div>

                                <div className="offer-content">
                                    <div className="offer-date">
                                        <Calendar size={14} className="text-red-600" />
                                        {offer.date}
                                    </div>
                                    <h3 className="offer-title">{offer.title}</h3>
                                    <p className="offer-desc">{offer.description}</p>

                                    <div className="flex items-center justify-between mt-auto">
                                        <button className="btn-detail">
                                            DETAILS <ChevronRight size={16} />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        ))
                    ) : (
                        <div className="col-span-full py-20 text-center border border-dashed border-white/10 rounded-2xl">
                            <p className="text-gray-500">No new offers available at the moment. Check back later!</p>
                        </div>
                    )}
                </div>
            </div>

            <Footer />
        </div>
    );
};

export default Offers;

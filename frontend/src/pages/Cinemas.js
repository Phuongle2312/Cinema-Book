import React, { useState, useEffect } from 'react';
import Navbar from '../components/Navbar';
import Footer from '../components/Footer';
import theaterService from '../services/theaterService';
import './Cinemas.css';
import { MapPin, Loader2, Navigation, Phone, Info, Mail, Clock, X } from 'lucide-react';

const Cinemas = () => {
    const [theaters, setTheaters] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [selectedCity, setSelectedCity] = useState("Hồ Chí Minh");
    const [selectedTheater, setSelectedTheater] = useState(null);

    const cities = ["Hồ Chí Minh", "Hà Nội", "Đà Nẵng", "Cần Thơ", "Đồng Nai", "Hải Phòng", "Quảng Ninh"];

    useEffect(() => {
        const fetchTheaters = async () => {
            try {
                setLoading(true);
                const data = await theaterService.getTheaters();
                setTheaters(data.data || data);
            } catch (err) {
                console.error("Failed to fetch theaters:", err);
                setError("Unable to load cinemas at this time.");
            } finally {
                setLoading(false);
            }
        };

        fetchTheaters();
    }, []);

    // Filter theaters by city
    const filteredTheaters = theaters.filter(theater => theater.city?.name === selectedCity);

    const openDetails = (theater) => {
        setSelectedTheater(theater);
    };

    const closeDetails = () => {
        setSelectedTheater(null);
    };

    return (
        <div className="cinemas-page">
            <Navbar />

            <div className="cinemas-hero">
                <div className="container">
                    <h1>Our Cinemas</h1>
                    <p>Experience the magic of movies at our state-of-the-art theaters across the country.</p>
                </div>
            </div>

            <div className="container cinemas-content">
                {/* City Selector */}
                <div className="city-tabs">
                    {cities.map(city => (
                        <button
                            key={city}
                            className={`city-tab ${selectedCity === city ? 'active' : ''}`}
                            onClick={() => setSelectedCity(city)}
                        >
                            {city}
                        </button>
                    ))}
                </div>

                {/* Theater List */}
                {loading ? (
                    <div className="loading-state">
                        <Loader2 className="animate-spin" size={40} />
                        <p>Finding theaters near you...</p>
                    </div>
                ) : error ? (
                    <div className="error-state">
                        <p>{error}</p>
                    </div>
                ) : (
                    <div className="theater-list">
                        {filteredTheaters.length > 0 ? (
                            <div className="theaters-grid">
                                {filteredTheaters.map(theater => (
                                    <div key={theater.theater_id} className="theater-card">
                                        <div className="theater-image">
                                            <img
                                                src="https://images.unsplash.com/photo-1517604931442-7e0c8ed2963c?q=80&w=2070&auto=format&fit=crop"
                                                alt={theater.name}
                                            />
                                            <div className="theater-badge">Now Open</div>
                                        </div>
                                        <div className="theater-info">
                                            <h3>{theater.name}</h3>

                                            <div className="theater-actions">
                                                <button className="btn-details" onClick={() => openDetails(theater)}>
                                                    <Info size={16} />
                                                    Details
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        ) : (
                            <div className="empty-state">
                                <p>No theaters found in {selectedCity}. We are expanding soon!</p>
                            </div>
                        )}
                    </div>
                )}
            </div>

            {/* Theater Details Modal */}
            {selectedTheater && (
                <div className="theater-modal-overlay" onClick={closeDetails}>
                    <div className="theater-modal" onClick={(e) => e.stopPropagation()}>
                        <button className="modal-close" onClick={closeDetails}>
                            <X size={24} />
                        </button>

                        <div className="modal-header">
                            <img
                                src="https://images.unsplash.com/photo-1517604931442-7e0c8ed2963c?q=80&w=2070&auto=format&fit=crop"
                                alt={selectedTheater.name}
                            />
                            <div className="modal-title">
                                <h2>{selectedTheater.name}</h2>
                                <span className="modal-city">{selectedTheater.city?.name}</span>
                            </div>
                        </div>

                        <div className="modal-body">
                            <div className="detail-item">
                                <MapPin size={20} className="detail-icon" />
                                <div>
                                    <strong>Address</strong>
                                    <p>{selectedTheater.address || '123 Example Street, ' + selectedTheater.city?.name}</p>
                                </div>
                            </div>

                            <div className="detail-item">
                                <Phone size={20} className="detail-icon" />
                                <div>
                                    <strong>Phone</strong>
                                    <p>{selectedTheater.phone || '1900-6017'}</p>
                                </div>
                            </div>

                            <div className="detail-item">
                                <Mail size={20} className="detail-icon" />
                                <div>
                                    <strong>Email</strong>
                                    <p>contact@cinebook.vn</p>
                                </div>
                            </div>

                            <div className="detail-item">
                                <Clock size={20} className="detail-icon" />
                                <div>
                                    <strong>Opening Hours</strong>
                                    <p>Daily: 9:00 AM - 11:00 PM</p>
                                </div>
                            </div>
                        </div>

                        <div className="modal-footer">
                            <a
                                href={`https://maps.google.com/?q=${encodeURIComponent(selectedTheater.name)}`}
                                target="_blank"
                                rel="noopener noreferrer"
                                className="btn-primary"
                            >
                                <Navigation size={16} />
                                Get Directions
                            </a>
                        </div>
                    </div>
                </div>
            )}

            <Footer />
        </div>
    );
};

export default Cinemas;

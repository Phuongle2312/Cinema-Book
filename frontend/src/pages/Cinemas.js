import React, { useState, useEffect } from 'react';
import Navbar from '../components/Navbar';
import Footer from '../components/Footer';
import theaterService from '../services/theaterService';
import './Cinemas.css';
import { MapPin, Loader2, Navigation, Phone, Info } from 'lucide-react';

const Cinemas = () => {
    const [theaters, setTheaters] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [selectedCity, setSelectedCity] = useState("Hồ Chí Minh");

    const cities = ["Hồ Chí Minh", "Hà Nội", "Đà Nẵng", "Cần Thơ", "Đồng Nai", "Hải Phòng", "Quảng Ninh"];

    useEffect(() => {
        const fetchTheaters = async () => {
            try {
                setLoading(true);
                const data = await theaterService.getTheaters();
                setTheaters(data.data || data); // Adjust based on API structure
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
    const filteredTheaters = theaters.filter(theater => theater.city === selectedCity);

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
                                            {/* Generic Theater Image - Placeholder */}
                                            <img
                                                src="https://images.unsplash.com/photo-1517604931442-7e0c8ed2963c?q=80&w=2070&auto=format&fit=crop"
                                                alt={theater.name}
                                            />
                                            <div className="theater-badge">Now Open</div>
                                        </div>
                                        <div className="theater-info">
                                            <h3>{theater.name}</h3>

                                            <div className="theater-meta">
                                                <div className="meta-item">
                                                    <MapPin size={16} className="text-primary" />
                                                    <span>{theater.address}</span>
                                                </div>
                                            </div>

                                            <div className="theater-actions">
                                                <a
                                                    href={`https://maps.google.com/?q=${encodeURIComponent(theater.name + ' ' + theater.address)}`}
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    className="btn-map"
                                                >
                                                    <Navigation size={16} />
                                                    Get Directions
                                                </a>
                                                <button className="btn-details">
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

            <Footer />
        </div>
    );
};

export default Cinemas;

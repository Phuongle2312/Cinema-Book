import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import Navbar from '../components/Navbar';
import Banner from '../components/Banner';
import MovieCard from '../components/MovieCard';
import Footer from '../components/Footer';
import movieService from '../services/movieService';
import offerService from '../services/offerService'; // Added
import './Home.css';
import { ChevronRight, Loader2, Calendar, Tag, TicketPercent, MapPin, Film, Search } from 'lucide-react';
import eventData from '../data/events.json';
import OfferPopup from '../components/OfferPopup';

const Home = () => {
    const [movies, setMovies] = useState([]);
    const [comingSoonMovies, setComingSoonMovies] = useState([]);
    const [offers, setOffers] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const navigate = useNavigate();

    useEffect(() => {
        const fetchData = async () => {
            try {
                setLoading(true);
                const [moviesRes, comingSoonRes, offersRes] = await Promise.all([
                    movieService.getFeaturedMovies(),
                    movieService.getComingSoonMovies(),
                    offerService.getOffers()
                ]);

                // Map the data if necessary
                const movieData = moviesRes.data || moviesRes;
                setMovies(Array.isArray(movieData) ? movieData : []);

                const comingSoonData = comingSoonRes.data || comingSoonRes;
                setComingSoonMovies(Array.isArray(comingSoonData) ? comingSoonData : []);

                const offersData = offersRes.data || offersRes;
                setOffers(Array.isArray(offersData) ? offersData : []);
            } catch (err) {
                console.error("Failed to fetch data:", err);
                setError("Could not load content at this time.");
            } finally {
                setLoading(false);
            }
        };

        fetchData();
    }, []);

    return (
        <div className="home-page">
            <Navbar />
            <OfferPopup />

            {/* Cinematic Banner */}
            <Banner />

            {/* Movies Grid */}
            <section className="movies-section container">
                <div className="section-header">
                    <h2 className="section-title">Now Showing</h2>
                    <a href="/movies?status=showing" className="view-all">
                        View All
                        <ChevronRight size={18} />
                    </a>
                </div>

                {loading ? (
                    <div className="loading-state">
                        <Loader2 className="animate-spin" size={40} />
                        <p>Loading movies...</p>
                    </div>
                ) : error ? (
                    <div className="error-state">
                        <p>{error}</p>
                    </div>
                ) : (
                    <div className="movies-grid">
                        {movies.slice(0, 5).map(movie => (
                            <MovieCard
                                key={movie.movie_id}
                                movie={{
                                    id: movie.movie_id,
                                    slug: movie.slug,
                                    title: movie.title,
                                    poster: movie.poster_url || movie.image,
                                    rating: parseFloat(movie.rating) || 0,
                                    duration: movie.duration,
                                    genre: Array.isArray(movie.genres) ? movie.genres.map(g => g.name || g).slice(0, 2).join(', ') : (movie.genre || ''),
                                    year: parseInt(movie.year) || new Date(movie.release_date).getFullYear() || new Date().getFullYear(),
                                    age_rating: movie.age_rating || 'P'
                                }}
                            />
                        ))}
                    </div>
                )}
            </section>

            {/* Coming Soon Section */}
            {comingSoonMovies.length > 0 && (
                <section className="movies-section container">
                    <div className="section-header">
                        <h2 className="section-title">Coming Soon</h2>
                        <a href="/movies?status=coming_soon" className="view-all">
                            View All
                            <ChevronRight size={18} />
                        </a>
                    </div>
                    <div className="movies-grid">
                        {comingSoonMovies.slice(0, 5).map(movie => (
                            <MovieCard
                                key={movie.movie_id}
                                movie={{
                                    id: movie.movie_id,
                                    slug: movie.slug,
                                    title: movie.title,
                                    poster: movie.poster_url || movie.image,
                                    rating: parseFloat(movie.rating) || 0,
                                    duration: movie.duration,
                                    genre: Array.isArray(movie.genres) ? movie.genres.map(g => g.name || g).slice(0, 2).join(', ') : (movie.genre || ''),
                                    year: parseInt(movie.year) || new Date(movie.release_date).getFullYear() || new Date().getFullYear(),
                                    age_rating: movie.age_rating || 'P'
                                }}
                            />
                        ))}
                    </div>
                </section>
            )}

            {/* Offers Section */}
            <section className="events-section container">
                <div className="section-header">
                    <h2 className="section-title">Special Offers</h2>
                    <a href="/offers" className="view-all">
                        View All
                        <ChevronRight size={18} />
                    </a>
                </div>

                <div className="events-grid">
                    {offers.filter(item => item.type === 'offer').slice(0, 3).map(offer => (
                        <div key={offer.id} className="event-card" onClick={() => navigate(`/offers/${offer.id}`)} style={{ cursor: 'pointer' }}>
                            <div className="event-image">
                                <img src={offer.image} alt={offer.title} />
                                <div className="event-tag" style={{ backgroundColor: '#eab308', color: '#000' }}>
                                    <TicketPercent size={12} />
                                    {offer.tag}
                                </div>
                            </div>
                            <div className="event-info">
                                <div className="event-date">
                                    <Calendar size={14} />
                                    <span>{offer.date}</span>
                                </div>
                                <h3 className="event-title">{offer.title}</h3>
                                <p className="event-description">{offer.description}</p>
                                <button className="event-btn" onClick={(e) => {
                                    e.stopPropagation();
                                    navigate(`/offers/${offer.id}`);
                                }}>Get Offer</button>
                            </div>
                        </div>
                    ))}
                </div>
            </section>

            {/* Events Section */}
            <section className="events-section container">
                <div className="section-header">
                    <h2 className="section-title">Upcoming Events</h2>
                    <a href="/events" className="view-all">
                        View All
                        <ChevronRight size={18} />
                    </a>
                </div>

                <div className="events-grid">
                    {offers.filter(item => item.type === 'event').slice(0, 3).map(event => (
                        <div key={event.id} className="event-card" onClick={() => navigate(`/offers/${event.id}`)} style={{ cursor: 'pointer' }}>
                            <div className="event-image">
                                <img src={event.image} alt={event.title} />
                                <div className="event-tag">
                                    <Tag size={12} />
                                    {event.tag}
                                </div>
                            </div>
                            <div className="event-info">
                                <div className="event-date">
                                    <Calendar size={14} />
                                    <span>{event.date}</span>
                                </div>
                                <h3 className="event-title">{event.title}</h3>
                                <p className="event-description">{event.description}</p>
                                <button className="event-btn" onClick={(e) => {
                                    e.stopPropagation();
                                    navigate(`/offers/${event.id}`);
                                }}>Learn More</button>
                            </div>
                        </div>
                    ))}
                </div>
            </section>

            <Footer />
        </div>
    );
};

export default Home;

import React, { useState, useEffect } from 'react';
import { useParams, useNavigate, Link } from 'react-router-dom';
import { Star, Clock, Calendar, Globe, Play, ChevronLeft, User, MessageCircle, MapPin } from 'lucide-react';
import movieService from '../services/movieService';
import Navbar from '../components/Navbar';
import Footer from '../components/Footer';
import './MovieDetails.css';

const MovieDetails = () => {
    const { id } = useParams();
    const navigate = useNavigate();
    const [movie, setMovie] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
        const fetchMovie = async () => {
            try {
                setLoading(true);
                const data = await movieService.getMovieById(id);
                setMovie(data.data || data);
            } catch (err) {
                console.error("Failed to fetch movie details:", err);
                setError("Could not load movie details.");
            } finally {
                setLoading(false);
            }
        };

        fetchMovie();
    }, [id]);

    if (loading) {
        return (
            <div className="movie-details-loading">
                <Navbar />
                <div className="loading-container">
                    <div className="spinner"></div>
                    <p>Loading movie details...</p>
                </div>
                <Footer />
            </div>
        );
    }

    if (error || !movie) {
        return (
            <div className="movie-details-error">
                <Navbar />
                <div className="error-container">
                    <h2>Oops!</h2>
                    <p>{error || "Movie not found."}</p>
                    <button onClick={() => navigate('/')} className="btn-back-home">
                        <ChevronLeft size={20} />
                        Back to Home
                    </button>
                </div>
                <Footer />
            </div>
        );
    }

    return (
        <div className="movie-details-page">
            <Navbar />

            {/* Hero Backdrop */}
            <div className="movie-hero">
                <div className="hero-backdrop">
                    <img src={movie.banner_url || movie.backdrop_url || movie.image} alt={movie.title} />
                    <div className="hero-overlay"></div>
                </div>

                <div className="container hero-content-wrapper">
                    <div className="movie-poster-large">
                        <img src={movie.poster_url || movie.image} alt={movie.title} />
                        {movie.trailer_url && (
                            <button className="btn-play-trailer">
                                <Play fill="white" size={24} />
                                Watch Trailer
                            </button>
                        )}
                    </div>

                    <div className="movie-info-main">
                        <div className="movie-badges">
                            <span className="badge-rating">
                                <Star fill="#ffd700" color="#ffd700" size={16} />
                                {movie.rating}
                            </span>
                            {movie.genres && movie.genres.map(genre => (
                                <span key={genre.genre_id || genre} className="badge-genre">{genre.name || genre}</span>
                            ))}
                        </div>

                        <h1 className="movie-title-large">{movie.title}</h1>

                        <div className="movie-meta-row">
                            <span className="meta-item">
                                <Clock size={18} />
                                {movie.duration}
                            </span>
                            <span className="meta-item">
                                <Calendar size={18} />
                                {movie.year || (movie.release_date ? new Date(movie.release_date).getFullYear() : '2025')}
                            </span>
                            <span className="meta-item">
                                <Globe size={18} />
                                {movie.language || 'English'}
                            </span>
                        </div>

                        <div className="movie-actions">
                            <Link to={`/booking/movie/${movie.id}`} className="btn-book-tickets">Book Tickets Now</Link>
                            <button className="btn-save-later">Save to Watchlist</button>
                        </div>
                    </div>
                </div>
            </div>

            <div className="container movie-details-content">
                <div className="details-grid">
                    <div className="details-main">
                        <section className="movie-section">
                            <h2 className="section-title">Synopsis</h2>
                            <p className="synopsis-text">{movie.description || movie.synopsis || "No synopsis available."}</p>
                        </section>

                        {movie.trailer_url && (
                            <section className="movie-section">
                                <h2 className="section-title">Trailer</h2>
                                <div className="video-container">
                                    <iframe
                                        src={movie.trailer_url}
                                        title="Movie Trailer"
                                        frameBorder="0"
                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                        allowFullScreen
                                    ></iframe>
                                </div>
                            </section>
                        )}

                        <section className="movie-section">
                            <h2 className="section-title">Cast & Crew</h2>
                            <div className="cast-grid">
                                {(movie.cast || []).map((person, index) => (
                                    <div key={index} className="cast-card">
                                        <div className="cast-img">
                                            {person.image_url || person.image ? <img src={person.image_url || person.image} alt={person.name} /> : <User size={40} />}
                                        </div>
                                        <div className="cast-info">
                                            <span className="cast-name">{person.name}</span>
                                            <span className="cast-role">{person.pivot?.role || person.role}</span>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </section>
                    </div>

                    <div className="details-sidebar">
                        <section className="movie-section">
                            <h2 className="section-title">Reviews</h2>
                            <div className="reviews-list">
                                {(movie.reviews || []).length > 0 ? (
                                    movie.reviews.map((review, index) => (
                                        <div key={index} className="review-card">
                                            <div className="review-header">
                                                <div className="reviewer-info">
                                                    <span className="reviewer-name">{review.user_name}</span>
                                                    <div className="review-rating">
                                                        <Star fill="#ffd700" color="#ffd700" size={14} />
                                                        {review.rating}
                                                    </div>
                                                </div>
                                            </div>
                                            <p className="review-text">{review.comment}</p>
                                        </div>
                                    ))
                                ) : (
                                    <div className="no-reviews">
                                        <MessageCircle size={32} />
                                        <p>No reviews yet.</p>
                                    </div>
                                )}
                            </div>
                        </section>
                    </div>
                </div>
            </div>

            <Footer />
        </div>
    );
};

export default MovieDetails;

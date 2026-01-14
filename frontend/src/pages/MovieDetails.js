import React, { useState, useEffect } from 'react';
import { useParams, useNavigate, Link } from 'react-router-dom';
import { Star, Clock, Calendar, Globe, Play, ChevronLeft, User, Heart } from 'lucide-react';
import movieService from '../services/movieService';
import wishlistService from '../services/wishlistService';
import { getYouTubeEmbedUrl } from '../utils/videoUtils';
import Navbar from '../components/Navbar';
import Footer from '../components/Footer';
import { useAuth } from '../context/AuthContext';
import './MovieDetails.css';

const MovieDetails = () => {
    const { slug } = useParams();
    const navigate = useNavigate();
    const [movie, setMovie] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [isFavorite, setIsFavorite] = useState(false);
    const [wishlistLoading, setWishlistLoading] = useState(false);
    const { isAuthenticated } = useAuth();

    useEffect(() => {
        const fetchMovie = async () => {
            try {
                setLoading(true);
                const data = await movieService.getMovieById(slug);
                const movieData = data.data || data;
                setMovie(movieData);

                if (isAuthenticated && (movieData.movie_id || movieData.id)) {
                    const favStatus = await wishlistService.checkIsFavorite(movieData.movie_id || movieData.id);
                    setIsFavorite(favStatus.is_favorite);
                }
            } catch (err) {
                console.error("Failed to fetch movie details:", err);
                setError("Could not load movie details.");
            } finally {
                setLoading(false);
            }
        };

        fetchMovie();
    }, [slug, isAuthenticated]);

    const handleToggleWishlist = async () => {
        if (!isAuthenticated) {
            navigate('/login');
            return;
        }

        setWishlistLoading(true);
        try {
            const res = await wishlistService.toggleWishlist(movie.movie_id || movie.id);
            if (res.success) {
                setIsFavorite(res.is_favorite);
            }
        } catch (err) {
            console.error("Error toggling wishlist", err);
        } finally {
            setWishlistLoading(false);
        }
    };

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
                            <Link to={`/booking/movie/${movie.slug || movie.movie_id || movie.id}`} className="btn-book-tickets">Book Tickets Now</Link>
                            <button
                                className={`btn-save-later ${isFavorite ? 'active' : ''}`}
                                onClick={handleToggleWishlist}
                                disabled={wishlistLoading}
                            >
                                <Heart size={18} fill={isFavorite ? "#e50914" : "none"} color={isFavorite ? "#e50914" : "white"} />
                                {isFavorite ? 'Saved to Watchlist' : 'Save to Watchlist'}
                            </button>
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
                                        src={getYouTubeEmbedUrl(movie.trailer_url)}
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
                            <h2 className="section-title">Movie Info</h2>
                            <div className="movie-info-list">
                                <div className="info-row">
                                    <span className="info-label">Director</span>
                                    <span className="info-value">{movie.director || 'Not available'}</span>
                                </div>
                                <div className="info-row">
                                    <span className="info-label">Actor</span>
                                    <span className="info-value">{movie.actor || 'Not available'}</span>
                                </div>
                                <div className="info-row">
                                    <span className="info-label">Duration</span>
                                    <span className="info-value">{movie.duration} minutes</span>
                                </div>
                                <div className="info-row">
                                    <span className="info-label">Release Date</span>
                                    <span className="info-value">
                                        {movie.release_date ? new Date(movie.release_date).toLocaleDateString('vi-VN') : 'TBA'}
                                    </span>
                                </div>
                                <div className="info-row">
                                    <span className="info-label">Status</span>
                                    <span className={`info-value status-badge ${movie.status}`}>
                                        {movie.status === 'now_showing' ? 'Now Showing' :
                                            movie.status === 'coming_soon' ? 'Coming Soon' : 'Ended'}
                                    </span>
                                </div>
                            </div>
                        </section>

                        {/* Quick Book Section */}
                        <section className="movie-section">
                            <h2 className="section-title">Quick Book</h2>
                            <div className="quick-book-card">
                                <p className="quick-book-text">Ready to watch this movie?</p>
                                <Link to={`/booking/movie/${movie.slug || movie.movie_id || movie.id}`} className="btn-quick-book">
                                    <Calendar size={18} />
                                    Book Tickets Now
                                </Link>
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

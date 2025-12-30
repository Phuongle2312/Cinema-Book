import React, { useState, useEffect } from 'react';
import Navbar from '../components/Navbar';
import Banner from '../components/Banner';
import MovieCard from '../components/MovieCard';
import Footer from '../components/Footer';
import movieService from '../services/movieService';
import './Home.css';
import { ChevronRight, Loader2 } from 'lucide-react';

const Home = () => {
    const [movies, setMovies] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
        const fetchMovies = async () => {
            try {
                setLoading(true);
                const response = await movieService.getFeaturedMovies();
                // Map the data if necessary. Adjusted based on standard Laravel resource structure
                const movieData = response.data || response;
                setMovies(Array.isArray(movieData) ? movieData : []);
            } catch (err) {
                console.error("Failed to fetch movies:", err);
                setError("Could not load movies at this time.");
            } finally {
                setLoading(false);
            }
        };

        fetchMovies();
    }, []);

    return (
        <div className="home-page">
            <Navbar />

            {/* Cinematic Banner */}
            <Banner />

            {/* Movies Grid */}
            <section className="movies-section container">
                <div className="section-header">
                    <h2 className="section-title">Now Showing</h2>
                    <a href="/movies" className="view-all">
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
                        {movies.slice(0, 10).map(movie => (
                            <MovieCard
                                key={movie.movie_id}
                                movie={{
                                    id: movie.movie_id,
                                    title: movie.title,
                                    poster: movie.poster_url || movie.image,
                                    rating: parseFloat(movie.rating) || 0,
                                    duration: movie.duration,
                                    genre: Array.isArray(movie.genres) ? movie.genres.map(g => g.name || g).slice(0, 2).join(', ') : (movie.genre || ''),
                                    year: parseInt(movie.year) || new Date(movie.release_date).getFullYear() || new Date().getFullYear()
                                }}
                            />
                        ))}
                    </div>
                )}
            </section>

            <Footer />
        </div>
    );
};

export default Home;

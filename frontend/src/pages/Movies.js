import React, { useState, useEffect, useCallback } from 'react';
import { Search, Filter, SlidersHorizontal, Loader2, X } from 'lucide-react';
import { useLocation } from 'react-router-dom';
import movieService from '../services/movieService';
import Navbar from '../components/Navbar';
import Footer from '../components/Footer';
import MovieCard from '../components/MovieCard';
import CustomDropdown from '../components/CustomDropdown';
import './Movies.css';

const Movies = () => {
    const [movies, setMovies] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [searchQuery, setSearchQuery] = useState('');
    const [activeFilters, setActiveFilters] = useState({
        genre: '',
        language: '',
        city: '',
        rating: '',
        date: '',
        status: '',
        sort_by: 'release_date'
    });
    const [showMobileFilters, setShowMobileFilters] = useState(false);
    const location = useLocation();

    const genreOptions = [
        { label: 'All Genres', value: '' },
        { label: 'Action', value: 'Action' },
        { label: 'Comedy', value: 'Comedy' },
        { label: 'Drama', value: 'Drama' },
        { label: 'Sci-Fi', value: 'Sci-Fi' },
        { label: 'Horror', value: 'Horror' }
    ];

    const languageOptions = [
        { label: 'All Languages', value: '' },
        { label: 'English', value: 'English' },
        { label: 'Vietnamese', value: 'Vietnamese' },
        { label: 'Korean', value: 'Korean' },
        { label: 'Japanese', value: 'Japanese' }
    ];

    const ratingOptions = [
        { label: 'Any Rating', value: '' },
        { label: '9+ Stars', value: '9' },
        { label: '8+ Stars', value: '8' },
        { label: '7+ Stars', value: '7' },
        { label: '6+ Stars', value: '6' }
    ];

    const sortOptions = [
        { label: 'Newest', value: 'release_date' },
        { label: 'Rating', value: 'rating' },
        { label: 'Title', value: 'title' }
    ];

    const fetchMovies = useCallback(async (query = '', filters = {}) => {
        try {
            setLoading(true);
            let response;
            if (query) {
                response = await movieService.searchMovies(query);
            } else if (Object.values(filters).some(v => v !== '')) {
                // Map frontend filter keys to backend expected keys
                const apiFilters = {};
                if (filters.genre) apiFilters.hashtag = filters.genre; // Backend expects 'hashtag' for genre names
                if (filters.language) apiFilters.hashtag = filters.language; // Assuming languages are also hashtags or separate logic needed
                if (filters.rating) apiFilters.rating = filters.rating;
                if (filters.status) apiFilters.status = filters.status;
                if (filters.sort_by) apiFilters.sort_by = filters.sort_by;

                response = await movieService.filterMovies(apiFilters);
            } else {
                response = await movieService.getMovies();
            }

            const movieData = response.data || response;
            setMovies(Array.isArray(movieData) ? movieData : []);
            setError(null);
        } catch (err) {
            console.error("Failed to fetch movies:", err);
            setError("Failed to load movies. Please try again later.");
        } finally {
            setLoading(false);
        }
    }, []);

    useEffect(() => {
        const params = new URLSearchParams(location.search);
        const q = params.get('q') || '';
        const status = params.get('status') || '';

        setSearchQuery(q);
        const initialFilters = {
            ...activeFilters,
            status: status
        };
        setActiveFilters(initialFilters);
        fetchMovies(q, initialFilters);
    }, [location.search, fetchMovies]);

    const handleSearchSubmit = (e) => {
        e.preventDefault();
        fetchMovies(searchQuery, activeFilters);
    };

    const handleFilterChange = (name, value) => {
        setActiveFilters(prev => {
            const newFilters = { ...prev, [name]: value };
            fetchMovies(searchQuery, newFilters); // Pass newFilters directly
            return newFilters;
        });
    };

    const clearFilters = () => {
        const resetFilters = {
            genre: '',
            language: '',
            city: '',
            rating: '',
            date: ''
        };
        setActiveFilters(resetFilters);
        setSearchQuery('');
        fetchMovies('', resetFilters);
    };

    return (
        <div className="movies-page">
            <Navbar />

            <div className="movies-header">
                <div className="container">
                    <h1 className="page-title">Explore Movies</h1>
                    <p className="page-subtitle">Find your next cinematic experience</p>

                </div>
            </div>

            <main className="container movies-layout">
                {/* Desktop Sidebar Filters */}
                <aside className="filters-sidebar">
                    <div className="filter-group-header">
                        <div className="filter-title">
                            <SlidersHorizontal size={18} />
                            <span>Filters</span>
                        </div>
                        <button className="btn-clear" onClick={clearFilters}>Clear All</button>
                    </div>

                    <CustomDropdown
                        label="Genre"
                        options={genreOptions}
                        value={activeFilters.genre}
                        onChange={(val) => handleFilterChange('genre', val)}
                    />

                    <CustomDropdown
                        label="Language"
                        options={languageOptions}
                        value={activeFilters.language}
                        onChange={(val) => handleFilterChange('language', val)}
                    />

                    <CustomDropdown
                        label="Min Rating"
                        options={ratingOptions}
                        value={activeFilters.rating}
                        onChange={(val) => handleFilterChange('rating', val)}
                    />
                </aside>

                <div className="movies-content">
                    <div className="results-header">
                        <span className="results-count">{movies.length} movies found</span>
                        <div className="sort-wrapper">
                            <span>Sort by:</span>
                            <div className="sort-dropdown-small">
                                <CustomDropdown
                                    options={sortOptions}
                                    value={activeFilters.sort_by}
                                    onChange={(val) => handleFilterChange('sort_by', val)}
                                />
                            </div>
                        </div>
                        <button
                            className="btn-mobile-filter"
                            onClick={() => setShowMobileFilters(true)}
                        >
                            <Filter size={18} />
                            Filters
                        </button>
                    </div>

                    {loading ? (
                        <div className="movies-loading">
                            <Loader2 className="animate-spin" size={48} />
                            <p>Loading movies...</p>
                        </div>
                    ) : error ? (
                        <div className="movies-error">
                            <p>{error}</p>
                            <button onClick={() => fetchMovies(searchQuery, activeFilters)} className="btn-retry">Retry</button>
                        </div>
                    ) : movies.length > 0 ? (
                        <div className="movies-grid">
                            {movies.map(movie => (
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
                                        year: parseInt(movie.year) || new Date(movie.release_date).getFullYear() || new Date().getFullYear()
                                    }}
                                />
                            ))}
                        </div>
                    ) : (
                        <div className="no-results">
                            <img src="/assets/no-results.png" alt="No results" onError={(e) => e.target.style.display = 'none'} />
                            <h3>No movies found</h3>
                            <p>Try adjusting your search or filters to find what you're looking for.</p>
                            <button className="btn-reset" onClick={clearFilters}>Reset All</button>
                        </div>
                    )}
                </div>
            </main>

            {/* Mobile Filter Overlay */}
            {showMobileFilters && (
                <div className="mobile-filter-overlay">
                    <div className="mobile-filter-content">
                        <div className="mobile-filter-header">
                            <h2>Filters</h2>
                            <button onClick={() => setShowMobileFilters(false)}>
                                <X size={24} />
                            </button>
                        </div>
                        <div className="mobile-filter-body">
                            {/* Replicate filter sections here or extract into component */}
                            <CustomDropdown
                                label="Genre"
                                options={genreOptions}
                                value={activeFilters.genre}
                                onChange={(val) => handleFilterChange('genre', val)}
                            />

                            <CustomDropdown
                                label="Language"
                                options={languageOptions}
                                value={activeFilters.language}
                                onChange={(val) => handleFilterChange('language', val)}
                            />

                            <CustomDropdown
                                label="Min Rating"
                                options={ratingOptions}
                                value={activeFilters.rating}
                                onChange={(val) => handleFilterChange('rating', val)}
                            />
                        </div>
                        <div className="mobile-filter-footer">
                            <button className="btn-apply" onClick={() => setShowMobileFilters(false)}>Apply Filters</button>
                        </div>
                    </div>
                </div>
            )}

            <Footer />
        </div>
    );
};

export default Movies;

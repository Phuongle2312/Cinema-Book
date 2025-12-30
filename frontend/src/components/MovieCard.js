import React from 'react';
import { Star, Play, Calendar } from 'lucide-react';
import { Link } from 'react-router-dom';
import './MovieCard.css';

const MovieCard = ({ movie }) => {
    return (
        <div className="movie-card">
            <Link to={`/movies/${movie.id}`} className="movie-poster">
                <img src={movie.poster} alt={movie.title} />
                <div className="movie-overlay">
                    <button className="btn-play-overlay">
                        <Play fill="black" size={24} color="black" />
                    </button>
                    <div className="overlay-info">
                        <span className="rating">
                            <Star size={14} fill="#ffd700" color="#ffd700" />
                            {movie.rating}
                        </span>
                        <span className="duration">{movie.duration}</span>
                    </div>
                </div>
            </Link>
            <div className="movie-details">
                <Link to={`/movies/${movie.id}`}>
                    <h3 className="movie-title">{movie.title}</h3>
                </Link>
                <div className="movie-meta">
                    <span className="genre">
                        {movie.genre}
                        <span className="separator">, </span>
                        Chiếu Rạp
                    </span>
                    <span className="year">
                        <Calendar size={14} />
                        {movie.year}
                    </span>
                </div>
                <Link to={`/booking/movie/${movie.id}`} className="btn-book">Book Now</Link>
            </div>
        </div>
    );
};

export default MovieCard;

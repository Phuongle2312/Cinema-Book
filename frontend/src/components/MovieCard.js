import React from 'react';
import { Star, Play, Calendar } from 'lucide-react';
import './MovieCard.css';

const MovieCard = ({ movie }) => {
    return (
        <div className="movie-card">
            <div className="movie-poster">
                <img src={movie.poster} alt={movie.title} />
                <div className="movie-overlay">
                    <button className="btn-play">
                        <Play fill="white" size={24} />
                    </button>
                    <div className="movie-info-quick">
                        <span className="rating">
                            <Star size={16} fill="#ffd700" color="#ffd700" />
                            {movie.rating}
                        </span>
                        <span className="duration">{movie.duration}</span>
                    </div>
                </div>
            </div>
            <div className="movie-details">
                <h3 className="movie-title">{movie.title}</h3>
                <div className="movie-meta">
                    <span className="genre">{movie.genre}</span>
                    <span className="year">
                        <Calendar size={14} />
                        {movie.year}
                    </span>
                </div>
                <button className="btn-book">Book Now</button>
            </div>
        </div>
    );
};

export default MovieCard;

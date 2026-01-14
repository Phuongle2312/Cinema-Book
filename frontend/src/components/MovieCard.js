import React from 'react';
import { Play, Ticket } from 'lucide-react';
import { Link } from 'react-router-dom';
import './MovieCard.css';

const MovieCard = ({ movie }) => {
    // Ưu tiên dùng slug cho URL nếu có, nếu không dùng id
    const movieSlug = movie.slug || movie.id;
    const detailsLink = `/movies/${movieSlug}`;
    const bookingLink = `/booking/movie/${movieSlug}`;

    return (
        <div className="movie-card">
            <Link to={detailsLink} className="movie-poster">
                <img src={movie.poster} alt={movie.title} />
                <div className="movie-overlay">
                    <button className="btn-play-overlay">
                        <Play fill="white" size={24} color="white" />
                    </button>
                </div>
            </Link>

            <div className="movie-details">
                <Link to={detailsLink} className="movie-title">
                    {movie.title}
                </Link>

                <div className="movie-meta">
                    <span className="genre">{movie.genre?.split(',')[0] || 'Phim'}</span>
                    <span className="year">{movie.duration} min</span>
                </div>

                <Link to={bookingLink} className="btn-book flex items-center justify-center gap-2">
                    <Ticket size={16} />
                    <span>Book Now</span>
                </Link>
            </div>
        </div>
    );
};

export default MovieCard;

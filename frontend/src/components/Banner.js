import React, { useState, useEffect } from 'react';
import { Play, Heart, Info, ChevronLeft, ChevronRight, X } from 'lucide-react';
import './Banner.css';
import bannerData from '../data/banner.json';
import trailerData from '../data/trailer.json';
import { getYouTubeEmbedUrl } from '../utils/videoUtils';

const Banner = () => {
    const [currentIndex, setCurrentIndex] = useState(0);
    const [showTrailer, setShowTrailer] = useState(false);

    const nextSlide = () => {
        setCurrentIndex((prev) => (prev === bannerData.length - 1 ? 0 : prev + 1));
    };

    const prevSlide = () => {
        setCurrentIndex((prev) => (prev === 0 ? bannerData.length - 1 : prev - 1));
    };

    const currentMovie = bannerData[currentIndex];

    const currentTrailerUrl = getYouTubeEmbedUrl(currentMovie.trailerUrl || currentMovie.trailer);

    useEffect(() => {
        if (showTrailer) return; // Pause auto-slide when trailer is open
        const timer = setInterval(nextSlide, 12000);
        return () => clearInterval(timer);
    }, [showTrailer]);

    const handlePlayClick = () => {
        if (currentTrailerUrl) {
            setShowTrailer(true);
        } else {
            alert("Trailer not available");
        }
    };

    const closeTrailer = () => {
        setShowTrailer(false);
    };

    return (
        <div className="banner-carousel">
            <div className="banner-slide" key={currentMovie.id}>
                <div className="banner-image">
                    <img src={currentMovie.image} alt={currentMovie.title} />
                    <div className="banner-overlay"></div>
                </div>

                <div className="container banner-content">
                    <div className="banner-info">
                        <div className="banner-title-group">
                            {currentMovie.titleImage ? (
                                <>
                                    <img
                                        src={currentMovie.titleImage}
                                        alt={currentMovie.title}
                                        className="banner-title-image"
                                    />
                                    <h3 className="banner-title-secondary">{currentMovie.title}</h3>
                                </>
                            ) : (
                                <h1 className="banner-title">{currentMovie.title}</h1>
                            )}
                            <h2 className="banner-subtitle">{currentMovie.subTitle}</h2>
                        </div>

                        <h3 className="banner-title-english">{currentMovie.titleEnglish}</h3>

                        <div className="banner-meta">
                            <span className="meta-badge imdb">IMDb {currentMovie.rating}</span>
                            <span className="meta-badge quality">{currentMovie.quality}</span>
                            <span className="meta-badge age">{currentMovie.age}</span>
                            <span className="meta-item">{currentMovie.year}</span>
                            <span className="meta-item">{currentMovie.duration}</span>
                        </div>

                        <div className="banner-genres">
                            {currentMovie.genres.map((genre, idx) => (
                                <span key={idx} className="genre-pill">{genre}</span>
                            ))}
                        </div>

                        <p className="banner-description">{currentMovie.description}</p>

                        <div className="banner-actions">
                            <button className="btn-play-circle" onClick={handlePlayClick}>
                                <Play fill="currentColor" size={28} />
                            </button>
                            <div className="btn-group-pills">
                                <button className="btn-pill">
                                    <Heart size={20} />
                                </button>
                                <button className="btn-pill">
                                    <Info size={20} />
                                </button>
                            </div>
                        </div>
                    </div>

                    <div className="banner-thumbnails">
                        {bannerData.map((movie, idx) => (
                            <div
                                key={movie.id}
                                className={`thumbnail ${idx === currentIndex ? 'active' : ''}`}
                                onClick={() => setCurrentIndex(idx)}
                            >
                                <img src={movie.image} alt={movie.title} />
                            </div>
                        ))}
                    </div>
                </div>
            </div>

            {/* Trailer Modal */}
            {showTrailer && currentTrailerUrl && (
                <div className="trailer-modal" onClick={closeTrailer}>
                    <div className="trailer-content" onClick={(e) => e.stopPropagation()}>
                        <button className="close-trailer" onClick={closeTrailer}>
                            <X size={24} />
                        </button>
                        <iframe
                            width="100%"
                            height="100%"
                            src={`${currentTrailerUrl}?autoplay=1`}
                            title="Movie Trailer"
                            frameBorder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowFullScreen
                        ></iframe>
                    </div>
                </div>
            )}
        </div>
    );
};

export default Banner;

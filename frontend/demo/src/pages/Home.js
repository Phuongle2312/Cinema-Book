import React from 'react';
import Navbar from '../components/Navbar';
import Banner from '../components/Banner';
import MovieCard from '../components/MovieCard';
import Footer from '../components/Footer';
import './Home.css';
import { ChevronRight } from 'lucide-react';
import bannerData from '../data/banner.json';
import movieData from '../data/movies.json';

const Home = () => {
    const nowShowingMovies = movieData;

    return (
        <div className="home-page">
            <Navbar />

            {/* Cinematic Banner */}
            <Banner />

            {/* Movies Grid */}
            <section className="movies-section container">
                <div className="section-header">
                    <h2 className="section-title">Now Showing</h2>
                    <a href="#" className="view-all">
                        View All
                        <ChevronRight size={18} />
                    </a>
                </div>

                <div className="movies-grid">
                    {nowShowingMovies.slice(0, 5).map(movie => (
                        <MovieCard
                            key={movie.id}
                            movie={{
                                id: movie.id,
                                title: movie.title,
                                poster: movie.image,
                                rating: parseFloat(movie.rating),
                                duration: movie.duration,
                                genre: movie.genres.slice(0, 2).join(', '),
                                year: parseInt(movie.year)
                            }}
                        />
                    ))}
                </div>
            </section>

            <Footer />
        </div>
    );
};

export default Home;

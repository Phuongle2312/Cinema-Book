import React from 'react';
import { Link } from 'react-router-dom';
import './Sitemap.css';
import { Map, Film, User, Info, Shield } from 'lucide-react';

const Sitemap = () => {
    return (
        <div className="sitemap-container">
            <div className="sitemap-header">
                <h1>Sitemap</h1>
                <p>Overview of CineBook structure</p>
            </div>

            <div className="sitemap-grid">
                {/* Main Navigation */}
                <div className="sitemap-section">
                    <div className="section-title">
                        <Map size={24} className="section-icon" />
                        <h2>Main Navigation</h2>
                    </div>
                    <ul className="sitemap-links">
                        <li><Link to="/">Home</Link></li>
                        <li><Link to="/movies">Movies</Link></li>
                        <li><Link to="/cinemas">Cinemas / Theaters</Link></li>
                        <li><Link to="/events">Events & Offers</Link></li>
                    </ul>
                </div>

                {/* Movies */}
                <div className="sitemap-section">
                    <div className="section-title">
                        <Film size={24} className="section-icon" />
                        <h2>Movies</h2>
                    </div>
                    <ul className="sitemap-links">
                        <li><Link to="/movies?tab=now_showing">Now Showing</Link></li>
                        <li><Link to="/movies?tab=coming_soon">Coming Soon</Link></li>
                        <li><Link to="/movies/featured">Featured Movies</Link></li>
                    </ul>
                </div>

                {/* Account & User */}
                <div className="sitemap-section">
                    <div className="section-title">
                        <User size={24} className="section-icon" />
                        <h2>Account</h2>
                    </div>
                    <ul className="sitemap-links">
                        <li><Link to="/login">Login</Link></li>
                        <li><Link to="/register">Register</Link></li>
                        <li><Link to="/profile">My Profile</Link></li>
                        <li><Link to="/profile?tab=bookings">My Bookings</Link></li>
                    </ul>
                </div>

                {/* Support & Legal */}
                <div className="sitemap-section">
                    <div className="section-title">
                        <Info size={24} className="section-icon" />
                        <h2>Support & Legal</h2>
                    </div>
                    <ul className="sitemap-links">
                        <li><Link to="/faq">FAQ</Link></li>
                        <li><Link to="/terms">Terms & Conditions</Link></li>
                        <li><Link to="/privacy">Privacy Policy</Link></li>
                        <li><Link to="/booking-guide">Booking Guide</Link></li>
                    </ul>
                </div>

                {/* Admin Area (Restricted) */}
                <div className="sitemap-section">
                    <div className="section-title">
                        <Shield size={24} className="section-icon" />
                        <h2>Administration</h2>
                    </div>
                    <ul className="sitemap-links">
                        <li><Link to="/admin">Admin Dashboard</Link></li>
                        <li><Link to="/admin/login">Admin Login</Link></li>
                    </ul>
                </div>
            </div>
        </div>
    );
};

export default Sitemap;

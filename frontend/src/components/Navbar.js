import React, { useState, useEffect } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { Search, Popcorn, User, Menu, X } from 'lucide-react';
import './Navbar.css';

const Navbar = () => {
    const [isScrolled, setIsScrolled] = useState(false);
    const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);

    useEffect(() => {
        const handleScroll = () => {
            setIsScrolled(window.scrollY > 20);
        };
        window.addEventListener('scroll', handleScroll);
        return () => window.removeEventListener('scroll', handleScroll);
    }, []);

    return (
        <nav className={`navbar ${isScrolled ? 'scrolled' : ''}`}>
            <div className="container nav-content">
                <div className="nav-left">
                    <Link to="/" className="logo">
                        <Popcorn className="logo-icon" />
                        <span>CINE<span>BOOK</span></span>
                    </Link>
                    <ul className="nav-links">
                        <li><Link to="/" className="active">Movies</Link></li>
                        <li><a href="#">Cinemas</a></li>
                        <li><a href="#">Offers</a></li>
                        <li><a href="#">Events</a></li>
                    </ul>
                </div>

                <div className="nav-right">
                    <div className="search-box">
                        <Search size={20} />
                        <input type="text" placeholder="Search movies..." />
                    </div>
                    <Link to="/login" className="btn-login">
                        <User size={20} />
                        <span>Login</span>
                    </Link>
                    <button className="mobile-menu-btn" onClick={() => setIsMobileMenuOpen(!isMobileMenuOpen)}>
                        {isMobileMenuOpen ? <X size={24} /> : <Menu size={24} />}
                    </button>
                </div>
            </div>

            {/* Mobile Menu */}
            <div className={`mobile-menu ${isMobileMenuOpen ? 'open' : ''}`}>
                <ul>
                    <li><Link to="/" onClick={() => setIsMobileMenuOpen(false)}>Movies</Link></li>
                    <li><a href="#" onClick={() => setIsMobileMenuOpen(false)}>Cinemas</a></li>
                    <li><a href="#" onClick={() => setIsMobileMenuOpen(false)}>Offers</a></li>
                    <li><a href="#" onClick={() => setIsMobileMenuOpen(false)}>Events</a></li>
                    <li><Link to="/login" className="btn-login-mobile" onClick={() => setIsMobileMenuOpen(false)}>Login</Link></li>
                </ul>
            </div>
        </nav>
    );
};

export default Navbar;

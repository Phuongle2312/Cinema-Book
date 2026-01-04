import React, { useState, useEffect } from 'react';
import { NavLink, Link, useNavigate } from 'react-router-dom';
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

    const [searchQuery, setSearchQuery] = useState('');
    const navigate = useNavigate();

    const handleSearch = (e) => {
        e.preventDefault();
        if (searchQuery.trim()) {
            navigate(`/movies?q=${encodeURIComponent(searchQuery)}`);
            setSearchQuery('');
        }
    };

    return (
        <nav className={`navbar ${isScrolled ? 'scrolled' : ''}`}>
            <div className="container nav-content">
                <div className="nav-left">
                    <Link to="/" className="logo">
                        <Popcorn className="logo-icon" />
                        <span>CINE<span>BOOK</span></span>
                    </Link>
                    <ul className="nav-links">
                        <li><NavLink to="/movies">Movies</NavLink></li>
                        <li><NavLink to="/cinemas">Cinemas</NavLink></li>
                        <li><NavLink to="/offers">Offers</NavLink></li>
                        <li><NavLink to="/events">Events</NavLink></li>
                    </ul>
                </div>

                <div className="nav-right">
                    <form className="search-box" onSubmit={handleSearch}>
                        <Search size={20} className="search-icon" onClick={handleSearch} style={{ cursor: 'pointer' }} />
                        <input
                            type="text"
                            placeholder="Search movies..."
                            value={searchQuery}
                            onChange={(e) => setSearchQuery(e.target.value)}
                        />
                    </form>
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
                    <li><NavLink to="/movies" onClick={() => setIsMobileMenuOpen(false)}>Movies</NavLink></li>
                    <li><NavLink to="/cinemas" onClick={() => setIsMobileMenuOpen(false)}>Cinemas</NavLink></li>
                    <li><NavLink to="/offers" onClick={() => setIsMobileMenuOpen(false)}>Offers</NavLink></li>
                    <li><NavLink to="/events" onClick={() => setIsMobileMenuOpen(false)}>Events</NavLink></li>
                    <li><Link to="/login" className="btn-login-mobile" onClick={() => setIsMobileMenuOpen(false)}>Login</Link></li>
                </ul>
            </div>
        </nav>
    );
};

export default Navbar;

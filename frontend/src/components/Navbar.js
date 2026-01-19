import React, { useState, useEffect } from 'react';
import { NavLink, Link, useNavigate } from 'react-router-dom';
import { Search, Popcorn, User, Menu, X } from 'lucide-react';
import './Navbar.css';

import { useAuth } from '../context/AuthContext';

const Navbar = () => {
    const { isAuthenticated, user, logout } = useAuth();
    const [isScrolled, setIsScrolled] = useState(false);
    const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);
    const [isUserMenuOpen, setIsUserMenuOpen] = useState(false);

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

    const handleLogout = async () => {
        await logout();
        navigate('/login');
        setIsUserMenuOpen(false);
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

                    {isAuthenticated ? (
                        <div className="user-menu-container" style={{ position: 'relative' }}>
                            <div
                                className="user-trigger"
                                onClick={() => setIsUserMenuOpen(!isUserMenuOpen)}
                                style={{ display: 'flex', alignItems: 'center', gap: '8px', cursor: 'pointer', color: 'white' }}
                            >
                                <span className="user-name">
                                    {user?.role === 'admin' ? `Admin: ${user?.name}` : `Hi, ${user?.name}`}
                                </span>
                                <div className="avatar-circle" style={{ width: '35px', height: '35px', borderRadius: '50%', background: user?.role === 'admin' ? '#ffcc00' : '#e50914', color: user?.role === 'admin' ? '#000' : '#fff', display: 'flex', alignItems: 'center', justifyContent: 'center', fontWeight: 'bold' }}>
                                    {user?.name?.charAt(0).toUpperCase()}
                                </div>
                            </div>

                            {isUserMenuOpen && (
                                <div className="user-dropdown" style={{
                                    position: 'absolute',
                                    top: '120%',
                                    right: 0,
                                    background: '#1a1a1a',
                                    border: '1px solid #333',
                                    borderRadius: '8px',
                                    padding: '10px 0',
                                    width: '180px',
                                    boxShadow: '0 4px 12px rgba(0,0,0,0.5)',
                                    zIndex: 1000
                                }}>
                                    {user?.role === 'admin' ? (
                                        <>
                                            <div style={{ padding: '10px 20px', fontSize: '12px', color: '#888', borderBottom: '1px solid #333', marginBottom: '5px' }}>
                                                {user?.email}
                                            </div>
                                            <Link to="/admin/dashboard" className="dropdown-item" style={{ display: 'block', padding: '10px 20px', color: '#ffcc00', textDecoration: 'none', transition: 'background 0.2s' }} onClick={() => setIsUserMenuOpen(false)}>Admin Dashboard</Link>
                                            <div style={{ padding: '10px 20px', fontSize: '12px', color: '#666', fontStyle: 'italic' }}>Authorized Access Only</div>
                                        </>
                                    ) : (
                                        <>
                                            <div style={{ padding: '10px 20px', fontSize: '12px', color: '#888', borderBottom: '1px solid #333', marginBottom: '5px' }}>
                                                {user?.email}
                                            </div>
                                            <Link to="/profile" className="dropdown-item" style={{ display: 'block', padding: '10px 20px', color: '#fff', textDecoration: 'none', transition: 'background 0.2s' }} onClick={() => setIsUserMenuOpen(false)}>My Profile</Link>
                                        </>
                                    )}

                                    <div style={{ height: '1px', background: '#333', margin: '5px 0' }}></div>
                                    <button onClick={handleLogout} style={{ display: 'block', width: '100%', textAlign: 'left', padding: '10px 20px', background: 'none', border: 'none', color: '#ff4d4d', cursor: 'pointer', fontSize: '14px' }}>Logout</button>
                                </div>
                            )}
                        </div>
                    ) : (
                        <Link to="/login" className="btn-login">
                            <User size={20} />
                            <span>Login</span>
                        </Link>
                    )}

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

                    {isAuthenticated ? (
                        <>
                            <li style={{ borderTop: '1px solid rgba(255,255,255,0.1)', marginTop: '10px', paddingTop: '10px' }}>
                                <Link to="/profile" onClick={() => setIsMobileMenuOpen(false)} style={{ color: '#e50914' }}>Hi, {user?.name}</Link>
                            </li>
                            <li><Link to="/user/bookings" onClick={() => setIsMobileMenuOpen(false)}>My Bookings</Link></li>
                            {user?.role === 'admin' && (
                                <li><Link to="/admin/dashboard" onClick={() => setIsMobileMenuOpen(false)}>Admin Dashboard</Link></li>
                            )}
                            <li>
                                <button
                                    onClick={() => { handleLogout(); setIsMobileMenuOpen(false); }}
                                    style={{ background: 'none', border: 'none', color: '#ff4d4d', fontSize: '1.2rem', padding: '1rem', width: '100%', textAlign: 'left', cursor: 'pointer' }}
                                >
                                    Logout
                                </button>
                            </li>
                        </>
                    ) : (
                        <li><Link to="/login" className="btn-login-mobile" onClick={() => setIsMobileMenuOpen(false)}>Login</Link></li>
                    )}
                </ul>
            </div>
        </nav>
    );
};

export default Navbar;

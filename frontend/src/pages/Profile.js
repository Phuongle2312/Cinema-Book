import React, { useState, useEffect } from 'react';
import { useAuth } from '../context/AuthContext';
import { LogOut, Ticket, Calendar, MapPin, Clock } from 'lucide-react';
import Navbar from '../components/Navbar';
import bookingService from '../services/bookingService';
import { Link, useNavigate } from 'react-router-dom';
import './Profile.css';

const Profile = () => {
    const { user, logout } = useAuth();
    const navigate = useNavigate();

    const [bookings, setBookings] = useState([]);
    const [activeTab, setActiveTab] = useState('upcoming'); // upcoming | past
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        const fetchBookings = async () => {
            try {
                setLoading(true);
                const data = await bookingService.getUserBookings({ type: activeTab });
                setBookings(data.data || []);
            } catch (err) {
                console.error("Failed to fetch bookings", err);
            } finally {
                setLoading(false);
            }
        };

        if (user) {
            fetchBookings();
        }
    }, [user, activeTab]);

    const handleLogout = async () => {
        await logout();
        navigate('/');
    };

    if (!user) return null;

    return (
        <div className="profile-page">
            <Navbar />
            <div className="container profile-container">
                {/* Sidebar */}
                <aside className="profile-sidebar">
                    <div className="user-card">
                        <div className="user-avatar">
                            <span className="text-2xl font-bold">{user.name.charAt(0).toUpperCase()}</span>
                        </div>
                        <h3>{user.name}</h3>
                        <p>{user.email}</p>
                    </div>

                    <nav className="profile-nav">
                        <button className="nav-item active">
                            <Ticket size={20} /> My Bookings
                        </button>
                        <button className="nav-item text-red-500 hover:bg-red-500/10" onClick={handleLogout}>
                            <LogOut size={20} /> Logout
                        </button>
                    </nav>
                </aside>

                {/* Content */}
                <main className="profile-content">
                    <div className="content-header">
                        <h1>My Bookings</h1>
                        <div className="tabs">
                            <button
                                className={`tab ${activeTab === 'upcoming' ? 'active' : ''}`}
                                onClick={() => setActiveTab('upcoming')}
                            >
                                Upcoming
                            </button>
                            <button
                                className={`tab ${activeTab === 'past' ? 'active' : ''}`}
                                onClick={() => setActiveTab('past')}
                            >
                                Past History
                            </button>
                        </div>
                    </div>

                    <div className="bookings-list">
                        {loading ? (
                            <div className="text-center py-10 opacity-50">Loading...</div>
                        ) : bookings.length > 0 ? (
                            bookings.map(booking => (
                                <div key={booking.booking_id} className="booking-card">
                                    <div className="booking-poster">
                                        <img src={booking.showtime.movie.poster_url} alt={booking.showtime.movie.title} />
                                    </div>
                                    <div className="booking-details">
                                        <div className="booking-header-row">
                                            <h3>{booking.showtime.movie.title}</h3>
                                            <span className={`status-badge ${booking.status}`}>{booking.status}</span>
                                        </div>

                                        <div className="booking-info-grid">
                                            <div className="info-item">
                                                <Calendar size={16} />
                                                <span>{new Date(booking.showtime.start_time).toLocaleDateString()}</span>
                                            </div>
                                            <div className="info-item">
                                                <Clock size={16} />
                                                <span>{new Date(booking.showtime.start_time).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}</span>
                                            </div>
                                            <div className="info-item">
                                                <MapPin size={16} />
                                                <span>{booking.showtime.room.theater.name} - {booking.showtime.room.name}</span>
                                            </div>
                                        </div>

                                        <div className="booking-footer-row">
                                            <span className="seats-text">
                                                Seats: {booking.seats.map(s => `${s.row}${s.number}`).join(', ')}
                                            </span>
                                            {booking.status === 'confirmed' && (
                                                <Link to={`/eticket/${booking.booking_id}`} className="btn-view-ticket">
                                                    View Ticket
                                                </Link>
                                            )}
                                        </div>
                                    </div>
                                </div>
                            ))
                        ) : (
                            <div className="empty-state">
                                <Ticket size={48} />
                                <p>No {activeTab} bookings found.</p>
                                <Link to="/" className="btn-browse">Browse Movies</Link>
                            </div>
                        )}
                    </div>
                </main>
            </div>
        </div>
    );
};

export default Profile;

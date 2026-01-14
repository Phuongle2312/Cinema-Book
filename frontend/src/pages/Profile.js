import React, { useState, useEffect } from 'react';
import { useAuth } from '../context/AuthContext';
import { User, LogOut, Ticket, Calendar, MapPin, Clock, Heart, CreditCard, CheckCircle, XCircle, AlertCircle } from 'lucide-react';
import Navbar from '../components/Navbar';
import BookingTimer from '../components/BookingTimer';
import bookingService from '../services/bookingService';
import wishlistService from '../services/wishlistService';
import paymentService from '../services/paymentService';
import { Link, useNavigate } from 'react-router-dom';
import './Profile.css';

const Profile = () => {
    const { user, logout } = useAuth();
    const navigate = useNavigate();

    const [bookings, setBookings] = useState([]);
    const [wishlist, setWishlist] = useState([]);
    const [payments, setPayments] = useState([]);
    const [activeTab, setActiveTab] = useState('personal'); // personal | upcoming | past | wishlist | payments
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        const fetchData = async () => {
            try {
                setLoading(true);
                if (activeTab === 'wishlist') {
                    const data = await wishlistService.getWishlist();
                    setWishlist(data.data || []);
                } else if (activeTab === 'payments') {
                    const data = await paymentService.getPaymentHistory();
                    setPayments(data.data || []);
                } else if (activeTab !== 'personal') {
                    const data = await bookingService.getUserBookings({ type: activeTab });
                    setBookings(data.data || []);
                }
            } catch (err) {
                console.error("Failed to fetch data", err);
            } finally {
                setLoading(false);
            }
        };

        if (user) {
            fetchData();
        }
    }, [user, activeTab]);

    const handleLogout = async () => {
        await logout();
        navigate('/');
    };

    const getPaymentStatusIcon = (status) => {
        switch (status) {
            case 'approved':
                return <CheckCircle size={16} className="text-green-500" />;
            case 'rejected':
                return <XCircle size={16} className="text-red-500" />;
            default:
                return <AlertCircle size={16} className="text-yellow-500" />;
        }
    };

    const getPaymentStatusText = (status) => {
        switch (status) {
            case 'approved':
                return 'Approved';
            case 'rejected':
                return 'Rejected';
            default:
                return 'Pending';
        }
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
                        <button
                            className={`nav-item ${activeTab === 'personal' ? 'active' : ''}`}
                            onClick={() => setActiveTab('personal')}
                        >
                            <User size={20} /> Personal Info
                        </button>
                        <button
                            className={`nav-item ${activeTab === 'upcoming' || activeTab === 'past' ? 'active' : ''}`}
                            onClick={() => setActiveTab('upcoming')}
                        >
                            <Ticket size={20} /> My Bookings
                        </button>
                        <button
                            className={`nav-item ${activeTab === 'payments' ? 'active' : ''}`}
                            onClick={() => setActiveTab('payments')}
                        >
                            <CreditCard size={20} /> Payment History
                        </button>
                        <button
                            className={`nav-item ${activeTab === 'wishlist' ? 'active' : ''}`}
                            onClick={() => setActiveTab('wishlist')}
                        >
                            <Heart size={20} /> Wishlist
                        </button>
                        <button className="nav-item text-red-500 hover:bg-red-500/10" onClick={handleLogout}>
                            <LogOut size={20} /> Logout
                        </button>
                    </nav>
                </aside>

                {/* Content */}
                <main className="profile-content">
                    <div className="content-header">
                        <h1>
                            {activeTab === 'wishlist' ? 'My Wishlist' :
                                activeTab === 'personal' ? 'Account Settings' :
                                    activeTab === 'payments' ? 'Payment History' : 'My Bookings'}
                        </h1>
                        {(activeTab === 'upcoming' || activeTab === 'past') && (
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
                        )}
                    </div>

                    <div className="bookings-list">
                        {loading ? (
                            <div className="text-center py-10 opacity-50">Loading...</div>
                        ) : activeTab === 'personal' ? (
                            <div className="personal-info-card">
                                <div className="info-group">
                                    <label>Full Name</label>
                                    <input type="text" value={user.name} disabled />
                                </div>
                                <div className="info-group">
                                    <label>Email Address</label>
                                    <input type="email" value={user.email} disabled />
                                </div>
                                <div className="info-group">
                                    <label>Member Since</label>
                                    <input type="text" value={new Date(user.created_at || Date.now()).toLocaleDateString('vi-VN')} disabled />
                                </div>
                                <p className="text-muted mt-4 text-sm italic">* Information is managed by the system and cannot be changed directly here.</p>
                            </div>
                        ) : activeTab === 'payments' ? (
                            payments.length > 0 ? (
                                <div className="payments-list">
                                    {payments.map(payment => (
                                        <div key={payment.verification_id} className="payment-card">
                                            <div className="payment-header">
                                                <div className="payment-movie-info">
                                                    {payment.booking?.showtime?.movie?.poster_url && (
                                                        <img
                                                            src={payment.booking.showtime.movie.poster_url}
                                                            alt={payment.booking.showtime.movie.title}
                                                            className="payment-poster"
                                                        />
                                                    )}
                                                    <div>
                                                        <h4>{payment.booking?.showtime?.movie?.title || 'Unknown Movie'}</h4>
                                                        <p className="text-muted text-sm">
                                                            Booking #{payment.booking_id}
                                                        </p>
                                                    </div>
                                                </div>
                                                <div className={`payment-status ${payment.status}`}>
                                                    {getPaymentStatusIcon(payment.status)}
                                                    <span>{getPaymentStatusText(payment.status)}</span>
                                                </div>
                                            </div>
                                            <div className="payment-details">
                                                <div className="payment-info-row">
                                                    <span>Amount:</span>
                                                    <span className="font-bold">{parseFloat(payment.amount).toLocaleString()} VND</span>
                                                </div>
                                                <div className="payment-info-row">
                                                    <span>Method:</span>
                                                    <span>{payment.payment_method}</span>
                                                </div>
                                                <div className="payment-info-row">
                                                    <span>Submitted:</span>
                                                    <span>{new Date(payment.submitted_at).toLocaleString('vi-VN')}</span>
                                                </div>
                                                {payment.verified_at && (
                                                    <div className="payment-info-row">
                                                        <span>Verified:</span>
                                                        <span>{new Date(payment.verified_at).toLocaleString('vi-VN')}</span>
                                                    </div>
                                                )}
                                                {payment.admin_note && (
                                                    <div className="payment-note">
                                                        <span>Admin Note:</span>
                                                        <p>{payment.admin_note}</p>
                                                    </div>
                                                )}
                                            </div>
                                            {payment.status === 'approved' && (
                                                <Link to={`/eticket/${payment.booking_id}`} className="btn-view-ticket">
                                                    View E-Ticket
                                                </Link>
                                            )}
                                        </div>
                                    ))}
                                </div>
                            ) : (
                                <div className="empty-state">
                                    <CreditCard size={48} />
                                    <p>No payment history found.</p>
                                    <Link to="/movies" className="btn-browse">Browse Movies</Link>
                                </div>
                            )
                        ) : activeTab === 'wishlist' ? (
                            wishlist.length > 0 ? (
                                <div className="wishlist-grid">
                                    {wishlist.map(movie => (
                                        <Link to={`/movies/${movie.slug || movie.movie_id}`} key={movie.movie_id} className="wishlist-item">
                                            <div className="wishlist-poster">
                                                <img src={movie.poster_url} alt={movie.title} />
                                                <div className="wishlist-overlay">
                                                    <span>View Details</span>
                                                </div>
                                            </div>
                                            <h4>{movie.title}</h4>
                                        </Link>
                                    ))}
                                </div>
                            ) : (
                                <div className="empty-state">
                                    <Heart size={48} />
                                    <p>Your wishlist is empty.</p>
                                    <Link to="/movies" className="btn-browse">Explore Movies</Link>
                                </div>
                            )
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

                                        <div className="booking-footer-row flex items-center justify-between mt-4">
                                            <div className="flex items-center">
                                                <span className="seats-text mr-4">
                                                    Seats: {booking.seats.map(s => `${s.row}${s.number}`).join(', ')}
                                                </span>
                                                {booking.status === 'pending' && (
                                                    <BookingTimer expiresAt={booking.expires_at} />
                                                )}
                                            </div>
                                            {booking.status === 'confirmed' && (
                                                <Link to={`/eticket/${booking.booking_id}`} className="btn-view-ticket">
                                                    View Ticket
                                                </Link>
                                            )}
                                            {booking.status === 'pending' && (
                                                <Link
                                                    to={`/payment/${booking.booking_id}`}
                                                    className="btn-view-ticket"
                                                    style={{ backgroundColor: '#ca8a04', marginLeft: '10px' }}
                                                >
                                                    Continue Payment
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


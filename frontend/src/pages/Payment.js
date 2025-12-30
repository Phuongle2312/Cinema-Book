import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { Clock, CreditCard, Banknote, ShieldCheck } from 'lucide-react';
import Navbar from '../components/Navbar';
import bookingService from '../services/bookingService';
import './Payment.css';

const Payment = () => {
    const { bookingId } = useParams();
    const navigate = useNavigate();

    const [booking, setBooking] = useState(null);
    const [timeLeft, setTimeLeft] = useState(0);
    const [paymentMethod, setPaymentMethod] = useState('credit_card');
    const [loading, setLoading] = useState(true);
    const [processing, setProcessing] = useState(false);
    const [error, setError] = useState(null);

    useEffect(() => {
        const fetchBooking = async () => {
            try {
                const data = await bookingService.getBookingById(bookingId);
                setBooking(data.data);

                // Calculate time left
                if (data.data.expires_at) {
                    const expireTime = new Date(data.data.expires_at).getTime();
                    const now = new Date().getTime();
                    const diff = Math.floor((expireTime - now) / 1000);
                    setTimeLeft(diff > 0 ? diff : 0);
                }
            } catch (err) {
                setError("Failed to load booking details.");
            } finally {
                setLoading(false);
            }
        };
        fetchBooking();
    }, [bookingId]);

    // Timer Interval
    useEffect(() => {
        if (timeLeft <= 0) return;

        const interval = setInterval(() => {
            setTimeLeft(prev => {
                if (prev <= 1) {
                    clearInterval(interval);
                    return 0;
                }
                return prev - 1;
            });
        }, 1000);

        return () => clearInterval(interval);
    }, [timeLeft]);

    const formatTime = (seconds) => {
        const min = Math.floor(seconds / 60);
        const sec = seconds % 60;
        return `${min}:${sec < 10 ? '0' : ''}${sec}`;
    };

    const handlePayment = async () => {
        if (timeLeft === 0) {
            alert("Booking expired. Please try again.");
            navigate('/');
            return;
        }

        setProcessing(true);
        try {
            await bookingService.processPayment(bookingId, { payment_method: paymentMethod });
            // Success
            navigate(`/eticket/${bookingId}`);
        } catch (err) {
            alert(err.message || "Payment failed");
        } finally {
            setProcessing(false);
        }
    };

    if (loading) return <div className="loading-screen">Loading payment details...</div>;
    if (error) return <div className="error-screen">{error}</div>;

    if (!booking) return null;

    return (
        <div className="payment-page">
            <Navbar />
            <div className="container payment-container">
                <div className="payment-content">
                    <h1 className="payment-title">Confirm & Pay</h1>

                    {/* Timer Alert */}
                    <div className={`timer-alert ${timeLeft < 60 ? 'urgent' : ''}`}>
                        <Clock size={20} />
                        <span>Complete your payment in </span>
                        <strong>{formatTime(timeLeft)}</strong>
                    </div>

                    <div className="payment-grid">
                        {/* Summary Card */}
                        <div className="summary-card">
                            <div className="movie-summary-header">
                                <img src={booking.showtime?.movie?.poster_url} alt="Movie Poster" />
                                <div>
                                    <h3>{booking.showtime?.movie?.title}</h3>
                                    <p>{booking.showtime?.room?.theater?.name}</p>
                                    <p>{booking.showtime?.room?.name}</p>
                                    <p className="text-primary font-bold">
                                        {new Date(booking.showtime?.start_time).toLocaleString()}
                                    </p>
                                </div>
                            </div>

                            <div className="summary-details">
                                <div className="summary-row">
                                    <span>Seats</span>
                                    <span>{booking.seats?.map(s => `${s.row}${s.number}`).join(', ')}</span>
                                </div>
                                <div className="summary-row">
                                    <span>Seats Price</span>
                                    <span>{parseFloat(booking.seats_total).toLocaleString()} VND</span>
                                </div>
                                {booking.combo_total > 0 && (
                                    <div className="summary-row">
                                        <span>Combos</span>
                                        <span>{parseFloat(booking.combo_total).toLocaleString()} VND</span>
                                    </div>
                                )}
                                <div className="summary-divider"></div>
                                <div className="summary-row total">
                                    <span>Total Amount</span>
                                    <span>{parseFloat(booking.total_price).toLocaleString()} VND</span>
                                </div>
                            </div>
                        </div>

                        {/* Payment Methods */}
                        <div className="payment-methods-section">
                            <h2>Select Payment Method</h2>
                            <div className="methods-list">
                                <div
                                    className={`method-item ${paymentMethod === 'credit_card' ? 'selected' : ''}`}
                                    onClick={() => setPaymentMethod('credit_card')}
                                >
                                    <CreditCard size={24} />
                                    <span>Credit Card</span>
                                </div>
                                <div
                                    className={`method-item ${paymentMethod === 'momo' ? 'selected' : ''}`}
                                    onClick={() => setPaymentMethod('momo')}
                                >
                                    <img src="https://upload.wikimedia.org/wikipedia/vi/f/fe/MoMo_Logo.png" alt="Momo" className="method-icon" />
                                    <span>Momo E-Wallet</span>
                                </div>
                                <div
                                    className={`method-item ${paymentMethod === 'zalopay' ? 'selected' : ''}`}
                                    onClick={() => setPaymentMethod('zalopay')}
                                >
                                    <img src="https://cdn.haitrieu.com/wp-content/uploads/2022/10/Logo-ZaloPay-Square.png" alt="ZaloPay" className="method-icon" />
                                    <span>ZaloPay</span>
                                </div>
                            </div>

                            <button
                                className="btn-pay-now"
                                onClick={handlePayment}
                                disabled={processing || timeLeft === 0}
                            >
                                {processing ? 'Processing...' : `Pay ${parseFloat(booking.total_price).toLocaleString()} VND`}
                            </button>

                            <div className="secure-note">
                                <ShieldCheck size={16} />
                                <span>Payments are secure and encrypted</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default Payment;

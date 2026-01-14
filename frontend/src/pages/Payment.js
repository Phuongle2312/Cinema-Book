import React, { useState, useEffect } from 'react';
import { usePopup } from '../context/PopupContext';
import { useParams, useNavigate } from 'react-router-dom';
import { Clock, CreditCard, Banknote, ShieldCheck } from 'lucide-react';
import Navbar from '../components/Navbar';
import bookingService from '../services/bookingService';
import paymentService from '../services/paymentService';
import promotionService from '../services/promotionService';
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



    const { showError, showWarning } = usePopup();

    const handlePayment = async () => {
        if (timeLeft === 0) {
            showWarning("Booking expired. Please try again.");
            navigate('/');
            return;
        }

        setProcessing(true);
        try {
            // For now, treat 'credit_card' as manual process or 'bank_transfer'
            // If we want to simulate manual verification for ALL methods currently:

            // Construct FormData for submitPayment
            const formData = new FormData();
            formData.append('booking_id', bookingId);
            formData.append('payment_method', paymentMethod);
            // formData.append('payment_proof', file); // If we had file upload
            formData.append('customer_note', 'Manual payment via website');

            const result = await paymentService.submitPayment(formData);

            if (result.success) {
                // Navigate to Waiting Screen (BookingSuccess handles pending status)
                navigate(`/eticket/${bookingId}`);
            } else {
                // Fallback to old method if not manual? Or just error
                showError(result.message || "Payment submission failed");
            }
        } catch (err) {
            console.error(err);
            showError("An error occurred during payment submission");
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
                                <img
                                    src={booking.showtime?.movie?.poster_url || 'https://via.placeholder.com/300x450?text=No+Poster'}
                                    alt="Movie Poster"
                                    onError={(e) => { e.target.src = 'https://via.placeholder.com/300x450?text=No+Poster'; }}
                                />
                                <div>
                                    <h3>{booking.showtime?.movie?.title}</h3>
                                    <p>{booking.showtime?.room?.theater?.name}</p>
                                    <p>{booking.showtime?.room?.name}</p>
                                    <p className="text-primary font-bold">
                                        {booking.showtime?.start_time ? new Date(booking.showtime.start_time.includes('T') ? booking.showtime.start_time : booking.showtime.start_time.replace(' ', 'T')).toLocaleString('vi-VN', {
                                            day: '2-digit',
                                            month: '2-digit',
                                            year: 'numeric',
                                            hour: '2-digit',
                                            minute: '2-digit'
                                        }) : 'Invalid Date'}
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
                                    className={`method-item selected`}
                                >
                                    <CreditCard size={24} />
                                    <span>Credit Card</span>
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

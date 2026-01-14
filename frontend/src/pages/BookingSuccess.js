import React, { useState, useEffect, useRef } from 'react';
import { useParams, Link } from 'react-router-dom';
import { CheckCircle, Share2, Download, Printer, Loader2 } from 'lucide-react';
import Navbar from '../components/Navbar';
import bookingService from '../services/bookingService';
import './BookingSuccess.css';

const BookingSuccess = () => {
    const { bookingId } = useParams();
    const [eTicket, setETicket] = useState(null);
    const [status, setStatus] = useState('loading'); // loading, pending, confirmed, rejected, error
    const [error, setError] = useState(null);
    const ticketRef = useRef(null);
    const pollingRef = useRef(null);

    const fetchTicketOrStatus = async () => {
        try {
            // First try to get E-Ticket (works if confirmed)
            const ticketData = await bookingService.getETicket(bookingId);

            if (ticketData.success) {
                setETicket(ticketData.data);
                setStatus('confirmed');
                return true; // Stop polling
            }

            // If not found or error, check booking status directly
            const bookingData = await bookingService.getBookingById(bookingId);
            if (bookingData && bookingData.data) {
                const bookingStatus = bookingData.data.status;

                if (bookingStatus === 'confirmed') {
                    // Retry fetching ticket in next poll or immediate retry could be done
                    return false;
                } else if (bookingStatus === 'cancelled' || bookingStatus === 'rejected') {
                    setStatus('rejected');
                    return true; // Stop polling
                } else {
                    setStatus('pending');
                    return false; // Keep polling
                }
            } else {
                throw new Error("Could not check booking status");
            }

        } catch (err) {
            console.error("Polling error", err);
            // Don't stop polling immediately on network error, but maybe limit retries in real app
            // For now, if E-Ticket 404s, it might just mean not generated yet.
            // setStatus('pending'); // Assume pending if failed
            return false;
        }
    };

    useEffect(() => {
        let mounted = true;

        const startPolling = async () => {
            // Initial check
            const stop = await fetchTicketOrStatus();
            if (stop || !mounted) return;

            // Loop
            pollingRef.current = setInterval(async () => {
                const shouldStop = await fetchTicketOrStatus();
                if (shouldStop) {
                    clearInterval(pollingRef.current);
                }
            }, 3000); // Poll every 3 seconds
        };

        startPolling();

        return () => {
            mounted = false;
            if (pollingRef.current) clearInterval(pollingRef.current);
        };
    }, [bookingId]);

    const handlePrint = () => {
        window.print();
    };

    // --- RENDER STATES ---

    if (status === 'loading') return (
        <div className="success-page">
            <Navbar />
            <div className="flex flex-col items-center justify-center h-[60vh] text-white">
                <Loader2 className="animate-spin mb-4 text-red-500" size={48} />
                <p className="text-xl">Checking payment status...</p>
            </div>
        </div>
    );

    if (status === 'pending') return (
        <div className="success-page">
            <Navbar />
            <div className="container success-container text-center pt-20">
                <div className="bg-[#1a1a1a] p-10 rounded-2xl max-w-2xl mx-auto border border-gray-800">
                    <Loader2 className="animate-spin mx-auto text-yellow-500 mb-6" size={64} />
                    <h1 className="text-3xl font-bold text-white mb-4">Payment Verification in Progress</h1>
                    <p className="text-gray-400 text-lg mb-8">
                        We have received your payment proof. Please wait while an administrator verifies your transaction.
                        <br />
                        This page will automatically update once confirmed.
                    </p>
                    <div className="p-4 bg-[#252525] rounded-lg text-sm text-gray-400 inline-block">
                        Booking ID: <span className="text-white font-mono font-bold">#{bookingId}</span>
                    </div>
                </div>
            </div>
        </div>
    );

    if (status === 'rejected') return (
        <div className="success-page">
            <Navbar />
            <div className="container success-container text-center pt-20">
                <div className="bg-[#1a1a1a] p-10 rounded-2xl max-w-2xl mx-auto border border-red-900/50">
                    <div className="mx-auto bg-red-900/20 w-20 h-20 rounded-full flex items-center justify-center mb-6">
                        <span className="text-4xl text-red-500">✕</span>
                    </div>
                    <h1 className="text-3xl font-bold text-white mb-4">Payment Rejected</h1>
                    <p className="text-gray-400 text-lg mb-8">
                        Unfortunately, your payment could not be verified.
                        Please contact support or try booking again.
                    </p>
                    <Link to="/" className="bg-red-600 text-white px-8 py-3 rounded-lg hover:bg-red-700 transition">
                        Back to Home
                    </Link>
                </div>
            </div>
        </div>
    );

    if (error) return (
        <div className="success-page">
            <Navbar />
            <div className="error-screen text-white pt-20 text-center">
                <p className="text-xl mb-4">{error}</p>
                <Link to="/" className="text-primary hover:underline">Back to Home</Link>
            </div>
        </div>
    );

    if (!eTicket) return null; // Should not reach here if status is confirmed

    // --- CONFIRMED STATE (Original UI) ---
    return (
        <div className="success-page">
            <Navbar />
            <div className="container success-container">
                <div className="success-header">
                    <CheckCircle size={60} className="text-green-500" />
                    <h1>Booking Confirmed!</h1>
                    <p>Thank you for your purchase. A copy has been sent to your email.</p>
                </div>

                <div className="ticket-wrapper">
                    <div className="e-ticket" ref={ticketRef}>
                        <div className="ticket-header">
                            <div className="ticket-brand">CINE<span>BOOK</span></div>
                            <div className="ticket-id">#{eTicket.booking_code}</div>
                        </div>

                        <div className="ticket-body">
                            <div className="ticket-poster">
                                <img
                                    src={eTicket.movie.poster || 'https://via.placeholder.com/300x450?text=No+Poster'}
                                    alt="Movie Poster"
                                    onError={(e) => { e.target.src = 'https://via.placeholder.com/300x450?text=No+Poster'; }}
                                />
                            </div>
                            <div className="ticket-info">
                                <h2>{eTicket.movie.title}</h2>
                                <div className="info-row">
                                    <span className="label">Date</span>
                                    <span className="value">{eTicket.showtime.date}</span>
                                </div>
                                <div className="info-row">
                                    <span className="label">Time</span>
                                    <span className="value">{eTicket.showtime.time} - {eTicket.showtime.end_time}</span>
                                </div>
                                <div className="info-row">
                                    <span className="label">Theater</span>
                                    <span className="value">{eTicket.theater.name}</span>
                                </div>
                                <div className="info-row">
                                    <span className="label">Hall</span>
                                    <span className="value">{eTicket.theater.room}</span>
                                </div>
                                <div className="info-row">
                                    <span className="label">Seats</span>
                                    <span className="value hl">{eTicket.seats.map(s => s.label).join(', ')}</span>
                                </div>
                                {eTicket.combos.length > 0 && (
                                    <div className="info-row">
                                        <span className="label">Combos</span>
                                        <span className="value">{eTicket.combos.map(c => `${c.quantity}x ${c.name}`).join(', ')}</span>
                                    </div>
                                )}
                            </div>
                        </div>

                        <div className="ticket-qr-section">
                            <img src={eTicket.qr_code} alt="QR Code" className="qr-code" />
                            <p>Scan this QR code at the entrance</p>
                        </div>

                        <div className="ticket-footer">
                            <div className="payment-info">
                                <span>Total Paid: {parseFloat(eTicket.payment.total_price).toLocaleString()} VND</span>
                                <span>Method: {eTicket.payment.payment_method.toUpperCase().replace('_', ' ')}</span>
                            </div>
                        </div>

                        <div className="ticket-circles-top"></div>
                        <div className="ticket-circles-bottom"></div>
                    </div>

                    <div className="ticket-actions">
                        <button className="btn-action" onClick={handlePrint}>
                            <Printer size={20} /> Print Ticket
                        </button>
                        <button className="btn-action">
                            <Download size={20} /> Save Image
                        </button>
                    </div>

                    <Link to="/" className="btn-home">Back to Home</Link>
                </div>
            </div>
        </div>
    );
};

export default BookingSuccess;

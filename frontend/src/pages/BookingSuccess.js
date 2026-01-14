import React, { useState, useEffect, useRef } from 'react';
import { useParams, Link } from 'react-router-dom';
import { CheckCircle, Download, Printer } from 'lucide-react';
import Navbar from '../components/Navbar';
import bookingService from '../services/bookingService';
import './BookingSuccess.css';

const BookingSuccess = () => {
    const { bookingId } = useParams();
    const [eTicket, setETicket] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const ticketRef = useRef(null);

    useEffect(() => {
        const fetchETicket = async () => {
            try {
                const data = await bookingService.getETicket(bookingId);
                if (data.success) {
                    setETicket(data.data);
                } else {
                    setError(data.message || "Could not retrieve E-Ticket.");
                }
            } catch (err) {
                setError("An error occurred while loading your ticket.");
            } finally {
                setLoading(false);
            }
        };
        fetchETicket();
    }, [bookingId]);

    const handlePrint = () => {
        window.print();
    };

    if (loading) return (
        <div className="success-page">
            <Navbar />
            <div className="loading-screen text-white pt-20 text-center">Generating E-Ticket...</div>
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

    if (!eTicket) return (
        <div className="success-page">
            <Navbar />
            <div className="text-white pt-20 text-center">Ticket not found or still processing.</div>
        </div>
    );

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

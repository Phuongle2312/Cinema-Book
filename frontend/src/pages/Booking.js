import React, { useState, useEffect } from 'react';
import { useParams, useNavigate, useLocation } from 'react-router-dom';
import { ChevronLeft, Calendar, Clock, MapPin, Loader2 } from 'lucide-react';
import Navbar from '../components/Navbar';
import Footer from '../components/Footer';
import { useAuth } from '../context/AuthContext';
import movieService from '../services/movieService';
import showtimeService from '../services/showtimeService';
import bookingService from '../services/bookingService';
import './Booking.css';

const Booking = () => {
    const { movieId } = useParams();
    const navigate = useNavigate();
    const location = useLocation();
    const { isAuthenticated } = useAuth();

    // Data State
    const [movie, setMovie] = useState(null);
    const [showtimes, setShowtimes] = useState([]);
    const [seats, setSeats] = useState([]);
    const [seatMap, setSeatMap] = useState({});

    // UI Selection State
    const [selectedDate, setSelectedDate] = useState(new Date().toISOString().split('T')[0]);
    const [selectedCity, setSelectedCity] = useState('Hồ Chí Minh');
    const [selectedFormat, setSelectedFormat] = useState('2D Phụ Đề Anh');
    const [selectedShowtime, setSelectedShowtime] = useState(null);
    const [selectedSeats, setSelectedSeats] = useState([]);

    // Status State
    const [loading, setLoading] = useState(true);
    const [loadingSeats, setLoadingSeats] = useState(false);
    const [error, setError] = useState(null);

    const cities = ["Hồ Chí Minh", "Hà Nội", "Đà Nẵng", "Cần Thơ", "Đồng Nai", "Hải Phòng", "Quảng Ninh"];
    const formats = ["2D Phụ Đề Anh", "2D Phụ Đề Việt", "3D Phụ Đề Anh"];

    // Generate next 14 days
    const dates = Array.from({ length: 14 }, (_, i) => {
        const date = new Date();
        date.setDate(date.getDate() + i);
        return {
            full: date.toISOString().split('T')[0],
            day: date.toLocaleDateString('en-US', { weekday: 'short' }),
            date: date.getDate(),
            month: date.getMonth() + 1,
            monthStr: (date.getMonth() + 1).toString().padStart(2, '0')
        };
    });

    // 1. Fetch Movie Details
    useEffect(() => {
        const fetchMovie = async () => {
            try {
                const data = await movieService.getMovieById(movieId);
                setMovie(data.data || data);
            } catch (err) {
                setError("Failed to load movie details.");
            }
        };
        fetchMovie();
    }, [movieId]);

    // 2. Fetch Showtimes when Date changes
    useEffect(() => {
        const fetchShowtimes = async () => {
            try {
                setLoading(true);
                const data = await showtimeService.getShowtimes({
                    movie_id: movieId,
                    date: selectedDate
                });

                // Group showtimes by Theater and then Format
                const grouped = (data.data || []).reduce((acc, showtime) => {
                    const theaterName = showtime.room?.theater?.name || 'Unknown Theater';
                    const city = showtime.room?.theater?.city || 'Unknown City';
                    const format = showtime.format || 'Rạp 2D';

                    // Filter by selected city
                    if (city !== selectedCity) return acc;

                    if (!acc[theaterName]) acc[theaterName] = {};
                    if (!acc[theaterName][format]) acc[theaterName][format] = [];

                    acc[theaterName][format].push(showtime);
                    return acc;
                }, {});

                setShowtimes(grouped);
            } catch (err) {
                setError("Failed to load showtimes.");
            } finally {
                setLoading(false);
            }
        };

        if (movieId) {
            fetchShowtimes();
        }
    }, [movieId, selectedDate, selectedCity, selectedFormat]);

    // 3. Fetch Seats when Showtime selected
    const handleShowtimeSelect = async (showtime) => {
        try {
            setLoadingSeats(true);
            setSelectedShowtime(showtime);
            setSelectedSeats([]); // Reset seats

            const data = await showtimeService.getShowtimeSeats(showtime.showtime_id);
            setSeats(data.data.seats);
            setSeatMap(data.data.seat_map);
        } catch (err) {
            alert("Failed to load seat map.");
        } finally {
            setLoadingSeats(false);
        }
    };

    const handleBackToShowtimes = () => {
        setSelectedShowtime(null);
        setSelectedSeats([]);
    };

    const handleSeatClick = (seat) => {
        if (seat.status === 'booked' || seat.status === 'locked') return;

        const isSelected = selectedSeats.some(s => s.seat_id === seat.seat_id);

        if (isSelected) {
            setSelectedSeats(prev => prev.filter(s => s.seat_id !== seat.seat_id));
        } else {
            // Limit max 8 seats
            if (selectedSeats.length >= 8) {
                alert("You can only select up to 8 seats.");
                return;
            }
            setSelectedSeats(prev => [...prev, seat]);
        }
    };

    const calculateTotal = () => {
        if (!selectedShowtime) return 0;
        const basePrice = parseFloat(selectedShowtime.base_price);
        return selectedSeats.reduce((total, seat) => {
            return total + basePrice + parseFloat(seat.extra_price || 0);
        }, 0);
    };

    const handleContinue = async () => {
        if (selectedSeats.length === 0) return;

        // AUTH REMOVED FOR DEMO
        /*
        if (!isAuthenticated) {
            navigate('/login', { state: { from: location } });
            return;
        }
        */

        try {

            // Create pending booking
            const bookingData = {
                showtime_id: selectedShowtime.showtime_id,
                seat_ids: selectedSeats.map(s => s.seat_id),
                combos: [] // Future: Add combos
            };

            const response = await bookingService.createBooking(bookingData);

            if (response.success) {
                // Navigate to Payment
                navigate(`/payment/${response.data.booking_id}`);
            }
        } catch (err) {
            alert(err.message || "Booking failed. Please try again.");
        }
    };

    if (error) return <div className="text-center text-white pt-20">{error}</div>;
    if (!movie && loading) return <div className="flex justify-center items-center h-screen bg-[#0a0a0a]"><Loader2 className="animate-spin text-red-600" size={48} /></div>;

    return (
        <div className="booking-page">
            <Navbar />

            {!selectedShowtime ? (
                /* STEP 1: SELECT SHOWTIME - CGV DARK STYLE */
                <div className="booking-content-dark">
                    <div className="container">
                        <div className="booking-movie-header">
                            <h1>{movie?.title}</h1>
                            <div className="booking-movie-meta">
                                <span>{movie?.duration} min</span>
                                <span>{movie?.genres?.map(g => g.name).join(', ')}</span>
                            </div>
                        </div>

                        {/* Date Selector */}
                        <div className="cgv-date-selector">
                            <div className="date-track">
                                {dates.map((dateObj) => (
                                    <div
                                        key={dateObj.full}
                                        className={`cgv-date-card ${selectedDate === dateObj.full ? 'active' : ''}`}
                                        onClick={() => setSelectedDate(dateObj.full)}
                                    >
                                        <div className="date-month">{dateObj.monthStr}</div>
                                        <div className="date-weekday">{dateObj.day}</div>
                                        <div className="date-day-num">{dateObj.date}</div>
                                    </div>
                                ))}
                            </div>
                        </div>

                        {/* City Tabs */}
                        <div className="cgv-city-selector">
                            {cities.map(city => (
                                <button
                                    key={city}
                                    className={`city-tab ${selectedCity === city ? 'active' : ''}`}
                                    onClick={() => setSelectedCity(city)}
                                >
                                    {city}
                                </button>
                            ))}
                        </div>

                        {/* Format Filter */}
                        <div className="cgv-format-selector">
                            {formats.map(format => (
                                <button
                                    key={format}
                                    className={`format-btn ${selectedFormat === format ? 'active' : ''}`}
                                    onClick={() => setSelectedFormat(format)}
                                >
                                    {format}
                                </button>
                            ))}
                        </div>

                        {/* Showtimes List */}
                        <div className="cgv-showtime-list">
                            {loading ? (
                                <div className="py-20 text-center text-gray-800"><Loader2 className="animate-spin inline mr-2" /> Loading showtimes...</div>
                            ) : Object.keys(showtimes).length > 0 ? (
                                Object.entries(showtimes).map(([theater, formatsObj]) => (
                                    <div key={theater} className="cgv-theater-group">
                                        <h3 className="cgv-theater-name">{theater}</h3>

                                        {Object.entries(formatsObj).map(([formatName, times]) => (
                                            <div key={formatName} className="cgv-format-group">
                                                <div className="cgv-format-label">{formatName}</div>
                                                <div className="cgv-time-grid">
                                                    {times.map(time => (
                                                        <div
                                                            key={time.showtime_id}
                                                            className="cgv-time-slot"
                                                            onClick={() => handleShowtimeSelect(time)}
                                                        >
                                                            {new Date(time.start_time).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', hour12: false })}
                                                        </div>
                                                    ))}
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                ))
                            ) : (
                                <div className="text-center py-20 text-gray-500">No showtimes available for this selection.</div>
                            )}
                        </div>
                    </div>
                </div>
            ) : (
                /* STEP 2: SELECT SEATS */
                <div className="container seat-selection-container">
                    <button className="btn-back" onClick={handleBackToShowtimes}>
                        <ChevronLeft size={20} />
                        <span>Back to times</span>
                    </button>

                    <h2 className="text-2xl font-bold mb-8">Select Seats</h2>
                    <p className="mb-4 text-gray-400">{selectedShowtime.room?.theater?.name} - {selectedShowtime.room?.name} - {new Date(selectedShowtime.start_time).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}</p>

                    <div className="screen-container">
                        <div className="screen"></div>
                        <div className="screen-text">SCREEN</div>
                    </div>

                    {loadingSeats ? (
                        <div className="py-20"><Loader2 className="animate-spin inline" /> Loading seats...</div>
                    ) : (
                        <div className="seats-grid">
                            {Object.entries(seatMap).map(([row, rowSeats]) => (
                                <div key={row} className="seat-row">
                                    <span className="seat-row-label">{row}</span>
                                    {rowSeats.map(seat => {
                                        let statusClass = seat.status; // available, booked, locked
                                        if (selectedSeats.some(s => s.seat_id === seat.seat_id)) statusClass = 'selected';

                                        // Specific types
                                        if (seat.seat_type?.name === 'VIP') statusClass += ' vip';
                                        else statusClass += ' standard';

                                        return (
                                            <div
                                                key={seat.seat_id}
                                                className={`seat ${statusClass}`}
                                                onClick={() => handleSeatClick(seat)}
                                                title={`${seat.row}${seat.number} - ${seat.seat_type?.name} - ${parseInt(seat.extra_price) + parseFloat(selectedShowtime.base_price)} VND`}
                                            >
                                                {/* Optional: Show seat number only on hover or selection */}
                                                <span className="text-[10px]">{seat.number}</span>
                                            </div>
                                        );
                                    })}
                                </div>
                            ))}
                        </div>
                    )}

                    <div className="seat-legend">
                        <div className="legend-item"><div className="legend-box seat standard"></div> Available</div>
                        <div className="legend-item"><div className="legend-box seat selected"></div> Selected</div>
                        <div className="legend-item"><div className="legend-box seat occupied"></div> Booked</div>
                        <div className="legend-item"><div className="legend-box seat vip"></div> VIP</div>
                    </div>

                    {/* Footer Bar */}
                    {selectedSeats.length > 0 && (
                        <div className="booking-footer">
                            <div className="container footer-content">
                                <div className="selected-seats-info">
                                    <span className="selected-seats-text">
                                        {selectedSeats.map(s => `${s.row}${s.number}`).join(', ')}
                                    </span>
                                    <span className="text-sm text-gray-400">
                                        {selectedSeats.length} seats selected
                                    </span>
                                </div>

                                <div className="flex items-center gap-8">
                                    <div className="total-price">
                                        {calculateTotal().toLocaleString()} VND
                                    </div>
                                    <button className="btn-continue" onClick={handleContinue}>
                                        Continue
                                    </button>
                                </div>
                            </div>
                        </div>
                    )}
                </div>
            )}

            <Footer />
        </div>
    );
};

export default Booking;

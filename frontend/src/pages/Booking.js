import React, { useState, useEffect, useMemo } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { ChevronLeft, Calendar as CalendarIcon, Clock, MapPin, Loader2, Info, Ticket, Film } from 'lucide-react';
import Navbar from '../components/Navbar';
import Footer from '../components/Footer';
import { useAuth } from '../context/AuthContext';
import movieService from '../services/movieService';
import showtimeService from '../services/showtimeService';
import bookingService from '../services/bookingService';
import './Booking.css';

const Booking = () => {
    const { movieId } = useParams(); // Could be ID or Slug
    const navigate = useNavigate();
    const { isAuthenticated } = useAuth();

    // Data State
    const [movie, setMovie] = useState(null);
    const [allShowtimes, setAllShowtimes] = useState([]);
    const [seats, setSeats] = useState([]);
    const [seatMap, setSeatMap] = useState({});

    // Selection State
    const [selectedDate, setSelectedDate] = useState(new Date().toISOString().split('T')[0]);
    const [selectedCity, setSelectedCity] = useState('Hồ Chí Minh');
    const [selectedFormat, setSelectedFormat] = useState('All');
    const [selectedShowtime, setSelectedShowtime] = useState(null);
    const [selectedSeats, setSelectedSeats] = useState([]);

    // Loading State
    const [loading, setLoading] = useState(true);
    const [loadingSeats, setLoadingSeats] = useState(false);
    const [bookingLoading, setBookingLoading] = useState(false);
    const [error, setError] = useState(null);

    const cities = ["Hồ Chí Minh", "Hà Nội", "Đà Nẵng", "Cần Thơ", "Đồng Nai", "Hải Phòng", "Quảng Ninh"];
    const formats = ["All", "2D Phụ Đề", "3D Phụ Đề", "IMAX"];

    // Generate dates
    const dates = useMemo(() => {
        return Array.from({ length: 14 }, (_, i) => {
            const date = new Date();
            date.setDate(date.getDate() + i);
            const iso = date.toISOString().split('T')[0];
            return {
                id: iso,
                dayName: date.toLocaleDateString('vi-VN', { weekday: 'short' }),
                dayNum: date.getDate(),
                month: date.getMonth() + 1,
            };
        });
    }, []);

    // 1. Fetch Movie & Initial Showtimes
    useEffect(() => {
        const fetchInitialData = async () => {
            try {
                setLoading(true);
                const movieRes = await movieService.getMovieById(movieId);
                if (movieRes.success) {
                    setMovie(movieRes.data);
                    // Initial showtimes fetch
                    const stRes = await showtimeService.getShowtimes({
                        movie_id: movieRes.data.movie_id,
                        date: selectedDate
                    });
                    setAllShowtimes(stRes.data || []);
                } else {
                    setError("Không tìm thấy phim.");
                }
            } catch (err) {
                console.error(err);
                setError("Lỗi khi tải dữ liệu.");
            } finally {
                setLoading(false);
            }
        };
        fetchInitialData();
    }, [movieId]);

    // 2. Refetch Showtimes when Date changes
    useEffect(() => {
        const refetchShowtimes = async () => {
            if (!movie) return;
            try {
                const res = await showtimeService.getShowtimes({
                    movie_id: movie.movie_id,
                    date: selectedDate
                });
                setAllShowtimes(res.data || []);
                setSelectedShowtime(null);
                setSelectedSeats([]);
            } catch (err) {
                console.error(err);
            }
        };
        refetchShowtimes();
    }, [selectedDate, movie]);

    // 3. Group and Filter Showtimes
    const groupedShowtimes = useMemo(() => {
        const filtered = allShowtimes.filter(st => {
            const city = st.room?.theater?.city?.name || '';
            const isCityMatch = city.includes(selectedCity);
            const isFormatMatch = selectedFormat === 'All' || (st.format && st.format.includes(selectedFormat));
            return isCityMatch && isFormatMatch;
        });

        return filtered.reduce((acc, st) => {
            const tName = st.room?.theater?.name || 'Rạp không tên';
            const fmt = st.format || 'Standard 2D';
            if (!acc[tName]) acc[tName] = {};
            if (!acc[tName][fmt]) acc[tName][fmt] = [];
            acc[tName][fmt].push(st);
            return acc;
        }, {});
    }, [allShowtimes, selectedCity, selectedFormat]);

    // 4. Handle Selection
    const handleShowtimeClick = async (st) => {
        try {
            setLoadingSeats(true);
            setSelectedShowtime(st);
            setSelectedSeats([]);
            const res = await showtimeService.getShowtimeSeats(st.showtime_id);
            if (res.success) {
                setSeats(res.data.seats || []);
                setSeatMap(res.data.seat_map || {});
            }
        } catch (err) {
            alert("Không thể tải sơ đồ ghế.");
        } finally {
            setLoadingSeats(false);
        }
    };

    const handleSeatClick = (seat) => {
        if (seat.status === 'booked' || seat.status === 'locked') return;

        // Couple Seat Logic
        if (seat.type === 'couple') {
            const num = parseInt(seat.number);
            const partnerNum = num % 2 !== 0 ? num + 1 : num - 1;
            const partner = seats.find(s => s.row === seat.row && parseInt(s.number) === partnerNum);

            if (!partner) return;
            if (partner.status === 'booked' || partner.status === 'locked') {
                alert("Ghế đôi này có một ghế đã bị đặt.");
                return;
            }

            const isSel = selectedSeats.some(s => s.seat_id === seat.seat_id);
            if (isSel) {
                setSelectedSeats(prev => prev.filter(s => s.seat_id !== seat.seat_id && s.seat_id !== partner.seat_id));
            } else {
                if (selectedSeats.length + 2 > 8) {
                    alert("Chỉ được chọn tối đa 8 ghế.");
                    return;
                }
                setSelectedSeats(prev => [...prev, seat, partner]);
            }
            return;
        }

        // Standard/VIP Logic
        const isSel = selectedSeats.some(s => s.seat_id === seat.seat_id);
        if (isSel) {
            setSelectedSeats(prev => prev.filter(s => s.seat_id !== seat.seat_id));
        } else {
            if (selectedSeats.length >= 8) {
                alert("Chỉ được chọn tối đa 8 ghế.");
                return;
            }
            setSelectedSeats(prev => [...prev, seat]);
        }
    };

    const totalAmount = useMemo(() => {
        if (!selectedShowtime) return 0;
        const base = parseFloat(selectedShowtime.base_price);
        return selectedSeats.reduce((sum, s) => sum + base + parseFloat(s.extra_price || 0), 0);
    }, [selectedSeats, selectedShowtime]);

    const handleBooking = async () => {
        if (!isAuthenticated) {
            alert("Vui lòng đăng nhập để đặt vé.");
            navigate('/login', { state: { from: window.location.pathname } });
            return;
        }
        if (selectedSeats.length === 0) {
            alert("Vui lòng chọn ít nhất một ghế.");
            return;
        }

        try {
            setBookingLoading(true);
            const res = await bookingService.createBooking({
                showtime_id: selectedShowtime.showtime_id,
                seat_ids: selectedSeats.map(s => s.seat_id)
            });
            if (res.success) {
                navigate(`/payment/${res.data.booking_id}`);
            } else {
                alert(res.message || "Đặt vé thất bại.");
            }
        } catch (err) {
            alert("Lỗi hệ thống khi đặt vé.");
        } finally {
            setBookingLoading(false);
        }
    };

    if (loading) return (
        <div className="booking-page flex flex-col items-center justify-center">
            <Navbar />
            <Loader2 className="animate-spin text-red-600" size={48} />
            <p className="mt-4 text-gray-500">Đang chuẩn bị phòng vé...</p>
        </div>
    );

    if (error) return (
        <div className="booking-page flex flex-col items-center justify-center p-20">
            <Navbar />
            <div className="text-center">
                <h2 className="text-2xl font-bold mb-4">{error}</h2>
                <button onClick={() => navigate('/')} className="px-6 py-2 bg-red-600 rounded-lg">Quay lại</button>
            </div>
        </div>
    );

    return (
        <div className="booking-page">
            <Navbar />

            <div className="booking-content-dark">
                <div className="booking-container">

                    {!selectedShowtime ? (
                        /* Step 1: Selection */
                        <div className="fade-in">
                            <div className="booking-movie-header">
                                <h1 className="movie-title">{movie.title}</h1>
                                <div className="booking-movie-meta">
                                    <div className="meta-item"><Clock size={16} /> <span>{movie.duration} phút</span></div>
                                    <div className="meta-item"><Film size={16} /> <span>{movie.genres?.map(g => g.name).join(', ')}</span></div>
                                </div>
                            </div>

                            {/* Date Selector */}
                            <div className="cgv-date-selector">
                                <div className="date-track">
                                    {dates.map(d => (
                                        <div
                                            key={d.id}
                                            className={`date-card ${selectedDate === d.id ? 'active' : ''}`}
                                            onClick={() => setSelectedDate(d.id)}
                                        >
                                            <span className="day-name">{d.dayName}</span>
                                            <span className="day-num">{d.dayNum}</span>
                                            <span className="month">Th {d.month}</span>
                                        </div>
                                    ))}
                                </div>
                            </div>

                            {/* City & Format Tabs */}
                            <div className="cgv-tabs-container">
                                <div className="tab-selector">
                                    {cities.map(city => (
                                        <button
                                            key={city}
                                            className={`tab-btn ${selectedCity === city ? 'active' : ''}`}
                                            onClick={() => setSelectedCity(city)}
                                        >
                                            {city}
                                        </button>
                                    ))}
                                </div>
                                <div className="tab-selector">
                                    {formats.map(fmt => (
                                        <button
                                            key={fmt}
                                            className={`tab-btn ${selectedFormat === fmt ? 'active' : ''}`}
                                            onClick={() => setSelectedFormat(fmt)}
                                        >
                                            {fmt}
                                        </button>
                                    ))}
                                </div>
                            </div>

                            {/* Theater Showtimes List */}
                            <div className="showtimes-listing">
                                {Object.keys(groupedShowtimes).length > 0 ? (
                                    Object.entries(groupedShowtimes).map(([theater, formatsObj]) => (
                                        <div key={theater} className="theater-card-group">
                                            <div className="theater-info-row">
                                                <h3 className="theater-title"><MapPin size={24} /> {theater}</h3>
                                            </div>

                                            {Object.entries(formatsObj).map(([fmt, stList]) => (
                                                <div key={fmt} className="format-block">
                                                    <div className="format-name">{fmt}</div>
                                                    <div className="time-slots">
                                                        {stList.map(st => (
                                                            <button
                                                                key={st.showtime_id}
                                                                className="time-btn"
                                                                onClick={() => handleShowtimeClick(st)}
                                                            >
                                                                {st.start_time?.split(' ')[1]?.substring(0, 5) || st.start_time}
                                                            </button>
                                                        ))}
                                                    </div>
                                                </div>
                                            ))}
                                        </div>
                                    ))
                                ) : (
                                    <div className="booking-empty-state">
                                        <Info size={48} className="mx-auto text-gray-700" />
                                        <h3>Rất tiếc, không có suất chiếu nào</h3>
                                        <p className="text-gray-500 mt-2">Vui lòng chọn ngày hoặc thành phố khác.</p>
                                    </div>
                                )}
                            </div>
                        </div>
                    ) : (
                        /* Step 2: Seat Selection */
                        <div className="seat-selection-view fade-in">
                            <div className="back-btn-container text-left">
                                <button className="btn-icon-back" onClick={() => setSelectedShowtime(null)}>
                                    <ChevronLeft size={20} />
                                    Đổi suất chiếu
                                </button>
                            </div>

                            <div className="screen-box">
                                <span className="screen-label">MÀN HÌNH</span>
                                <div className="screen-glow"></div>
                            </div>

                            {loadingSeats ? (
                                <div className="flex justify-center py-20">
                                    <Loader2 className="animate-spin text-red-600" size={40} />
                                </div>
                            ) : (
                                <div className="flex flex-col items-center w-full">
                                    <div className="seats-layout">
                                        {Object.entries(seatMap).map(([row, rowSeats]) => (
                                            <div key={row} className="row-container">
                                                <span className="row-label">{row}</span>
                                                <div className="seat-list">
                                                    {rowSeats.map(seat => {
                                                        const isSel = selectedSeats.some(s => s.seat_id === seat.seat_id);
                                                        const cls = `seat ${seat.type || 'standard'} ${seat.status} ${isSel ? 'selected' : ''}`;
                                                        return (
                                                            <div
                                                                key={seat.seat_id}
                                                                className={cls}
                                                                onClick={() => handleSeatClick(seat)}
                                                            >
                                                                {seat.number}
                                                            </div>
                                                        );
                                                    })}
                                                </div>
                                            </div>
                                        ))}
                                    </div>

                                    <div className="legend-container">
                                        <div className="legend-item"><div className="legend-box standard"></div> <span>Thường</span></div>
                                        <div className="legend-item"><div className="legend-box vip"></div> <span>VIP</span></div>
                                        <div className="legend-item"><div className="legend-box couple"></div> <span>Ghế đôi</span></div>
                                        <div className="legend-item"><div className="legend-box booked"></div> <span>Đã đặt</span></div>
                                        <div className="legend-item"><div className="legend-box selected"></div> <span>Đang chọn</span></div>
                                    </div>
                                </div>
                            )}
                        </div>
                    )}
                </div>
            </div>

            {/* Sticky Footer */}
            {selectedShowtime && (
                <div className="booking-footer-fixed fade-in-up">
                    <div className="footer-inner">
                        <div className="footer-info">
                            <span className="footer-label">Ghế đã chọn ({selectedSeats.length})</span>
                            <div className="footer-value">
                                {selectedSeats.length > 0
                                    ? selectedSeats.map(s => `${s.row}${s.number}`).join(', ')
                                    : 'Chưa chọn ghế'}
                            </div>
                        </div>

                        <div className="footer-info text-center">
                            <span className="footer-label uppercase">Tổng cộng</span>
                            <div className="footer-price">{totalAmount.toLocaleString('vi-VN')} đ</div>
                        </div>

                        <button
                            className="btn-checkout"
                            disabled={selectedSeats.length === 0 || bookingLoading}
                            onClick={handleBooking}
                        >
                            {bookingLoading ? <Loader2 className="animate-spin" size={24} /> : 'TIẾP TỤC'}
                        </button>
                    </div>
                </div>
            )}

            <Footer />
        </div>
    );
};

export default Booking;

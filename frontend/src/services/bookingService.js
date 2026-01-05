import { MOCK_SHOWTIMES, MOCK_MOVIES, MOCK_BOOKINGS } from './mockData';

// Reference to the centralized data
let mockBookings = MOCK_BOOKINGS;

const bookingService = {
    // Tạo mới booking (khóa ghế)
    createBooking: async (bookingData) => {
        const bookingId = `BK-${Date.now()}`;
        const bookingCode = `CODE-${Math.floor(1000 + Math.random() * 9000)}`;

        // Calculate price
        const showtime = MOCK_SHOWTIMES.find(s => s.showtime_id === parseInt(bookingData.showtime_id));
        if (!showtime) throw new Error("Showtime not found");

        const movie = MOCK_MOVIES.find(m => m.movie_id === showtime.movie_id);

        // Mock seat objects (in real app, we'd fetch specific seat prices)
        const seats = bookingData.seat_ids.map(id => {
            const row = id.charAt(0);
            const number = parseInt(id.substring(1));
            // Simple logic for price
            const extraPrice = row === 'G' ? 20000 : 0;
            return {
                seat_id: id,
                row: row,
                number: number,
                extra_price: extraPrice,
                price: showtime.base_price + extraPrice,
                label: id
            };
        });

        const seatsTotal = seats.reduce((acc, seat) => acc + seat.price, 0);

        const newBooking = {
            booking_id: bookingId,
            user_id: 1, // Mock user ID
            showtime_id: bookingData.showtime_id,
            seat_ids: bookingData.seat_ids,
            seats: seats,
            combos: bookingData.combos || [],
            seats_total: seatsTotal,
            combo_total: 0,
            total_price: seatsTotal,
            status: 'pending', // Pending payment
            booking_code: bookingCode,
            created_at: new Date().toISOString(),
            expires_at: new Date(Date.now() + 10 * 60 * 1000).toISOString() // 10 mins expiry
        };

        mockBookings.push(newBooking);

        return {
            success: true,
            data: newBooking
        };
    },

    // Lấy chi tiết booking (cho trang thanh toán)
    getBookingById: async (id) => {
        const booking = mockBookings.find(b => b.booking_id === id);

        if (!booking) {
            return { success: false, message: "Booking not found" };
        }

        // Hydrate with showtime/movie info
        const showtime = MOCK_SHOWTIMES.find(s => s.showtime_id === booking.showtime_id);
        const movie = MOCK_MOVIES.find(m => m.movie_id === showtime.movie_id);

        return {
            success: true,
            data: {
                ...booking,
                showtime: {
                    ...showtime,
                    movie: movie
                }
            }
        };
    },

    // Xử lý thanh toán
    processPayment: async (bookingId, paymentData) => {
        const index = mockBookings.findIndex(b => b.booking_id === bookingId);
        if (index !== -1) {
            mockBookings[index].status = 'confirmed';
            mockBookings[index].payment_method = paymentData.payment_method;
            mockBookings[index].paid_at = new Date().toISOString();
            return { success: true, message: "Payment successful" };
        }
        return { success: false, message: "Booking not found" };
    },

    // Lấy thông tin E-Ticket
    getETicket: async (bookingId) => {
        const booking = mockBookings.find(b => b.booking_id === bookingId);
        if (!booking) return { success: false, message: "Ticket not found" };

        const showtime = MOCK_SHOWTIMES.find(s => s.showtime_id === booking.showtime_id);
        const movie = MOCK_MOVIES.find(m => m.movie_id === showtime.movie_id);

        // Format for E-Ticket View
        const eTicketData = {
            booking_code: booking.booking_code,
            movie: {
                title: movie.title,
                poster: movie.poster_url
            },
            theater: {
                name: showtime.room.theater.name,
                address: showtime.room.theater.city,
                room: showtime.room.name
            },
            showtime: {
                date: new Date(showtime.start_time).toLocaleDateString(),
                time: new Date(showtime.start_time).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
                end_time: new Date(new Date(showtime.start_time).getTime() + movie.duration * 60000).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
            },
            seats: booking.seats.map(s => ({ label: `${s.row}${s.number}` })),
            combos: booking.combos || [],
            payment: {
                total_price: booking.total_price,
                payment_method: booking.payment_method || 'credit_card'
            },
            qr_code: `https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${booking.booking_code}`
        };

        return {
            success: true,
            data: eTicketData
        };
    },

    // Lấy lịch sử đặt vé của User
    getUserBookings: async (params) => {
        // Filter bookings by user (mock user 1)
        const myBookings = mockBookings.filter(b => b.user_id === 1);

        // Hydrate data
        const enrichedBookings = myBookings.map(booking => {
            const showtime = MOCK_SHOWTIMES.find(s => s.showtime_id === booking.showtime_id);
            const movie = MOCK_MOVIES.find(m => m.movie_id === showtime?.movie_id) || MOCK_MOVIES[0];

            return {
                ...booking,
                showtime: {
                    ...showtime,
                    movie: movie
                }
            };
        });

        // Sort by date desc
        enrichedBookings.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));

        return { success: true, data: enrichedBookings };
    }
};

export default bookingService;

import api from './api';

const bookingService = {
    // Tạo mới booking (khóa ghế)
    createBooking: async (bookingData) => {
        try {
            // Payload: { showtime_id, seat_ids: [], ... }
            const response = await api.post('/bookings', bookingData);
            return response.data;
        } catch (error) {
            console.error('Create booking error:', error);
            return {
                success: false,
                message: error.response?.data?.message || 'Đặt vé thất bại',
                errors: error.response?.data?.errors || {}
            };
        }
    },

    // Lấy chi tiết booking (cho trang thanh toán)
    getBookingById: async (id) => {
        try {
            const response = await api.get(`/bookings/${id}`);
            return response.data;
        } catch (error) {
            console.error('Get booking error:', error);
            return {
                success: false,
                message: error.response?.data?.message || 'Không thể lấy thông tin đơn hàng',
                data: null
            };
        }
    },

    // Xử lý thanh toán
    processPayment: async (bookingId, paymentData) => {
        try {
            const response = await api.post(`/bookings/${bookingId}/pay`, paymentData);
            return response.data;
        } catch (error) {
            console.error('Payment error:', error);
            return {
                success: false,
                message: error.response?.data?.message || 'Thanh toán thất bại',
                errors: error.response?.data?.errors || {}
            };
        }
    },

    // Lấy thông tin E-Ticket
    getETicket: async (bookingId) => {
        try {
            const response = await api.get(`/bookings/e-ticket/${bookingId}`);
            return response.data;
        } catch (error) {
            console.error('Get E-Ticket error:', error);
            return {
                success: false,
                message: error.response?.data?.message || 'Không thể lấy vé điện tử',
                data: null
            };
        }
    },

    // Lấy lịch sử đặt vé của User
    getUserBookings: async () => {
        try {
            const response = await api.get('/user/bookings');
            return response.data;
        } catch (error) {
            console.error('Get user bookings error:', error);
            return {
                success: false,
                message: error.response?.data?.message || 'Không thể lấy lịch sử đặt vé',
                data: []
            };
        }
    }
};

export default bookingService;

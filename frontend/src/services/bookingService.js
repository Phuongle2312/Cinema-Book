import api from './api';

// Base URL is already configured in api.js

const bookingService = {
    // Get seats for a showtime
    getSeats: async (showtimeId) => {
        // In real app: GET /api/showtimes/:id/seats
        // Returns list of seats with status
        const response = await api.get(`/showtimes/${showtimeId}/seats`);
        return response.data.data;
    },

    // Hold seats
    holdSeats: async (showtimeId, seatIds) => {
        // POST /api/bookings/hold
        const response = await api.post(`/bookings/hold`, {
            showtime_id: showtimeId,
            seat_ids: seatIds
        });
        return response.data;
    },

    // Create Booking
    createBooking: async (bookingData) => {
        // POST /api/bookings
        const response = await api.post(`/bookings`, bookingData);
        return response.data;
    },

    // Get Booking Details (for Payment)
    getBookingById: async (bookingId) => {
        const response = await api.get(`/bookings/${bookingId}`);
        return response.data;
    },

    // Verify Payment (Admin mostly)
    verifyPayment: async (paymentData) => {
        try {
            const response = await api.post('/verify-payments', paymentData);
            return response.data;
        } catch (error) {
            console.error('Verify payment error:', error);
            throw error;
        }
    },

    // Apply Offer (Voucher)
    applyOffer: async (bookingId, offerCode) => {
        try {
            const response = await api.post(`/bookings/${bookingId}/apply-offer`, { offer_code: offerCode });
            return response.data;
        } catch (error) {
            console.error('Apply offer error:', error);
            throw error;
        }
    },

    // Remove Offer (Reset to Auto or None)
    removeOffer: async (bookingId) => {
        try {
            const response = await api.post(`/bookings/${bookingId}/remove-offer`);
            return response.data;
        } catch (error) {
            console.error('Remove offer error:', error);
            throw error;
        }
    },

    // Process Payment
    processPayment: async (bookingId, paymentData) => {
        const response = await api.post(`/bookings/${bookingId}/pay`, paymentData);
        return response.data;
    },

    // Get E-Ticket
    getETicket: async (bookingId) => {
        const response = await api.get(`/bookings/e-ticket/${bookingId}`);
        return response.data;
    },

    // Get User Bookings
    getUserBookings: async (params) => {
        const response = await api.get('/user/bookings', { params });
        return response.data;
    }
};

export default bookingService;

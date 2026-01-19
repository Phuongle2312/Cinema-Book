import api from './api';

const adminService = {
    // Get Dashboard Stats
    getDashboardStats: async () => {
        const response = await api.get('/admin/dashboard');
        return response.data;
    },

    // Get All Users
    getUsers: async () => {
        const response = await api.get('/admin/users');
        return response.data;
    },

    // --- Movies ---
    getMovies: async (params) => {
        const response = await api.get('/admin/movies', { params });
        return response.data;
    },
    getGenres: async () => {
        const response = await api.get('/genres');
        return response.data;
    },
    getLanguages: async () => {
        const response = await api.get('/languages');
        return response.data;
    },
    createMovie: async (data) => {
        const response = await api.post('/admin/movies', data);
        return response.data;
    },
    updateMovie: async (id, data) => {
        const response = await api.put(`/admin/movies/${id}`, data);
        return response.data;
    },
    deleteMovie: async (id) => {
        const response = await api.delete(`/admin/movies/${id}`);
        return response.data;
    },

    // --- Theaters / Cinemas ---
    getTheaters: async (params) => {
        const response = await api.get('/admin/theaters', { params }); // Note: Using admin route for theaters
        return response.data;
    },
    getCities: async (params) => {
        const response = await api.get('/cities', { params });
        return response.data;
    },
    getTheaterRooms: async (id) => {
        const response = await api.get(`/admin/theaters/${id}/rooms`);
        return response.data;
    },

    createTheater: async (data) => {
        const response = await api.post('/admin/theaters', data);
        return response.data;
    },
    updateTheater: async (id, data) => {
        const response = await api.put(`/admin/theaters/${id}`, data);
        return response.data;
    },
    deleteTheater: async (id) => {
        const response = await api.delete(`/admin/theaters/${id}`);
        return response.data;
    },

    // --- Showtimes ---
    getShowtimes: async (params) => {
        const response = await api.get('/admin/showtimes', { params });
        return response.data;
    },
    createShowtime: async (data) => {
        const response = await api.post('/admin/showtimes', data);
        return response.data;
    },
    updateShowtime: async (id, data) => {
        const response = await api.put(`/admin/showtimes/${id}`, data);
        return response.data;
    },
    deleteShowtime: async (id) => {
        const response = await api.delete(`/admin/showtimes/${id}`);
        return response.data;
    },

    // --- Offers (System-wide & Vouchers) ---
    getOffers: async (params) => {
        const response = await api.get('/admin/offers', { params });
        return response.data;
    },
    createOffer: async (data) => {
        const response = await api.post('/admin/offers', data);
        return response.data;
    },
    updateOffer: async (id, data) => {
        const response = await api.put(`/admin/offers/${id}`, data);
        return response.data;
    },
    deleteOffer: async (id) => {
        const response = await api.delete(`/admin/offers/${id}`);
        return response.data;
    },

    // --- Reviews ---
    getReviews: async (params) => {
        const response = await api.get('/admin/reviews', { params });
        return response.data;
    },
    updateReviewStatus: async (id, status) => {
        const response = await api.put(`/admin/reviews/${id}`, { status });
        return response.data;
    },
    deleteReview: async (id) => {
        const response = await api.delete(`/admin/reviews/${id}`);
        return response.data;
    },

    // --- Payments ---
    getPaymentRequests: async (params) => {
        const response = await api.get('/verify-payments', { params });
        return response.data;
    },
    verifyPayment: async (id, notes) => {
        const response = await api.post(`/verify-payments/${id}/verify`, { notes });
        return response.data;
    },

    // --- Bookings ---
    getBookings: async (params) => {
        const response = await api.get('/admin/bookings', { params });
        return response.data;
    },
    updateBookingStatus: async (id, status) => {
        const response = await api.put(`/admin/bookings/${id}`, { status });
        return response.data;
    },
    deleteBooking: async (id) => {
        const response = await api.delete(`/admin/bookings/${id}`);
        return response.data;
    },
};

export default adminService;

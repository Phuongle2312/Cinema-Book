import api from './api';

const adminService = {
    // Dashboard Stats
    getDashboardStats: async () => {
        try {
            const response = await api.get('/admin/dashboard/stats');
            return response.data;
        } catch (error) {
            console.error('Failed to fetch dashboard stats', error);
            throw error;
        }
    },

    // --- Movies ---
    getMovies: async (params) => {
        const response = await api.get('/admin/movies', { params });
        return response.data;
    },
    createMovie: async (data) => {
        const response = await api.post('/admin/movies', data);
        return response.data;
    },
    updateMovie: async (id, data) => {
        const response = await api.post(`/admin/movies/${id}?_method=PUT`, data); // Laravel often needs POST with _method=PUT for formData
        return response.data;
    },
    deleteMovie: async (id) => {
        const response = await api.delete(`/admin/movies/${id}`);
        return response.data;
    },

    // --- Theaters ---
    getTheaters: async (params) => {
        const response = await api.get('/admin/theaters', { params });
        return response.data;
    },

    // --- Showtimes ---
    getShowtimes: async (params) => {
        const response = await api.get('/admin/showtimes', { params });
        return response.data;
    },

    // --- Users ---
    getUsers: async (params) => {
        const response = await api.get('/admin/users', { params });
        return response.data;
    },

    // --- Payments ---
    getPayments: async (params) => {
        const response = await api.get('/admin/payments', { params });
        return response.data;
    },
    approvePayment: async (id, data = {}) => {
        const response = await api.post(`/admin/payments/${id}/approve`, data);
        return response.data;
    },
    rejectPayment: async (id, data) => {
        const response = await api.post(`/admin/payments/${id}/reject`, data);
        return response.data;
    },

    // --- Discounts (Offers) ---
    getDiscounts: async (params) => {
        const response = await api.get('/admin/discounts', { params });
        return response.data;
    },
    toggleDiscount: async (id) => {
        const response = await api.post(`/admin/discounts/${id}/toggle`);
        return response.data;
    }
};

export default adminService;

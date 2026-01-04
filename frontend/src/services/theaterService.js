import api from './api';

const theaterService = {
    // Lấy danh sách rạp
    getTheaters: async (params) => {
        try {
            const response = await api.get('/theaters', { params });
            return response.data;
        } catch (error) {
            throw error.response?.data || error.message;
        }
    },

    // Lấy chi tiết rạp
    getTheaterById: async (id) => {
        try {
            const response = await api.get(`/theaters/${id}`);
            return response.data;
        } catch (error) {
            throw error.response?.data || error.message;
        }
    }
};

export default theaterService;

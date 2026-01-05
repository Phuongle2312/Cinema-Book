import api from './api';

const theaterService = {
    // Lấy danh sách rạp
    getTheaters: async (params = {}) => {
        try {
            const response = await api.get('/theaters', { params });
            return response.data;
        } catch (error) {
            console.error('Get theaters error:', error);
            return {
                success: false,
                message: error.response?.data?.message || 'Không thể lấy danh sách rạp',
                data: []
            };
        }
    },

    // Lấy chi tiết rạp
    getTheaterById: async (id) => {
        try {
            const response = await api.get(`/theaters/${id}`);
            return response.data;
        } catch (error) {
            console.error('Get theater details error:', error);
            return {
                success: false,
                message: error.response?.data?.message || 'Không thể lấy thông tin rạp',
                data: null
            };
        }
    }
};

export default theaterService;

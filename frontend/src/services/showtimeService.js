import api from './api';

const showtimeService = {
    // Lấy danh sách lịch chiếu
    getShowtimes: async (params = {}) => {
        try {
            const response = await api.get('/showtimes', { params });
            return response.data;
        } catch (error) {
            console.error('Get showtimes error:', error);
            return {
                success: false,
                message: error.response?.data?.message || 'Không thể lấy lịch chiếu',
                data: []
            };
        }
    },

    // Lấy danh sách ghế cho showtime
    getSeats: async (showtimeId) => {
        try {
            const response = await api.get(`/showtimes/${showtimeId}/seats`);
            return response.data;
        } catch (error) {
            console.error('Get seats error:', error);
            return {
                success: false,
                message: error.response?.data?.message || 'Không thể lấy thông tin ghế',
                data: []
            };
        }
    }
};

export default showtimeService;

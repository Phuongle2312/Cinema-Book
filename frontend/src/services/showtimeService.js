import { MOCK_SHOWTIMES, generateMockSeats } from './mockData';

const showtimeService = {
    // Lấy danh sách suất chiếu
    getShowtimes: async (params) => {
        const movieId = parseInt(params.movie_id);
        const filtered = MOCK_SHOWTIMES.filter(s => s.movie_id === movieId);
        return { success: true, data: filtered };
    },

    // Lấy chi tiết suất chiếu và sơ đồ ghế
    getShowtimeSeats: async (id) => {
        const mockSeats = generateMockSeats();
        return { success: true, data: mockSeats };
    }
};

export default showtimeService;

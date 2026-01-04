import { MOCK_MOVIES } from './mockData';

const movieService = {
    // Phim nổi bật/Thịnh hành
    getFeaturedMovies: async () => {
        return { success: true, data: MOCK_MOVIES.filter(m => m.status === 'now_showing').slice(0, 10) };
    },

    // Danh sách phim
    getMovies: async (params = {}) => {
        return { success: true, data: MOCK_MOVIES };
    },

    // Chi tiết phim
    getMovieById: async (id) => {
        const movie = MOCK_MOVIES.find(m => m.movie_id === parseInt(id));
        return { success: true, data: movie || MOCK_MOVIES[0] };
    },

    // Tìm kiếm
    searchMovies: async (query) => {
        const filtered = MOCK_MOVIES.filter(m =>
            m.title.toLowerCase().includes(query.toLowerCase())
        );
        return { success: true, data: filtered };
    },

    // Bộ lọc
    filterMovies: async (filters) => {
        let filtered = [...MOCK_MOVIES];
        if (filters.genre) {
            filtered = filtered.filter(m => m.genres.some(g => g.name === filters.genre));
        }
        if (filters.status) {
            filtered = filtered.filter(m => m.status === filters.status);
        }
        return { success: true, data: filtered };
    }
};

export default movieService;

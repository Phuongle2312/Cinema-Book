import api from './api';

const movieService = {
    // Phim nổi bật/Thịnh hành
    getFeaturedMovies: async () => {
        try {
            const response = await api.get('/movies/featured');
            return response.data;
        } catch (error) {
            console.error('Get featured movies error:', error);
            return {
                success: false,
                message: error.response?.data?.message || 'Không thể lấy danh sách phim nổi bật',
                data: []
            };
        }
    },

    // Danh sách phim
    getMovies: async (params = {}) => {
        try {
            const response = await api.get('/movies', { params });
            return response.data;
        } catch (error) {
            console.error('Get movies error:', error);
            return {
                success: false,
                message: error.response?.data?.message || 'Không thể lấy danh sách phim',
                data: []
            };
        }
    },

    // Chi tiết phim (hỗ trợ cả slug và id)
    getMovieById: async (idOrSlug) => {
        try {
            const response = await api.get(`/movies/${idOrSlug}`);
            return response.data;
        } catch (error) {
            console.error('Get movie details error:', error);
            return {
                success: false,
                message: error.response?.data?.message || 'Không thể lấy thông tin phim',
                data: null
            };
        }
    },

    // Tìm kiếm
    searchMovies: async (query) => {
        try {
            const response = await api.get('/movies/search', {
                params: { q: query }
            });
            return response.data;
        } catch (error) {
            console.error('Search movies error:', error);
            return {
                success: false,
                message: error.response?.data?.message || 'Tìm kiếm thất bại',
                data: []
            };
        }
    },

    // Bộ lọc
    filterMovies: async (filters) => {
        try {
            const response = await api.get('/movies/filter', {
                params: filters
            });
            return response.data;
        } catch (error) {
            console.error('Filter movies error:', error);
            return {
                success: false,
                message: error.response?.data?.message || 'Lọc phim thất bại',
                data: []
            };
        }
    }
};

export default movieService;

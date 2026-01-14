import api from './api';

const wishlistService = {
    // Lấy danh sách yêu thích
    getWishlist: async () => {
        try {
            const response = await api.get('/wishlist');
            return response.data;
        } catch (error) {
            console.error('Get wishlist error:', error);
            return {
                success: false,
                message: error.response?.data?.message || 'Không thể lấy danh sách yêu thích',
                data: []
            };
        }
    },

    // Thêm/Xóa khỏi danh sách yêu thích
    toggleWishlist: async (movieId) => {
        try {
            const response = await api.post('/wishlist/toggle', { movie_id: movieId });
            return response.data;
        } catch (error) {
            console.error('Toggle wishlist error:', error);
            return {
                success: false,
                message: error.response?.data?.message || 'Lỗi xử lý yêu thích',
                is_favorite: false
            };
        }
    },

    // Kiểm tra đã thích chưa
    checkIsFavorite: async (movieId) => {
        try {
            const response = await api.get(`/wishlist/check/${movieId}`);
            return response.data;
        } catch (error) {
            console.error('Check favorite error:', error);
            return {
                success: false,
                is_favorite: false
            };
        }
    }
};

export default wishlistService;

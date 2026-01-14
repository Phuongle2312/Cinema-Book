import api from './api';

const wishlistService = {
    getWishlist: async () => {
        try {
            const response = await api.get('/wishlist');
            return response.data;
        } catch (error) {
            console.error('Get wishlist error:', error);
            return { success: false, data: [] };
        }
    },

    toggleWishlist: async (movieId) => {
        try {
            const response = await api.post('/wishlist', { movie_id: movieId });
            return response.data;
        } catch (error) {
            console.error('Toggle wishlist error:', error);
            return { success: false, message: 'Lỗi khi cập nhật danh sách yêu thích' };
        }
    },

    checkWishlist: async (movieId) => {
        try {
            const response = await api.get(`/wishlist/check/${movieId}`);
            return response.data;
        } catch (error) {
            console.error('Check wishlist error:', error);
            return { success: false, is_favorite: false };
        }
    }
};

export default wishlistService;

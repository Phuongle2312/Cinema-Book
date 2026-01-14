import api from './api';

const offerService = {
    // Lấy tất cả ưu đãi active
    getOffers: async () => {
        const response = await api.get('/offers');
        return response.data;
    },

    // Lấy ưu đãi hệ thống (tự động áp dụng)
    getSystemOffers: async () => {
        const response = await api.get('/offers/system');
        return response.data;
    },

    // Validate mã code (vẫn giữ lại cho các trường hợp cần mã)
    validateOffer: async (code, amount) => {
        const response = await api.post('/offers/validate', { code, amount });
        return response.data;
    }
};

export default offerService;

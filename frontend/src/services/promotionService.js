import api from './api';

const promotionService = {
    getPromotions: async () => {
        const response = await api.get('/promotions');
        return response.data;
    },

    validatePromotion: async (code, amount) => {
        const response = await api.post('/promotions/validate', { code, amount });
        return response.data;
    }
};

export default promotionService;

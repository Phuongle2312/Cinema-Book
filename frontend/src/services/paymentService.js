import api from './api';

const paymentService = {
    /**
     * Submit payment proof for a booking
     */
    submitPayment: async (formData) => {
        try {
            const response = await api.post('/payments/submit', formData, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                },
            });
            return response.data;
        } catch (error) {
            console.error('Submit payment error:', error);
            return {
                success: false,
                message: error.response?.data?.message || 'Failed to submit payment',
            };
        }
    },

    /**
     * Get payment history for current user
     */
    getPaymentHistory: async () => {
        try {
            const response = await api.get('/payments/history');
            return response.data;
        } catch (error) {
            console.error('Get payment history error:', error);
            return {
                success: false,
                message: error.response?.data?.message || 'Failed to load payment history',
                data: [],
            };
        }
    },

    /**
     * Get single payment detail
     */
    getPaymentById: async (id) => {
        try {
            const response = await api.get(`/payments/${id}`);
            return response.data;
        } catch (error) {
            console.error('Get payment detail error:', error);
            return {
                success: false,
                message: error.response?.data?.message || 'Failed to load payment detail',
                data: null,
            };
        }
    },

    /**
     * Check payment status for a booking
     */
    checkPaymentStatus: async (bookingId) => {
        try {
            const response = await api.get(`/payments/check/${bookingId}`);
            return response.data;
        } catch (error) {
            console.error('Check payment status error:', error);
            return {
                success: false,
                message: error.response?.data?.message || 'Failed to check payment status',
                data: null,
            };
        }
    },
};

export default paymentService;

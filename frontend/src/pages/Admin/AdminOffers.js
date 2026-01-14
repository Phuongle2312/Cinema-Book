import React, { useEffect, useState } from 'react';
import { usePopup } from '../../context/PopupContext';
import AdminHeader from '../../components/Admin/AdminHeader';
import { Plus, Tag, Loader2, Power } from 'lucide-react';
import adminService from '../../services/adminService';

const AdminOffers = () => {
    const [discounts, setDiscounts] = useState([]);
    const [loading, setLoading] = useState(true);
    const { showError } = usePopup();

    useEffect(() => {
        fetchDiscounts();
    }, []);

    const fetchDiscounts = async () => {
        try {
            setLoading(true);
            const response = await adminService.getDiscounts();
            if (response.success) {
                setDiscounts(response.data);
            }
        } catch (error) {
            console.error("Failed to load discounts", error);
        } finally {
            setLoading(false);
        }
    };

    const handleToggle = async (id) => {
        try {
            await adminService.toggleDiscount(id);
            fetchDiscounts(); // Refresh
        } catch (error) {
            showError('Failed to update status');
        }
    }

    return (
        <>
            <AdminHeader title="Offers & Events" />
            <div className="admin-content">
                <div className="admin-page-header">
                    <h2 className="admin-page-title">Discounts Management</h2>
                    <button className="btn-primary">
                        <Plus size={18} /> Create New Offer
                    </button>
                </div>

                <div className="admin-card">
                    <div className="admin-table-container">
                        {loading ? (
                            <div className="p-8 flex justify-center">
                                <Loader2 className="animate-spin text-red-500" />
                            </div>
                        ) : (
                            <table className="admin-table">
                                <thead>
                                    <tr>
                                        <th>Discount Value</th>
                                        <th>Description</th>
                                        <th>End Date</th>
                                        <th>Status</th>
                                        <th style={{ textAlign: 'right' }}>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {discounts.map(discount => (
                                        <tr key={discount.id}>
                                            <td style={{ fontWeight: 'bold', color: 'var(--admin-primary)' }}>
                                                {discount.discount_type === 'percentage'
                                                    ? `${discount.discount_value}%`
                                                    : new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(discount.discount_value)}
                                            </td>
                                            <td>
                                                <div style={{ fontWeight: 'bold' }}>{discount.name}</div>
                                                <div style={{ fontSize: '0.8rem', color: '#888' }}>{discount.description}</div>
                                                {discount.movie && (
                                                    <div style={{ fontSize: '0.8rem', color: 'var(--admin-accent)' }}>
                                                        Movie: {discount.movie.title}
                                                    </div>
                                                )}
                                            </td>
                                            <td>{discount.end_date ? new Date(discount.end_date).toLocaleDateString() : 'Forever'}</td>
                                            <td>
                                                <span className={`status-badge ${discount.is_active ? 'status-active' : 'status-inactive'}`}>
                                                    {discount.is_active ? 'Active' : 'Inactive'}
                                                </span>
                                            </td>
                                            <td style={{ textAlign: 'right' }}>
                                                <button
                                                    className="action-btn"
                                                    style={{ color: discount.is_active ? 'var(--admin-success)' : 'var(--admin-text-secondary)' }}
                                                    onClick={() => handleToggle(discount.id)}
                                                    title="Toggle Status"
                                                >
                                                    <Power size={18} />
                                                </button>
                                            </td>
                                        </tr>
                                    ))}
                                    {discounts.length === 0 && (
                                        <tr>
                                            <td colSpan="6" style={{ textAlign: 'center', padding: '32px', color: 'gray' }}>
                                                No offers found.
                                            </td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                        )}
                    </div>
                </div>
            </div>
        </>
    );
};

export default AdminOffers;

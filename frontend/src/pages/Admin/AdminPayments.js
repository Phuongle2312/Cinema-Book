import React, { useEffect, useState } from 'react';
import { usePopup } from '../../context/PopupContext';
import AdminHeader from '../../components/Admin/AdminHeader';
import { Search, Loader2, Check, X, CreditCard } from 'lucide-react';
import adminService from '../../services/adminService';

const AdminPayments = () => {
    const [payments, setPayments] = useState([]);
    const [loading, setLoading] = useState(true);
    const { showError, showWarning } = usePopup();

    useEffect(() => {
        fetchPayments();
    }, []);

    const fetchPayments = async () => {
        try {
            setLoading(true);
            const response = await adminService.getPayments();
            if (response.success) {
                setPayments(response.data); // data might be paginated or array
            }
        } catch (error) {
            console.error("Failed to load payments", error);
            showError("Failed to load payments: " + (error.response?.data?.message || error.message));
        } finally {
            setLoading(false);
        }
    };

    const handleApprove = async (id) => {
        if (!window.confirm('Approve this payment?')) return;
        try {
            await adminService.approvePayment(id);
            fetchPayments();
        } catch (error) {
            console.error(error);
            showError(error.response?.data?.message || 'Action failed');
        }
    }

    const handleReject = async (id) => {
        const reason = window.prompt('Enter reason for rejection:');
        if (reason === null) return; // Cancelled
        if (!reason.trim()) {
            showWarning('Rejection reason is required.');
            return;
        }

        if (!window.confirm('Reject this payment directly?')) return;

        try {
            await adminService.rejectPayment(id, { admin_note: reason });
            fetchPayments();
        } catch (error) {
            console.error(error);
            showError(error.response?.data?.message || 'Action failed');
        }
    }

    return (
        <>
            <AdminHeader title="Payment Verification" />
            <div className="admin-content">
                <div className="admin-page-header">
                    <h2 className="admin-page-title">Pending Verifications</h2>
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
                                        <th>ID</th>
                                        <th>User / Booking</th>
                                        <th>Method</th>
                                        <th>Amount</th>
                                        <th>Transaction Code</th>
                                        <th>Status</th>
                                        <th style={{ textAlign: 'right' }}>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {payments.length > 0 ? payments.map(payment => (
                                        <tr key={payment.verification_id || payment.id}>
                                            <td>#{payment.verification_id || payment.id}</td>
                                            <td>
                                                <div style={{ fontWeight: 'bold' }}>{payment.user ? payment.user.name : 'Guest'}</div>
                                                <div style={{ fontSize: '0.8rem', color: 'var(--admin-text-secondary)' }}>
                                                    Booking: #{payment.booking_id}
                                                </div>
                                            </td>
                                            <td>{payment.payment_method}</td>
                                            <td>
                                                {new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(payment.amount)}
                                            </td>
                                            <td style={{ fontFamily: 'monospace' }}>{payment.transaction_code || '-'}</td>
                                            <td>
                                                <span className={`status-badge ${payment.status === 'approved' ? 'status-active' :
                                                    (payment.status === 'rejected' ? 'status-inactive' : 'status-inactive') // pending as grey
                                                    }`}
                                                    style={payment.status === 'pending' ? { color: '#facc15', background: 'rgba(250, 204, 21, 0.1)' } : {}}
                                                >
                                                    {payment.status.toUpperCase()}
                                                </span>
                                            </td>
                                            <td style={{ textAlign: 'right' }}>
                                                {payment.status === 'pending' && (
                                                    <div style={{ display: 'flex', gap: '8px', justifyContent: 'flex-end' }}>
                                                        <button
                                                            className="action-btn"
                                                            style={{ color: 'var(--admin-success)', background: 'rgba(34, 197, 94, 0.1)' }}
                                                            onClick={() => handleApprove(payment.verification_id || payment.id)}
                                                            title="Approve"
                                                        >
                                                            <Check size={18} />
                                                        </button>
                                                        <button
                                                            className="action-btn"
                                                            style={{ color: 'var(--admin-danger)', background: 'rgba(239, 68, 68, 0.1)' }}
                                                            onClick={() => handleReject(payment.verification_id || payment.id)}
                                                            title="Reject"
                                                        >
                                                            <X size={18} />
                                                        </button>
                                                    </div>
                                                )}
                                            </td>
                                        </tr>
                                    )) : (
                                        <tr>
                                            <td colSpan="7" style={{ textAlign: 'center', padding: '32px', color: 'gray' }}>
                                                No payments found.
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

export default AdminPayments;

import React, { useState, useEffect } from 'react';
import AdminHeader from '../../components/Admin/AdminHeader';
import adminService from '../../services/adminService';
import { useToast } from '../../context/ToastContext';
import { Check, X, CreditCard, Search, Calendar, ChevronLeft, ChevronRight, Loader2 } from 'lucide-react';

const ManagePayments = () => {
    const toast = useToast();
    const [payments, setPayments] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [page, setPage] = useState(1);
    const [totalPages, setTotalPages] = useState(1);
    const [verifyingId, setVerifyingId] = useState(null);

    useEffect(() => {
        fetchPayments();
    }, [page]);

    const fetchPayments = async () => {
        try {
            setLoading(true);
            const response = await adminService.getPaymentRequests({ page });
            if (response.success) {
                setPayments(response.data);
                setTotalPages(response.meta.last_page);
            } else {
                setError('Failed to fetch payments');
            }
        } catch (err) {
            setError('Error connecting to server');
            console.error(err);
        } finally {
            setLoading(false);
        }
    };

    const handleVerify = async (id) => {
        if (!window.confirm('Are you sure you want to verify this payment?')) return;

        try {
            setVerifyingId(id);
            const response = await adminService.verifyPayment(id, 'Verified by Admin');
            if (response.success) {
                // Refresh list logic or local update
                setPayments(prev => prev.map(p =>
                    p.id === id ? { ...p, status: 'verified', verified_at: new Date().toISOString() } : p
                ));
                toast.success('Payment verified successfully!');
            }
        } catch (err) {
            toast.error('Failed to verify payment');
            console.error(err);
        } finally {
            setVerifyingId(null);
        }
    };

    const formatDate = (dateString) => {
        if (!dateString) return 'N/A';
        return new Date(dateString).toLocaleString('vi-VN', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit'
        });
    };

    const getStatusBadge = (status) => {
        switch (status) {
            case 'verified':
                return <span className="status-badge active"><Check size={14} /> Verified</span>;
            case 'pending':
                return <span className="status-badge pending">Pending</span>;
            default:
                return <span className="status-badge inactive">{status}</span>;
        }
    };

    if (loading && payments.length === 0) return <div className="loading">Loading payments...</div>;

    return (
        <>
            <AdminHeader title="Payment Verification" />
            <div className="admin-content">
                <div className="admin-card">
                    <div className="admin-header-actions" style={{ padding: '16px 24px', borderBottom: '1px solid var(--admin-border)', display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                        <h2 style={{ fontSize: '1.2rem', fontWeight: '600', display: 'flex', alignItems: 'center', gap: '8px' }}>
                            <CreditCard size={20} className="text-primary" />
                            Request List
                        </h2>
                        <div className="search-box" style={{ position: 'relative' }}>
                            {/* Search placeholder if needed */}
                        </div>
                    </div>

                    <div className="admin-table-container">
                        <table className="admin-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>User</th>
                                    <th>Movie / Showtime</th>
                                    <th>Transaction Code</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th style={{ textAlign: 'right' }}>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                {payments.length === 0 ? (
                                    <tr>
                                        <td colSpan="7" style={{ textAlign: 'center', padding: '40px', color: 'var(--admin-text-secondary)' }}>
                                            No payment requests found.
                                        </td>
                                    </tr>
                                ) : (
                                    payments.map((payment) => (
                                        <tr key={payment.id}>
                                            <td style={{ color: 'var(--admin-text-secondary)' }}>#{payment.id}</td>
                                            <td>
                                                <div className="user-cell">
                                                    <div className="user-info">
                                                        <strong style={{ display: 'block', color: 'var(--admin-text-main)' }}>{payment.user?.name || 'Create User'}</strong>
                                                        <span style={{ fontSize: '0.85rem', color: 'var(--admin-text-secondary)' }}>{payment.user?.email}</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div style={{ display: 'flex', flexDirection: 'column' }}>
                                                    <span style={{ fontWeight: '500' }}>{payment.booking?.showtime?.movie?.title || 'Unknown Movie'}</span>
                                                    <span style={{ fontSize: '0.85rem', color: 'var(--admin-text-secondary)' }}>
                                                        {formatDate(payment.booking?.showtime?.start_time)}
                                                    </span>
                                                </div>
                                            </td>
                                            <td>
                                                <code className="transaction-code" style={{
                                                    background: 'rgba(255,255,255,0.1)',
                                                    padding: '4px 8px',
                                                    borderRadius: '4px',
                                                    fontFamily: 'monospace',
                                                    color: '#eab308'
                                                }}>
                                                    {payment.transaction_code}
                                                </code>
                                            </td>
                                            <td>
                                                <div style={{ display: 'flex', alignItems: 'center', gap: '6px', color: 'var(--admin-text-secondary)' }}>
                                                    <Calendar size={14} />
                                                    {formatDate(payment.created_at)}
                                                </div>
                                            </td>
                                            <td>{getStatusBadge(payment.status)}</td>
                                            <td style={{ textAlign: 'right' }}>
                                                {payment.status === 'pending' && (
                                                    <button
                                                        className="action-btn edit-btn"
                                                        onClick={() => handleVerify(payment.id)}
                                                        disabled={verifyingId === payment.id}
                                                        title="Verify Payment"
                                                        style={{
                                                            background: 'transparent',
                                                            border: '1px solid #22c55e',
                                                            color: '#22c55e',
                                                            padding: '6px',
                                                            borderRadius: '4px',
                                                            cursor: 'pointer',
                                                            display: 'inline-flex',
                                                            alignItems: 'center',
                                                            justifyContent: 'center'
                                                        }}
                                                    >
                                                        {verifyingId === payment.id ? <Loader2 className="animate-spin" size={18} /> : <Check size={18} />}
                                                    </button>
                                                )}
                                            </td>
                                        </tr>
                                    ))
                                )}
                            </tbody>
                        </table>
                    </div>

                    {totalPages > 1 && (
                        <div className="pagination" style={{ padding: '16px', borderTop: '1px solid var(--admin-border)', display: 'flex', justifyContent: 'center', gap: '8px' }}>
                            <button
                                disabled={page === 1}
                                onClick={() => setPage(p => p - 1)}
                                className="btn-icon"
                            >
                                <ChevronLeft size={20} />
                            </button>
                            <span style={{ display: 'flex', alignItems: 'center', margin: '0 12px' }}>Page {page} of {totalPages}</span>
                            <button
                                disabled={page === totalPages}
                                onClick={() => setPage(p => p + 1)}
                                className="btn-icon"
                            >
                                <ChevronRight size={20} />
                            </button>
                        </div>
                    )}
                </div>
            </div>
        </>
    );
};

export default ManagePayments;


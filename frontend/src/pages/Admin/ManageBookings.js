import React, { useState, useEffect } from 'react';
import AdminHeader from '../../components/Admin/AdminHeader';
import adminService from '../../services/adminService';
import { useToast } from '../../context/ToastContext';
import { Search, Calendar, Filter, Eye, Trash2, X, Loader2, CheckCircle } from 'lucide-react';

const ManageBookings = () => {
    const toast = useToast();
    const [bookings, setBookings] = useState([]);
    const [selectedBooking, setSelectedBooking] = useState(null); // Added state
    const [loading, setLoading] = useState(true);
    const [searchTerm, setSearchTerm] = useState('');
    const [filterStatus, setFilterStatus] = useState('all');
    const [filterDate, setFilterDate] = useState('');
    const [page, setPage] = useState(1);
    const [totalPages, setTotalPages] = useState(1);

    const fetchBookings = async () => {
        setLoading(true);
        try {
            const response = await adminService.getBookings({
                search: searchTerm,
                status: filterStatus,
                date: filterDate,
                page: page
            });
            if (response.success) {
                setBookings(response.data.data);
                setTotalPages(response.data.last_page);
            }
        } catch (error) {
            console.error("Failed to fetch bookings", error);
            toast.error("Failed to load bookings");
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        const timeoutId = setTimeout(() => {
            fetchBookings();
        }, 500);
        return () => clearTimeout(timeoutId);
    }, [searchTerm, filterStatus, filterDate, page]);

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

    const handleDelete = async (id) => {
        if (!window.confirm('Are you sure you want to delete this booking?')) return;
        try {
            await adminService.deleteBooking(id);
            toast.success('Booking deleted successfully');
            fetchBookings();
        } catch (error) {
            toast.error('Failed to delete booking');
        }
    };

    const handleStatusChange = async (id, newStatus) => {
        try {
            await adminService.updateBookingStatus(id, newStatus);
            toast.success('Booking status updated');
            fetchBookings();
        } catch (error) {
            toast.error('Failed to update status');
        }
    };

    const getStatusColor = (status) => {
        switch (status) {
            case 'confirmed': return 'status-active';
            case 'pending': return 'status-warning'; // Custom css needed or reuse pending style
            case 'pending_verification': return 'status-warning';
            case 'cancelled': return 'status-inactive';
            default: return '';
        }
    };

    return (
        <>
            <AdminHeader title="Manage Bookings" />
            <div className="admin-content">
                <div className="admin-page-header">
                    <h2 className="admin-page-title">Booking List</h2>
                </div>

                <div className="admin-card" style={{ padding: '0', overflow: 'hidden' }}>
                    {/* Toolbar */}
                    <div style={{ padding: '16px 24px', borderBottom: '1px solid var(--admin-border)', display: 'flex', gap: '16px', flexWrap: 'wrap', alignItems: 'center' }}>

                        {/* Search */}
                        <div style={{ position: 'relative', width: '250px' }}>
                            <Search size={18} style={{ position: 'absolute', left: '10px', top: '50%', transform: 'translateY(-50%)', color: '#888' }} />
                            <input
                                type="text"
                                placeholder="Search code or user..."
                                value={searchTerm}
                                onChange={e => setSearchTerm(e.target.value)}
                                style={{
                                    paddingLeft: '36px',
                                    height: '40px',
                                    borderRadius: '6px',
                                    border: '1px solid var(--admin-border)',
                                    background: 'var(--admin-bg)',
                                    color: 'white',
                                    width: '100%'
                                }}
                            />
                        </div>

                        {/* Status Filter */}
                        <div style={{ width: '150px' }}>
                            <select
                                value={filterStatus}
                                onChange={e => setFilterStatus(e.target.value)}
                                className="form-control"
                                style={{ height: '40px' }}
                            >
                                <option value="all">All Status</option>
                                <option value="confirmed">Confirmed</option>
                                <option value="pending">Pending</option>
                                <option value="pending_verification">Verifying</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>

                        {/* Date Filter */}
                        <div style={{ width: '150px' }}>
                            <input
                                type="date"
                                value={filterDate}
                                onChange={e => setFilterDate(e.target.value)}
                                className="form-control"
                                style={{ height: '40px' }}
                            />
                        </div>

                        <button
                            className="btn-secondary"
                            onClick={() => { setSearchTerm(''); setFilterStatus('all'); setFilterDate(''); }}
                            title="Clear Filters"
                        >
                            <X size={18} />
                        </button>
                    </div>

                    {/* Table */}
                    <div className="admin-table-container">
                        {loading ? (
                            <div className="flex justify-center p-8"><Loader2 className="animate-spin text-white py-8" /></div>
                        ) : (
                            <table className="admin-table">
                                <thead>
                                    <tr>
                                        <th>Code</th>
                                        <th>User</th>
                                        <th>Movie</th>
                                        <th>Showtime</th>
                                        <th>Seats</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th style={{ textAlign: 'right' }}>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {bookings.length > 0 ? bookings.map(booking => (
                                        <tr key={booking.booking_id}>
                                            <td style={{ fontWeight: 'bold', color: '#eab308' }}>{booking.booking_code}</td>
                                            <td>{booking.user?.name || 'Guest'}</td>
                                            <td>{booking.showtime?.movie?.title}</td>
                                            <td>
                                                <div className="text-sm text-gray-400">
                                                    {formatDate(booking.showtime?.start_time)}
                                                </div>
                                                <div className="text-xs text-gray-500">{booking.showtime?.room?.theater?.name} - {booking.showtime?.room?.name}</div>
                                            </td>
                                            <td>
                                                {/* Requires booking.seats relation if available, otherwise just count */}
                                                {booking.seats_count || '-'}
                                            </td>
                                            <td>{new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(booking.total_price)}</td>
                                            <td>
                                                <select
                                                    value={booking.status}
                                                    onChange={(e) => handleStatusChange(booking.booking_id, e.target.value)}
                                                    className={`status-badge ${getStatusColor(booking.status)}`}
                                                    style={{ border: 'none', cursor: 'pointer', outline: 'none', background: 'transparent' }}
                                                >
                                                    <option value="pending" style={{ color: 'black' }}>Pending</option>
                                                    <option value="pending_verification" style={{ color: 'black' }}>Verifying</option>
                                                    <option value="confirmed" style={{ color: 'black' }}>Confirmed</option>
                                                    <option value="cancelled" style={{ color: 'black' }}>Cancelled</option>
                                                </select>
                                            </td>
                                            <td style={{ textAlign: 'right' }}>
                                                <button
                                                    className="action-btn view"
                                                    style={{ marginRight: '8px', color: '#3b82f6' }}
                                                    onClick={() => setSelectedBooking(booking)}
                                                >
                                                    <Eye size={18} />
                                                </button>
                                                <button
                                                    className="action-btn delete"
                                                    onClick={() => handleDelete(booking.booking_id)}
                                                >
                                                    <Trash2 size={18} />
                                                </button>
                                            </td>
                                        </tr>
                                    )) : (
                                        <tr><td colSpan="8" className="text-center p-4">No bookings found</td></tr>
                                    )}
                                </tbody>
                            </table>
                        )}
                    </div>

                    {/* Pagination */}
                    {totalPages > 1 && (
                        <div className="pagination" style={{ padding: '16px', borderTop: '1px solid var(--admin-border)', display: 'flex', justifyContent: 'center', gap: '8px' }}>
                            <button disabled={page === 1} onClick={() => setPage(page - 1)} className="btn-icon">Prev</button>
                            <span style={{ display: 'flex', alignItems: 'center' }}>Page {page} of {totalPages}</span>
                            <button disabled={page === totalPages} onClick={() => setPage(page + 1)} className="btn-icon">Next</button>
                        </div>
                    )}
                </div>
            </div>
            {/* Detail Modal */}
            {selectedBooking && (
                <div className="fixed inset-0 bg-black/80 flex items-center justify-center z-50 p-4" onClick={() => setSelectedBooking(null)}>
                    <div className="bg-[#1a1a1a] border border-[#333] rounded-xl max-w-2xl w-full p-6 shadow-2xl" onClick={e => e.stopPropagation()}>
                        <div className="flex justify-between items-center mb-6 pb-4 border-b border-[#333]">
                            <h3 className="text-xl font-bold text-white">Booking Details</h3>
                            <button onClick={() => setSelectedBooking(null)} className="text-gray-400 hover:text-white">
                                <X size={24} />
                            </button>
                        </div>

                        <div className="grid grid-cols-2 gap-6 mb-6">
                            <div>
                                <label className="text-xs text-gray-500 uppercase block mb-1">Booking Code</label>
                                <p className="text-lg font-bold text-yellow-500">{selectedBooking.booking_code}</p>
                            </div>
                            <div>
                                <label className="text-xs text-gray-500 uppercase block mb-1">Status</label>
                                <span className={`inline-block px-3 py-1 rounded-full text-xs font-bold uppercase ${selectedBooking.status === 'confirmed' ? 'bg-green-500/20 text-green-500' :
                                    selectedBooking.status === 'pending' ? 'bg-yellow-500/20 text-yellow-500' :
                                        'bg-red-500/20 text-red-500'
                                    }`}>
                                    {selectedBooking.status}
                                </span>
                            </div>
                            <div>
                                <label className="text-xs text-gray-500 uppercase block mb-1">Customer</label>
                                <p className="text-white">{selectedBooking.user?.name}</p>
                                <p className="text-sm text-gray-400">{selectedBooking.user?.email}</p>
                            </div>
                            <div>
                                <label className="text-xs text-gray-500 uppercase block mb-1">Email Notification</label>
                                <div className="flex items-center gap-2 text-green-400 font-medium">
                                    <CheckCircle size={16} />
                                    <span>Sent to User</span>
                                </div>
                            </div>
                        </div>

                        <div className="bg-[#111] rounded-lg p-4 mb-6">
                            <h4 className="font-bold text-white mb-3 flex items-center gap-2">
                                <span className="w-1 h-4 bg-red-600 rounded-full"></span>
                                Movie Information
                            </h4>
                            <div className="space-y-2 text-sm">
                                <p className="flex justify-between"><span className="text-gray-500">Movie:</span> <span className="text-white">{selectedBooking.showtime?.movie?.title}</span></p>
                                <p className="flex justify-between"><span className="text-gray-500">Showtime:</span> <span className="text-white">{formatDate(selectedBooking.showtime?.start_time)}</span></p>
                                <p className="flex justify-between"><span className="text-gray-500">Theater:</span> <span className="text-white">{selectedBooking.showtime?.room?.theater?.name} - {selectedBooking.showtime?.room?.name}</span></p>
                                <p className="flex justify-between"><span className="text-gray-500">Seats:</span> <span className="text-white font-mono">{selectedBooking.seats_count ? `${selectedBooking.seats_count} Seats` : 'N/A'}</span></p>
                            </div>
                        </div>

                        <div className="flex justify-end pt-4 border-t border-[#333]">
                            <button onClick={() => setSelectedBooking(null)} className="px-6 py-2 bg-gray-700 hover:bg-gray-600 rounded-lg text-white font-medium transition-colors">
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </>
    );
};

export default ManageBookings;

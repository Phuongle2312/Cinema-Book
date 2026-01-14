import React, { useEffect, useState } from 'react';
import AdminHeader from '../../components/Admin/AdminHeader';
import { Plus, Calendar, Clock, Loader2, MapPin } from 'lucide-react';
import adminService from '../../services/adminService';

const AdminShowtimes = () => {
    const [showtimes, setShowtimes] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        fetchShowtimes();
    }, []);

    const fetchShowtimes = async () => {
        try {
            setLoading(true);
            const response = await adminService.getShowtimes();
            if (response.success) {
                setShowtimes(response.data.data);
            }
        } catch (error) {
            console.error("Failed to load showtimes", error);
        } finally {
            setLoading(false);
        }
    };

    return (
        <>
            <AdminHeader title="Showtimes Management" />
            <div className="admin-content">
                <div className="admin-page-header">
                    <h2 className="admin-page-title">Run Schedule</h2>
                    <button className="btn-primary">
                        <Plus size={18} /> Schedule Showtime
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
                                        <th>Movie</th>
                                        <th>Theater & Room</th>
                                        <th>Date & Time</th>
                                        <th>Price (Base)</th>
                                        <th style={{ textAlign: 'right' }}>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {showtimes.map(st => (
                                        <tr key={st.showtime_id}>
                                            <td style={{ fontWeight: 500 }}>{st.movie ? st.movie.title : 'Deleted Movie'}</td>
                                            <td>
                                                <div>{st.theater ? st.theater.name : 'Unknown Theater'}</div>
                                                <div style={{ fontSize: '0.8rem', color: 'var(--admin-text-secondary)' }}>
                                                    {st.room ? st.room.name : 'Unknown Room'}
                                                </div>
                                            </td>
                                            <td>
                                                <div style={{ display: 'flex', alignItems: 'center', gap: '6px' }}>
                                                    <Calendar size={14} />
                                                    {new Date(st.show_date).toLocaleDateString()}
                                                </div>
                                                <div style={{ display: 'flex', alignItems: 'center', gap: '6px', color: 'var(--admin-primary)', marginTop: '4px' }}>
                                                    <Clock size={14} />
                                                    {st.show_time.substring(0, 5)}
                                                </div>
                                            </td>
                                            <td>
                                                {new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(st.base_price)}
                                            </td>
                                            <td style={{ textAlign: 'right' }}>
                                                {/* Actions */}
                                            </td>
                                        </tr>
                                    ))}
                                    {showtimes.length === 0 && (
                                        <tr>
                                            <td colSpan="5" style={{ textAlign: 'center', padding: '32px', color: 'gray' }}>No showtimes scheduled</td>
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

export default AdminShowtimes;

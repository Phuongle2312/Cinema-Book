import React from 'react';
import AdminHeader from '../../components/Admin/AdminHeader';
import { Users, Film, Calendar, DollarSign, TrendingUp, Activity } from 'lucide-react';

const AdminDashboard = () => {
    // Mock Data
    const bookings = [
        { id: '#BK001', user: 'John Doe', movie: 'Avengers: Endgame', date: '2024-03-10', amount: '$24.00', status: 'Completed' },
        { id: '#BK002', user: 'Jane Smith', movie: 'Dune: Part Two', date: '2024-03-09', amount: '$32.00', status: 'Pending' },
        { id: '#BK003', user: 'Mike Johnson', movie: 'Kung Fu Panda 4', date: '2024-03-09', amount: '$16.00', status: 'Completed' },
        { id: '#BK004', user: 'Sarah Wilson', movie: 'Dune: Part Two', date: '2024-03-08', amount: '$64.00', status: 'Cancelled' },
        { id: '#BK005', user: 'Tom Brown', movie: 'Civil War', date: '2024-03-08', amount: '$20.00', status: 'Completed' },
    ];

    return (
        <>
            <AdminHeader title="Dashboard" />
            <div className="admin-content">
                <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(280px, 1fr))', gap: '24px', marginBottom: '32px' }}>
                    {/* Stats Card 1 */}
                    <div className="admin-card stats-card" style={{ background: 'linear-gradient(135deg, #e50914, #b20710)', color: 'white' }}>
                        <div style={{ position: 'relative', zIndex: 1 }}>
                            <h3 style={{ fontSize: '0.875rem', opacity: 0.9, marginBottom: '8px' }}>Total Bookings</h3>
                            <p style={{ fontSize: '2rem', fontWeight: 'bold', marginBottom: '4px' }}>1,230</p>
                            <span style={{ fontSize: '0.75rem', background: 'rgba(255,255,255,0.2)', padding: '2px 8px', borderRadius: '12px' }}>
                                +12.5% vs last month
                            </span>
                        </div>
                        <Calendar size={100} className="stats-icon" style={{ color: 'white', opacity: 0.2 }} />
                    </div>

                    {/* Stats Card 2 */}
                    <div className="admin-card stats-card">
                        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'start' }}>
                            <div>
                                <h3 style={{ fontSize: '0.875rem', color: 'var(--admin-text-secondary)', marginBottom: '8px' }}>Total Revenue</h3>
                                <p style={{ fontSize: '2rem', fontWeight: 'bold', color: 'var(--admin-text-main)', marginBottom: '4px' }}>$45,200</p>
                                <span style={{ fontSize: '0.75rem', color: 'var(--admin-success)', display: 'flex', alignItems: 'center', gap: '4px' }}>
                                    <TrendingUp size={14} /> +8.2%
                                </span>
                            </div>
                            <div style={{ background: 'rgba(34, 197, 94, 0.1)', padding: '12px', borderRadius: '50%', color: 'var(--admin-success)' }}>
                                <DollarSign size={24} />
                            </div>
                        </div>
                    </div>

                    {/* Stats Card 3 */}
                    <div className="admin-card stats-card">
                        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'start' }}>
                            <div>
                                <h3 style={{ fontSize: '0.875rem', color: 'var(--admin-text-secondary)', marginBottom: '8px' }}>Registered Users</h3>
                                <p style={{ fontSize: '2rem', fontWeight: 'bold', color: 'var(--admin-text-main)', marginBottom: '4px' }}>150</p>
                                <span style={{ fontSize: '0.75rem', color: 'var(--admin-success)', display: 'flex', alignItems: 'center', gap: '4px' }}>
                                    <TrendingUp size={14} /> +24 new
                                </span>
                            </div>
                            <div style={{ background: 'rgba(245, 158, 11, 0.1)', padding: '12px', borderRadius: '50%', color: 'var(--admin-warning)' }}>
                                <Users size={24} />
                            </div>
                        </div>
                    </div>

                    {/* Stats Card 4 */}
                    <div className="admin-card stats-card">
                        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'start' }}>
                            <div>
                                <h3 style={{ fontSize: '0.875rem', color: 'var(--admin-text-secondary)', marginBottom: '8px' }}>Active Movies</h3>
                                <p style={{ fontSize: '2rem', fontWeight: 'bold', color: 'var(--admin-text-main)', marginBottom: '4px' }}>24</p>
                                <span style={{ fontSize: '0.75rem', color: 'var(--admin-text-secondary)' }}>
                                    Across 12 genres
                                </span>
                            </div>
                            <div style={{ background: 'rgba(229, 9, 20, 0.1)', padding: '12px', borderRadius: '50%', color: 'var(--admin-primary)' }}>
                                <Film size={24} />
                            </div>
                        </div>
                    </div>
                </div>

                <div style={{ display: 'grid', gridTemplateColumns: '2fr 1fr', gap: '24px' }}>
                    <div className="admin-card" style={{ padding: '0', overflow: 'hidden' }}>
                        <div style={{ padding: '24px', borderBottom: '1px solid var(--admin-border)', display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                            <h3 style={{ margin: 0, fontSize: '1.125rem', color: 'var(--admin-text-main)' }}>Recent Bookings</h3>
                            <button className="btn-primary" style={{ padding: '6px 12px', fontSize: '0.875rem' }}>View All</button>
                        </div>
                        <div className="admin-table-container" style={{ border: 'none', borderRadius: '0' }}>
                            <table className="admin-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>User</th>
                                        <th>Movie</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th style={{ textAlign: 'right' }}>Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {bookings.map((booking) => (
                                        <tr key={booking.id}>
                                            <td style={{ fontWeight: '500' }}>{booking.id}</td>
                                            <td>
                                                <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
                                                    <div style={{ width: '24px', height: '24px', background: 'rgba(255,255,255,0.1)', borderRadius: '50%', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: '10px', fontWeight: 'bold', color: 'var(--admin-text-main)' }}>
                                                        {booking.user.charAt(0)}
                                                    </div>
                                                    {booking.user}
                                                </div>
                                            </td>
                                            <td>{booking.movie}</td>
                                            <td>{booking.date}</td>
                                            <td>
                                                <span className={`status-badge ${booking.status === 'Completed' ? 'status-active' :
                                                    booking.status === 'Cancelled' ? 'status-inactive' : 'status-inactive'
                                                    }`} style={booking.status === 'Cancelled' ? { backgroundColor: 'rgba(239, 68, 68, 0.2)', color: '#fca5a5' } : {}}>
                                                    {booking.status}
                                                </span>
                                            </td>
                                            <td style={{ textAlign: 'right', fontWeight: '500' }}>{booking.amount}</td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div className="admin-card">
                        <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: '24px' }}>
                            <h3 style={{ margin: 0, fontSize: '1.125rem', color: 'var(--admin-text-main)' }}>System Status</h3>
                            <Activity size={20} color="var(--admin-text-secondary)" />
                        </div>

                        <div style={{ display: 'flex', flexDirection: 'column', gap: '20px' }}>
                            {[
                                { label: 'Server Status', status: 'Online', color: '#22c55e' },
                                { label: 'Database', status: 'Connected', color: '#22c55e' },
                                { label: 'Storage Usage', status: '45%', color: '#f59e0b' },
                                { label: 'API Latency', status: '24ms', color: '#22c55e' }
                            ].map((item, index) => (
                                <div key={index} style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                                    <span style={{ color: 'var(--admin-text-secondary)' }}>{item.label}</span>
                                    <span style={{ display: 'flex', alignItems: 'center', gap: '8px', fontWeight: '500', color: 'var(--admin-text-main)' }}>
                                        <span style={{ width: '8px', height: '8px', borderRadius: '50%', backgroundColor: item.color }}></span>
                                        {item.status}
                                    </span>
                                </div>
                            ))}
                        </div>

                        <div style={{ marginTop: '32px', padding: '16px', background: 'rgba(255,255,255,0.03)', borderRadius: '8px' }}>
                            <h4 style={{ margin: '0 0 8px 0', fontSize: '0.875rem', color: 'var(--admin-text-main)' }}>Next Scheduled Backup</h4>
                            <p style={{ margin: 0, fontSize: '0.875rem', color: 'var(--admin-text-secondary)' }}>Today, 03:00 AM</p>
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
};

export default AdminDashboard;

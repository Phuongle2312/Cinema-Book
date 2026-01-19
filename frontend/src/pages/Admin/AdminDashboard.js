import React from 'react';
import AdminHeader from '../../components/Admin/AdminHeader';
import { Users, Film, Calendar, DollarSign, TrendingUp, Activity } from 'lucide-react';
import adminService from '../../services/adminService';
import { LineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer, BarChart, Bar, Legend, Cell, PieChart, Pie } from 'recharts';

const AdminDashboard = () => {
    const [stats, setStats] = React.useState(null);
    const [recentBookings, setRecentBookings] = React.useState([]);
    const [charts, setCharts] = React.useState({ revenue: [], top_movies: [], theater_revenue: [] });
    const [loading, setLoading] = React.useState(true);

    React.useEffect(() => {
        const fetchStats = async () => {
            try {
                const response = await adminService.getDashboardStats();
                if (response.success) {
                    setStats(response.data.stats);
                    setRecentBookings(response.data.recent_bookings);
                    setCharts(response.data.charts || { revenue: [], top_movies: [], theater_revenue: [] });
                }
            } catch (error) {
                console.error('Failed to fetch admin stats:', error);
            } finally {
                setLoading(false);
            }
        };

        fetchStats();
    }, []);

    if (loading) {
        return (
            <>
                <AdminHeader title="Dashboard" />
                <div className="admin-content" style={{ display: 'flex', justifyContent: 'center', paddingTop: '100px' }}>
                    <div style={{ color: 'white' }}>Loading dashboard data...</div>
                </div>
            </>
        );
    }

    const COLORS = ['#0088FE', '#00C49F', '#FFBB28', '#FF8042', '#e50914'];

    return (
        <>
            <AdminHeader title="Dashboard" />
            <div className="admin-content">
                {/* 1. KEY METRICS */}
                <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(280px, 1fr))', gap: '24px', marginBottom: '32px' }}>
                    {/* ... (Existing Stats Cards: Bookings, Revenue, Users, Movies) ... */}
                    {/* Re-using existing code for brevity, but ensuring they are preserved if I use replace correctly. */}
                    {/* Stats Card 1: Bookings */}
                    <div className="admin-card stats-card" style={{ background: 'linear-gradient(135deg, #e50914, #b20710)', color: 'white' }}>
                        <div style={{ position: 'relative', zIndex: 1 }}>
                            <h3 style={{ fontSize: '0.875rem', opacity: 0.9, marginBottom: '8px' }}>Total Bookings</h3>
                            <p style={{ fontSize: '2rem', fontWeight: 'bold', marginBottom: '4px' }}>{stats?.bookings?.value || 0}</p>
                            <span style={{ fontSize: '0.75rem', background: 'rgba(255,255,255,0.2)', padding: '2px 8px', borderRadius: '12px' }}>
                                {stats?.bookings?.change > 0 ? '+' : ''}{stats?.bookings?.change}% vs last month
                            </span>
                        </div>
                        <Calendar size={100} className="stats-icon" style={{ color: 'white', opacity: 0.2 }} />
                    </div>

                    {/* Stats Card 2: Revenue */}
                    <div className="admin-card stats-card">
                        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'start' }}>
                            <div>
                                <h3 style={{ fontSize: '0.875rem', color: 'var(--admin-text-secondary)', marginBottom: '8px' }}>Total Revenue</h3>
                                <p style={{ fontSize: '2rem', fontWeight: 'bold', color: 'var(--admin-text-main)', marginBottom: '4px' }}>{stats?.revenue?.value || 0}</p>
                                <span style={{ fontSize: '0.75rem', color: 'var(--admin-success)', display: 'flex', alignItems: 'center', gap: '4px' }}>
                                    <TrendingUp size={14} /> {stats?.revenue?.change > 0 ? '+' : ''}{stats?.revenue?.change}%
                                </span>
                            </div>
                            <div style={{ background: 'rgba(34, 197, 94, 0.1)', padding: '12px', borderRadius: '50%', color: 'var(--admin-success)' }}>
                                <DollarSign size={24} />
                            </div>
                        </div>
                    </div>

                    {/* Stats Card 3: Users */}
                    <div className="admin-card stats-card">
                        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'start' }}>
                            <div>
                                <h3 style={{ fontSize: '0.875rem', color: 'var(--admin-text-secondary)', marginBottom: '8px' }}>Registered Users</h3>
                                <p style={{ fontSize: '2rem', fontWeight: 'bold', color: 'var(--admin-text-main)', marginBottom: '4px' }}>{stats?.users?.value || 0}</p>
                                <span style={{ fontSize: '0.75rem', color: 'var(--admin-success)', display: 'flex', alignItems: 'center', gap: '4px' }}>
                                    <TrendingUp size={14} /> {stats?.users?.change > 0 ? '+' : ''}{stats?.users?.change}%
                                </span>
                            </div>
                            <div style={{ background: 'rgba(245, 158, 11, 0.1)', padding: '12px', borderRadius: '50%', color: 'var(--admin-warning)' }}>
                                <Users size={24} />
                            </div>
                        </div>
                    </div>

                    {/* Stats Card 4: Movies */}
                    <div className="admin-card stats-card">
                        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'start' }}>
                            <div>
                                <h3 style={{ fontSize: '0.875rem', color: 'var(--admin-text-secondary)', marginBottom: '8px' }}>Active Movies</h3>
                                <p style={{ fontSize: '2rem', fontWeight: 'bold', color: 'var(--admin-text-main)', marginBottom: '4px' }}>{stats?.active_movies?.value || 0}</p>
                                <span style={{ fontSize: '0.75rem', color: 'var(--admin-text-secondary)' }}>
                                    Now Showing
                                </span>
                            </div>
                            <div style={{ background: 'rgba(229, 9, 20, 0.1)', padding: '12px', borderRadius: '50%', color: 'var(--admin-primary)' }}>
                                <Film size={24} />
                            </div>
                        </div>
                    </div>
                </div>

                {/* 2. CHARTS ROW 1: Revenue Over Time */}
                <div style={{ marginBottom: '32px' }}>
                    <div className="admin-card" style={{ height: '400px' }}>
                        <h3 style={{ fontSize: '1.125rem', color: 'var(--admin-text-main)', marginBottom: '24px' }}>Revenue Overview (Last 7 Days)</h3>
                        <ResponsiveContainer width="100%" height="100%">
                            <LineChart data={charts.revenue}>
                                <CartesianGrid strokeDasharray="3 3" stroke="#333" />
                                <XAxis dataKey="date" stroke="#888" />
                                <YAxis stroke="#888" />
                                <Tooltip
                                    contentStyle={{ backgroundColor: '#1f2937', border: 'none', borderRadius: '8px', color: '#fff' }}
                                    formatter={(value) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value)}
                                />
                                <Line type="monotone" dataKey="revenue" stroke="#e50914" strokeWidth={3} dot={{ r: 4 }} activeDot={{ r: 8 }} />
                            </LineChart>
                        </ResponsiveContainer>
                    </div>
                </div>

                {/* 3. CHARTS ROW 2: Top Movies & Theater Revenue */}
                <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(400px, 1fr))', gap: '24px', marginBottom: '32px' }}>
                    {/* Top Movies Bar Chart */}
                    <div className="admin-card" style={{ height: '400px' }}>
                        <h3 style={{ fontSize: '1.125rem', color: 'var(--admin-text-main)', marginBottom: '24px' }}>Top 5 Movies by Revenue</h3>
                        <ResponsiveContainer width="100%" height="90%">
                            <BarChart data={charts.top_movies} layout="vertical" margin={{ top: 5, right: 30, left: 40, bottom: 5 }}>
                                <CartesianGrid strokeDasharray="3 3" stroke="#333" horizontal={false} />
                                <XAxis type="number" stroke="#888" hide />
                                <YAxis dataKey="name" type="category" width={100} stroke="#888" tick={{ fontSize: 12 }} />
                                <Tooltip
                                    cursor={{ fill: 'rgba(255,255,255,0.05)' }}
                                    contentStyle={{ backgroundColor: '#1f2937', border: 'none', borderRadius: '8px', color: '#fff' }}
                                    formatter={(value) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value)}
                                />
                                <Bar dataKey="value" fill="#e50914" radius={[0, 4, 4, 0]} barSize={20} />
                            </BarChart>
                        </ResponsiveContainer>
                    </div>

                    {/* Theater Revenue Pie Chart */}
                    <div className="admin-card" style={{ height: '400px' }}>
                        <h3 style={{ fontSize: '1.125rem', color: 'var(--admin-text-main)', marginBottom: '24px' }}>Revenue by Theater</h3>
                        <ResponsiveContainer width="100%" height="90%">
                            <PieChart>
                                <Pie
                                    data={charts.theater_revenue}
                                    cx="50%"
                                    cy="50%"
                                    labelLine={false}
                                    label={({ name, percent }) => `${name} (${(percent * 100).toFixed(0)}%)`}
                                    outerRadius={100}
                                    fill="#8884d8"
                                    dataKey="value"
                                >
                                    {charts.theater_revenue.map((entry, index) => (
                                        <Cell key={`cell-${index}`} fill={COLORS[index % COLORS.length]} />
                                    ))}
                                </Pie>
                                <Tooltip
                                    contentStyle={{ backgroundColor: '#1f2937', border: 'none', borderRadius: '8px', color: '#fff' }}
                                    formatter={(value) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value)}
                                />
                            </PieChart>
                        </ResponsiveContainer>
                    </div>
                </div>

                {/* 4. RECENT BOOKINGS TABLE */}
                <div style={{ display: 'grid', gridTemplateColumns: '1fr', gap: '24px' }}>
                    <div className="admin-card" style={{ padding: '0', overflow: 'hidden' }}>
                        {/* ... (Existing Recent Bookings Table Code) ... */}
                        <div style={{ padding: '24px', borderBottom: '1px solid var(--admin-border)', display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                            <h3 style={{ margin: 0, fontSize: '1.125rem', color: 'var(--admin-text-main)' }}>Recent Bookings</h3>
                            <button className="btn-primary" style={{ padding: '6px 12px', fontSize: '0.875rem' }} onClick={() => window.location.href = '/admin/bookings'}>View All</button>
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
                                    {recentBookings.length > 0 ? recentBookings.map((booking) => (
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
                                                <span className={`status-badge ${booking.status === 'Confirmed' ? 'status-active' :
                                                    booking.status === 'Cancelled' ? 'status-inactive' : 'status-inactive'
                                                    }`} style={booking.status === 'Cancelled' ? { backgroundColor: 'rgba(239, 68, 68, 0.2)', color: '#fca5a5' } : {}}>
                                                    {booking.status}
                                                </span>
                                            </td>
                                            <td style={{ textAlign: 'right', fontWeight: '500' }}>{booking.amount}</td>
                                        </tr>
                                    )) : (
                                        <tr>
                                            <td colSpan="6" style={{ textAlign: 'center', padding: '20px', color: '#888' }}>No bookings found</td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
};

export default AdminDashboard;

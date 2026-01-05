import React, { useState } from 'react';
import AdminHeader from '../../components/Admin/AdminHeader';
import { Search, Filter, Check, X, Trash2, MessageSquare, Star } from 'lucide-react';

const ManageReviews = () => {
    const [searchTerm, setSearchTerm] = useState('');

    // Initial Mock Data
    const [reviews, setReviews] = useState([
        { id: 1, user: 'John Doe', movie: 'Chainsaw Man', rating: 5, comment: 'Absolutely fantastic! Best movie of the year.', date: '2024-06-14', status: 'Pending' },
        { id: 2, user: 'Jane Smith', movie: 'Sisu', rating: 4, comment: 'Action packed and thrilling.', date: '2024-06-13', status: 'Approved' },
        { id: 3, user: 'Mike Ross', movie: 'Zootopia 2', rating: 2, comment: 'Disappointing sequel. Story was weak.', date: '2024-06-12', status: 'Rejected' },
        { id: 4, user: 'Alice', movie: 'Chainsaw Man', rating: 5, comment: 'Must watch!', date: '2024-06-11', status: 'Pending' },
    ]);

    const handleApprove = (id) => {
        setReviews(reviews.map(r => r.id === id ? { ...r, status: 'Approved' } : r));
    };

    const handleReject = (id) => {
        setReviews(reviews.map(r => r.id === id ? { ...r, status: 'Rejected' } : r));
    };

    const handleDelete = (id) => {
        if (window.confirm('Are you sure you want to delete this review?')) {
            setReviews(reviews.filter(r => r.id !== id));
        }
    };

    return (
        <>
            <AdminHeader title="Manage Reviews" />
            <div className="admin-content">
                <div className="admin-page-header">
                    <h2 className="admin-page-title">User Reviews</h2>
                </div>

                <div className="admin-card" style={{ padding: '0', overflow: 'hidden' }}>
                    {/* Toolbar */}
                    <div style={{ padding: '16px 24px', borderBottom: '1px solid var(--admin-border)', display: 'flex', justifyContent: 'space-between', alignItems: 'center', flexWrap: 'wrap', gap: '16px' }}>
                        <div style={{ position: 'relative', maxWidth: '300px', width: '100%' }}>
                            <Search size={18} style={{ position: 'absolute', left: '12px', top: '50%', transform: 'translateY(-50%)', color: 'var(--admin-text-secondary)' }} />
                            <input
                                type="text"
                                placeholder="Search reviews..."
                                style={{
                                    padding: '10px 10px 10px 40px',
                                    borderRadius: '8px',
                                    border: '1px solid var(--admin-border)',
                                    width: '100%',
                                    outline: 'none',
                                    fontSize: '0.95rem',
                                    background: 'var(--admin-bg)',
                                    color: 'var(--admin-text-main)'
                                }}
                                value={searchTerm}
                                onChange={(e) => setSearchTerm(e.target.value)}
                            />
                        </div>
                        <div style={{ display: 'flex', gap: '12px' }}>
                            <button style={{ display: 'flex', alignItems: 'center', gap: '8px', padding: '8px 16px', borderRadius: '8px', border: '1px solid var(--admin-border)', background: 'transparent', cursor: 'pointer', color: 'var(--admin-text-main)' }}>
                                <Filter size={18} />
                                Filter
                            </button>
                        </div>
                    </div>

                    <div className="admin-table-container" style={{ border: 'none', borderRadius: '0' }}>
                        <table className="admin-table">
                            <thead>
                                <tr>
                                    <th style={{ width: '60px' }}>ID</th>
                                    <th>User & Movie</th>
                                    <th>Review</th>
                                    <th>Status</th>
                                    <th style={{ textAlign: 'right' }}>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                {reviews.filter(r => r.movie.toLowerCase().includes(searchTerm.toLowerCase()) || r.user.toLowerCase().includes(searchTerm.toLowerCase())).map((item) => (
                                    <tr key={item.id}>
                                        <td style={{ color: 'var(--admin-text-secondary)' }}>#{item.id}</td>
                                        <td>
                                            <div style={{ display: 'flex', flexDirection: 'column', gap: '4px' }}>
                                                <span style={{ fontWeight: '500' }}>{item.user}</span>
                                                <span style={{ fontSize: '0.875rem', color: 'var(--admin-text-secondary)' }}>{item.movie}</span>
                                            </div>
                                        </td>
                                        <td style={{ maxWidth: '400px' }}>
                                            <div style={{ marginBottom: '4px', display: 'flex', gap: '2px' }}>
                                                {[...Array(5)].map((_, i) => (
                                                    <Star key={i} size={12} fill={i < item.rating ? "#eab308" : "none"} color={i < item.rating ? "#eab308" : "#444"} />
                                                ))}
                                            </div>
                                            <p style={{ fontSize: '0.95rem', color: 'var(--admin-text-main)', margin: 0 }}>{item.comment}</p>
                                            <span style={{ fontSize: '0.8rem', color: 'var(--admin-text-secondary)', display: 'block', marginTop: '4px' }}>{item.date}</span>
                                        </td>
                                        <td>
                                            <span className={`status-badge ${item.status === 'Approved' ? 'status-active' :
                                                    item.status === 'Rejected' ? 'status-inactive' : 'status-inactive'
                                                }`} style={item.status === 'Pending' ? { background: 'rgba(245, 158, 11, 0.2)', color: '#fbbf24' } :
                                                    item.status === 'Rejected' ? { background: 'rgba(239, 68, 68, 0.2)', color: '#f87171' } : {}}>
                                                {item.status}
                                            </span>
                                        </td>
                                        <td style={{ textAlign: 'right' }}>
                                            <div style={{ display: 'flex', justifyContent: 'flex-end', gap: '8px' }}>
                                                {item.status === 'Pending' && (
                                                    <>
                                                        <button className="action-btn" title="Approve" onClick={() => handleApprove(item.id)} style={{ color: 'var(--admin-success)' }}>
                                                            <Check size={18} />
                                                        </button>
                                                        <button className="action-btn" title="Reject" onClick={() => handleReject(item.id)} style={{ color: 'var(--admin-danger)' }}>
                                                            <X size={18} />
                                                        </button>
                                                    </>
                                                )}
                                                <button className="action-btn delete" title="Delete" onClick={() => handleDelete(item.id)}>
                                                    <Trash2 size={18} />
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </>
    );
};

export default ManageReviews;

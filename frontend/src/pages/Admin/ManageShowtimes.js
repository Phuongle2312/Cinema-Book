import React, { useState } from 'react';
import AdminHeader from '../../components/Admin/AdminHeader';
import { Plus, Search, Edit, Trash2, Calendar, Clock, X } from 'lucide-react';

const ManageShowtimes = () => {
    const [searchTerm, setSearchTerm] = useState('');
    const [showModal, setShowModal] = useState(false);
    const [editingShowtime, setEditingShowtime] = useState(null);

    // Initial Mock Data
    const [showtimes, setShowtimes] = useState([
        { id: 1, movie: 'Chainsaw Man - The Movie', cinema: 'CGV Vincom Center', hall: 'Hall 3', date: '2024-06-15', time: '19:30', price: '$12.00' },
        { id: 2, movie: 'Sisu: Road to Revenge', cinema: 'CGV Pearl Plaza', hall: 'Hall 1', date: '2024-06-15', time: '20:00', price: '$14.00' },
        { id: 3, movie: 'Zootopia 2', cinema: 'CGV Vivo City', hall: 'Hall 5', date: '2024-06-16', time: '10:00', price: '$10.00' },
    ]);

    const [formData, setFormData] = useState({
        movie: '',
        cinema: '',
        hall: '',
        date: '',
        time: '',
        price: ''
    });

    const handleAdd = () => {
        setEditingShowtime(null);
        setFormData({ movie: '', cinema: '', hall: '', date: '', time: '', price: '' });
        setShowModal(true);
    };

    const handleEdit = (showtime) => {
        setEditingShowtime(showtime);
        setFormData({ ...showtime });
        setShowModal(true);
    };

    const handleDelete = (id) => {
        if (window.confirm('Are you sure you want to delete this showtime?')) {
            setShowtimes(showtimes.filter(s => s.id !== id));
        }
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        if (editingShowtime) {
            setShowtimes(showtimes.map(s => s.id === editingShowtime.id ? { ...formData, id: s.id } : s));
        } else {
            setShowtimes([...showtimes, { ...formData, id: showtimes.length + 1 }]);
        }
        setShowModal(false);
    };

    return (
        <>
            <AdminHeader title="Manage Showtimes" />
            <div className="admin-content">
                <div className="admin-page-header">
                    <h2 className="admin-page-title">Showtimes List</h2>
                    <button className="btn-primary" onClick={handleAdd}>
                        <Plus size={18} />
                        Add New Showtime
                    </button>
                </div>

                <div className="admin-card" style={{ padding: '0', overflow: 'hidden' }}>
                    {/* Toolbar */}
                    <div style={{ padding: '16px 24px', borderBottom: '1px solid var(--admin-border)', display: 'flex', justifyContent: 'space-between', alignItems: 'center', flexWrap: 'wrap', gap: '16px' }}>
                        <div style={{ position: 'relative', maxWidth: '300px', width: '100%' }}>
                            <Search size={18} style={{ position: 'absolute', left: '12px', top: '50%', transform: 'translateY(-50%)', color: 'var(--admin-text-secondary)' }} />
                            <input
                                type="text"
                                placeholder="Search movie..."
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
                    </div>

                    <div className="admin-table-container" style={{ border: 'none', borderRadius: '0' }}>
                        <table className="admin-table">
                            <thead>
                                <tr>
                                    <th style={{ width: '60px' }}>ID</th>
                                    <th>Movie</th>
                                    <th>Cinema & Hall</th>
                                    <th>Date & Time</th>
                                    <th>Price</th>
                                    <th style={{ textAlign: 'right' }}>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                {showtimes.filter(s => s.movie.toLowerCase().includes(searchTerm.toLowerCase())).map((item) => (
                                    <tr key={item.id}>
                                        <td style={{ color: 'var(--admin-text-secondary)' }}>#{item.id}</td>
                                        <td>
                                            <span style={{ fontWeight: '500' }}>{item.movie}</span>
                                        </td>
                                        <td>
                                            <div style={{ display: 'flex', flexDirection: 'column', gap: '4px' }}>
                                                <span style={{ fontWeight: '500' }}>{item.cinema}</span>
                                                <span style={{ fontSize: '0.875rem', color: 'var(--admin-text-secondary)' }}>{item.hall}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div style={{ display: 'flex', flexDirection: 'column', gap: '4px' }}>
                                                <div style={{ display: 'flex', alignItems: 'center', gap: '6px', fontSize: '0.9rem' }}>
                                                    <Calendar size={14} color="var(--admin-text-secondary)" />
                                                    {item.date}
                                                </div>
                                                <div style={{ display: 'flex', alignItems: 'center', gap: '6px', fontSize: '0.9rem', color: 'var(--admin-primary)' }}>
                                                    <Clock size={14} />
                                                    {item.time}
                                                </div>
                                            </div>
                                        </td>
                                        <td style={{ fontWeight: '600' }}>{item.price}</td>
                                        <td style={{ textAlign: 'right' }}>
                                            <div style={{ display: 'flex', justifyContent: 'flex-end', gap: '8px' }}>
                                                <button className="action-btn edit" title="Edit" onClick={() => handleEdit(item)}>
                                                    <Edit size={18} />
                                                </button>
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

                {/* Modal */}
                {showModal && (
                    <div className="admin-modal-overlay" onClick={(e) => {
                        if (e.target === e.currentTarget) setShowModal(false);
                    }}>
                        <div className="admin-modal">
                            <div className="modal-header">
                                <h3 className="modal-title">{editingShowtime ? 'Edit Showtime' : 'Add New Showtime'}</h3>
                                <button className="modal-close" onClick={() => setShowModal(false)}>
                                    <X size={24} />
                                </button>
                            </div>
                            <form onSubmit={handleSubmit}>
                                <div className="modal-body">
                                    <div className="form-group">
                                        <label className="form-label">Movie</label>
                                        <input
                                            type="text"
                                            className="form-control"
                                            value={formData.movie}
                                            onChange={(e) => setFormData({ ...formData, movie: e.target.value })}
                                            required
                                        />
                                    </div>
                                    <div className="form-group">
                                        <label className="form-label">Cinema</label>
                                        <input
                                            type="text"
                                            className="form-control"
                                            value={formData.cinema}
                                            onChange={(e) => setFormData({ ...formData, cinema: e.target.value })}
                                            required
                                        />
                                    </div>
                                    <div className="form-group">
                                        <label className="form-label">Hall</label>
                                        <input
                                            type="text"
                                            className="form-control"
                                            value={formData.hall}
                                            onChange={(e) => setFormData({ ...formData, hall: e.target.value })}
                                            placeholder="e.g. Hall 1"
                                            required
                                        />
                                    </div>
                                    <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '16px' }}>
                                        <div className="form-group">
                                            <label className="form-label">Date</label>
                                            <input
                                                type="text"
                                                className="form-control"
                                                value={formData.date}
                                                onChange={(e) => setFormData({ ...formData, date: e.target.value })}
                                                placeholder="YYYY-MM-DD"
                                                required
                                            />
                                        </div>
                                        <div className="form-group">
                                            <label className="form-label">Time</label>
                                            <input
                                                type="text"
                                                className="form-control"
                                                value={formData.time}
                                                onChange={(e) => setFormData({ ...formData, time: e.target.value })}
                                                placeholder="HH:MM"
                                                required
                                            />
                                        </div>
                                    </div>
                                    <div className="form-group">
                                        <label className="form-label">Price</label>
                                        <input
                                            type="text"
                                            className="form-control"
                                            value={formData.price}
                                            onChange={(e) => setFormData({ ...formData, price: e.target.value })}
                                            placeholder="e.g. $12.00"
                                            required
                                        />
                                    </div>
                                </div>
                                <div className="modal-footer">
                                    <button type="button" className="btn-secondary" onClick={() => setShowModal(false)}>Cancel</button>
                                    <button type="submit" className="btn-primary">Save Changes</button>
                                </div>
                            </form>
                        </div>
                    </div>
                )}
            </div>
        </>
    );
};

export default ManageShowtimes;

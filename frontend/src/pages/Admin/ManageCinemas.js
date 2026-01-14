import React, { useState } from 'react';
import AdminHeader from '../../components/Admin/AdminHeader';
import { Plus, Search, Edit, Trash2, MapPin, Monitor, X } from 'lucide-react';

const ManageCinemas = () => {
    const [searchTerm, setSearchTerm] = useState('');
    const [showModal, setShowModal] = useState(false);
    const [editingCinema, setEditingCinema] = useState(null);

    // Initial Mock Data
    const [cinemas, setCinemas] = useState([
        { id: 1, name: 'CGV Vincom Center', location: 'District 1, HCMC', halls: 8, status: 'Active' },
        { id: 2, name: 'CGV Pearl Plaza', location: 'Binh Thanh, HCMC', halls: 6, status: 'Active' },
        { id: 3, name: 'CGV Aeon Mall', location: 'Tan Phu, HCMC', halls: 10, status: 'Maintenance' },
        { id: 4, name: 'CGV Vivo City', location: 'District 7, HCMC', halls: 7, status: 'Active' },
    ]);

    const [formData, setFormData] = useState({
        name: '',
        location: '',
        halls: '',
        status: 'Active'
    });

    const handleAdd = () => {
        setEditingCinema(null);
        setFormData({ name: '', location: '', halls: '', status: 'Active' });
        setShowModal(true);
    };

    const handleEdit = (cinema) => {
        setEditingCinema(cinema);
        setFormData({ ...cinema });
        setShowModal(true);
    };

    const handleDelete = (id) => {
        if (window.confirm('Are you sure you want to delete this cinema?')) {
            setCinemas(cinemas.filter(c => c.id !== id));
        }
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        if (editingCinema) {
            setCinemas(cinemas.map(c => c.id === editingCinema.id ? { ...formData, id: c.id } : c));
        } else {
            setCinemas([...cinemas, { ...formData, id: cinemas.length + 1 }]);
        }
        setShowModal(false);
    };

    return (
        <>
            <AdminHeader title="Manage Cinemas" />
            <div className="admin-content">
                <div className="admin-page-header">
                    <h2 className="admin-page-title">Cinema List</h2>
                    <button className="btn-primary" onClick={handleAdd}>
                        <Plus size={18} />
                        Add New Cinema
                    </button>
                </div>

                <div className="admin-card" style={{ padding: '0', overflow: 'hidden' }}>
                    {/* Toolbar */}
                    <div style={{ padding: '16px 24px', borderBottom: '1px solid var(--admin-border)', display: 'flex', justifyContent: 'space-between', alignItems: 'center', flexWrap: 'wrap', gap: '16px' }}>
                        <div style={{ position: 'relative', maxWidth: '300px', width: '100%' }}>
                            <Search size={18} style={{ position: 'absolute', left: '12px', top: '50%', transform: 'translateY(-50%)', color: 'var(--admin-text-secondary)' }} />
                            <input
                                type="text"
                                placeholder="Search cinemas..."
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
                                    <th>Cinema Name</th>
                                    <th>Location</th>
                                    <th>Total Halls</th>
                                    <th>Status</th>
                                    <th style={{ textAlign: 'right' }}>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                {cinemas.filter(c => c.name.toLowerCase().includes(searchTerm.toLowerCase())).map((item) => (
                                    <tr key={item.id}>
                                        <td style={{ color: 'var(--admin-text-secondary)' }}>#{item.id}</td>
                                        <td>
                                            <span style={{ fontWeight: '500' }}>{item.name}</span>
                                        </td>
                                        <td>
                                            <div style={{ display: 'flex', alignItems: 'center', gap: '6px', color: 'var(--admin-text-secondary)' }}>
                                                <MapPin size={14} />
                                                {item.location}
                                            </div>
                                        </td>
                                        <td>
                                            <div style={{ display: 'flex', alignItems: 'center', gap: '6px' }}>
                                                <Monitor size={14} />
                                                {item.halls} Halls
                                            </div>
                                        </td>
                                        <td>
                                            <span className={`status-badge ${item.status === 'Active' ? 'status-active' : 'status-inactive'
                                                }`}>
                                                {item.status}
                                            </span>
                                        </td>
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
                                <h3 className="modal-title">{editingCinema ? 'Edit Cinema' : 'Add New Cinema'}</h3>
                                <button className="modal-close" onClick={() => setShowModal(false)}>
                                    <X size={24} />
                                </button>
                            </div>
                            <form onSubmit={handleSubmit}>
                                <div className="modal-body">
                                    <div className="form-group">
                                        <label className="form-label">Cinema Name</label>
                                        <input
                                            type="text"
                                            className="form-control"
                                            value={formData.name}
                                            onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                                            required
                                        />
                                    </div>
                                    <div className="form-group">
                                        <label className="form-label">Location</label>
                                        <input
                                            type="text"
                                            className="form-control"
                                            value={formData.location}
                                            onChange={(e) => setFormData({ ...formData, location: e.target.value })}
                                            required
                                        />
                                    </div>
                                    <div className="form-group">
                                        <label className="form-label">Total Halls</label>
                                        <input
                                            type="number"
                                            className="form-control"
                                            value={formData.halls}
                                            onChange={(e) => setFormData({ ...formData, halls: e.target.value })}
                                            required
                                        />
                                    </div>
                                    <div className="form-group">
                                        <label className="form-label">Status</label>
                                        <select
                                            className="form-control"
                                            value={formData.status}
                                            onChange={(e) => setFormData({ ...formData, status: e.target.value })}
                                        >
                                            <option value="Active">Active</option>
                                            <option value="Maintenance">Maintenance</option>
                                            <option value="Closed">Closed</option>
                                        </select>
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

export default ManageCinemas;

import React, { useState } from 'react';
import AdminHeader from '../../components/Admin/AdminHeader';
import { Plus, Search, Filter, Tag, Calendar, Edit, Trash2, X } from 'lucide-react';

const ManageOffers = () => {
    const [searchTerm, setSearchTerm] = useState('');
    const [showModal, setShowModal] = useState(false);
    const [editingOffer, setEditingOffer] = useState(null);

    // Initial Mock Data
    const [offers, setOffers] = useState([
        { id: 1, title: 'Summer Blockbuster Sale', type: 'Event', code: '-', discount: '50% off', expiry: '2024-08-31', status: 'Active' },
        { id: 2, title: 'New User Welcome', type: 'Voucher', code: 'WELCOME50', discount: '$5.00', expiry: 'Permanent', status: 'Active' },
        { id: 3, title: 'Popcorn Weekend', type: 'Event', code: '-', discount: 'Free Popcorn', expiry: '2024-03-10', status: 'Expired' },
        { id: 4, title: 'Student Discount', type: 'Voucher', code: 'STUDENT24', discount: '20% off', expiry: '2024-12-31', status: 'Active' },
        { id: 5, title: 'Marvel Marathon', type: 'Event', code: '-', discount: 'Bundle Price', expiry: '2024-05-01', status: 'Upcoming' },
    ]);

    const [formData, setFormData] = useState({
        title: '',
        type: 'Voucher',
        code: '',
        discount: '',
        expiry: '',
        status: 'Active'
    });

    // Handlers
    const handleAdd = () => {
        setEditingOffer(null);
        setFormData({
            title: '',
            type: 'Voucher',
            code: '',
            discount: '',
            expiry: '',
            status: 'Active'
        });
        setShowModal(true);
    };

    const handleEdit = (offer) => {
        setEditingOffer(offer);
        setFormData({ ...offer });
        setShowModal(true);
    };

    const handleDelete = (id) => {
        if (window.confirm('Are you sure you want to delete this item?')) {
            setOffers(offers.filter(offer => offer.id !== id));
        }
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        if (editingOffer) {
            // Update existing
            setOffers(offers.map(offer => offer.id === editingOffer.id ? { ...formData, id: offer.id } : offer));
        } else {
            // Add new
            setOffers([...offers, { ...formData, id: offers.length + 1 }]);
        }
        setShowModal(false);
    };

    return (
        <>
            <AdminHeader title="Offers & Events" />
            <div className="admin-content">
                <div className="admin-page-header">
                    <h2 className="admin-page-title">Offers & Events List</h2>
                    <button className="btn-primary" onClick={handleAdd}>
                        <Plus size={18} />
                        Add New Item
                    </button>
                </div>

                <div className="admin-card" style={{ padding: '0', overflow: 'hidden' }}>
                    {/* Toolbar */}
                    <div style={{ padding: '16px 24px', borderBottom: '1px solid var(--admin-border)', display: 'flex', justifyContent: 'space-between', alignItems: 'center', flexWrap: 'wrap', gap: '16px' }}>
                        <div style={{ position: 'relative', maxWidth: '300px', width: '100%' }}>
                            <Search size={18} style={{ position: 'absolute', left: '12px', top: '50%', transform: 'translateY(-50%)', color: 'var(--admin-text-secondary)' }} />
                            <input
                                type="text"
                                placeholder="Search offers..."
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
                                    <th>Title</th>
                                    <th>Type</th>
                                    <th>Code</th>
                                    <th>Discount</th>
                                    <th>Expiry</th>
                                    <th>Status</th>
                                    <th style={{ textAlign: 'right' }}>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                {offers.filter(item => item.title.toLowerCase().includes(searchTerm.toLowerCase())).map((item) => (
                                    <tr key={item.id}>
                                        <td style={{ color: 'var(--admin-text-secondary)' }}>#{item.id}</td>
                                        <td>
                                            <span style={{ fontWeight: '500' }}>{item.title}</span>
                                        </td>
                                        <td>
                                            <div style={{ display: 'flex', alignItems: 'center', gap: '6px' }}>
                                                {item.type === 'Voucher' ? <Tag size={14} color="var(--admin-primary)" /> : <Calendar size={14} color="#f59e0b" />}
                                                <span style={{ fontSize: '0.9rem' }}>{item.type}</span>
                                            </div>
                                        </td>
                                        <td>
                                            {item.code !== '-' ? (
                                                <code style={{ background: 'rgba(255,255,255,0.1)', padding: '2px 6px', borderRadius: '4px', fontFamily: 'monospace' }}>{item.code}</code>
                                            ) : (
                                                <span style={{ color: 'var(--admin-text-secondary)' }}>-</span>
                                            )}
                                        </td>
                                        <td style={{ color: 'var(--admin-success)', fontWeight: '500' }}>{item.discount}</td>
                                        <td>{item.expiry}</td>
                                        <td>
                                            <span className={`status-badge ${item.status === 'Active' ? 'status-active' :
                                                    item.status === 'Upcoming' ? 'status-inactive' : 'status-inactive'
                                                }`} style={item.status === 'Upcoming' ? { background: 'rgba(59, 130, 246, 0.2)', color: '#60a5fa' } : {}}>
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
                                <h3 className="modal-title">{editingOffer ? 'Edit Offer' : 'Add New Offer'}</h3>
                                <button className="modal-close" onClick={() => setShowModal(false)}>
                                    <X size={24} />
                                </button>
                            </div>
                            <form onSubmit={handleSubmit}>
                                <div className="modal-body">
                                    <div className="form-group">
                                        <label className="form-label">Title</label>
                                        <input
                                            type="text"
                                            className="form-control"
                                            value={formData.title}
                                            onChange={(e) => setFormData({ ...formData, title: e.target.value })}
                                            required
                                        />
                                    </div>
                                    <div className="form-group">
                                        <label className="form-label">Type</label>
                                        <select
                                            className="form-control"
                                            value={formData.type}
                                            onChange={(e) => setFormData({ ...formData, type: e.target.value })}
                                        >
                                            <option value="Voucher">Voucher</option>
                                            <option value="Event">Event</option>
                                        </select>
                                    </div>
                                    {formData.type === 'Voucher' && (
                                        <div className="form-group">
                                            <label className="form-label">Code</label>
                                            <input
                                                type="text"
                                                className="form-control"
                                                value={formData.code}
                                                onChange={(e) => setFormData({ ...formData, code: e.target.value })}
                                            />
                                        </div>
                                    )}
                                    <div className="form-group">
                                        <label className="form-label">Discount / Offer</label>
                                        <input
                                            type="text"
                                            className="form-control"
                                            value={formData.discount}
                                            onChange={(e) => setFormData({ ...formData, discount: e.target.value })}
                                            placeholder="e.g. 50% off or Free Popcorn"
                                            required
                                        />
                                    </div>
                                    <div className="form-group">
                                        <label className="form-label">Expiry / Date</label>
                                        <input
                                            type="text"
                                            className="form-control"
                                            value={formData.expiry}
                                            onChange={(e) => setFormData({ ...formData, expiry: e.target.value })}
                                            placeholder="YYYY-MM-DD"
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
                                            <option value="Upcoming">Upcoming</option>
                                            <option value="Expired">Expired</option>
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

export default ManageOffers;

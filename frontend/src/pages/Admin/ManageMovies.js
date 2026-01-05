import React, { useState } from 'react';
import AdminHeader from '../../components/Admin/AdminHeader';
import bannerData from '../../data/banner.json';
import { Edit, Trash2, Plus, Search, Filter, X } from 'lucide-react';

const ManageMovies = () => {
    const [searchTerm, setSearchTerm] = useState('');
    const [movies, setMovies] = useState(bannerData);
    const [showModal, setShowModal] = useState(false);
    const [editingMovie, setEditingMovie] = useState(null);

    const [formData, setFormData] = useState({
        title: '',
        rating: '',
        duration: '',
        year: '',
        age: '',
        genres: '',
        description: '',
        bgImg: ''
    });

    const handleAdd = () => {
        setEditingMovie(null);
        setFormData({
            title: '',
            rating: '',
            duration: '',
            year: '',
            age: '',
            genres: '',
            description: '',
            bgImg: ''
        });
        setShowModal(true);
    };

    const handleEdit = (movie) => {
        setEditingMovie(movie);
        setFormData({
            ...movie,
            genres: movie.genres ? movie.genres.join(', ') : '',
            bgImg: movie.bgImg || movie.image || ''
        });
        setShowModal(true);
    };

    const handleDelete = (id) => {
        if (window.confirm('Are you sure you want to delete this movie?')) {
            setMovies(movies.filter(m => m.id !== id));
        }
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        const genresArray = formData.genres.split(',').map(g => g.trim()).filter(g => g);

        if (editingMovie) {
            setMovies(movies.map(m => m.id === editingMovie.id ? { ...formData, id: m.id, genres: genresArray } : m));
        } else {
            setMovies([...movies, { ...formData, id: movies.length + 1, genres: genresArray }]);
        }
        setShowModal(false);
    };

    return (
        <>
            <AdminHeader title="Manage Movies" />
            <div className="admin-content">
                <div className="admin-page-header">
                    <h2 className="admin-page-title">Movie List</h2>
                    <button className="btn-primary" onClick={handleAdd}>
                        <Plus size={18} />
                        Add New Movie
                    </button>
                </div>

                <div className="admin-card" style={{ padding: '0', overflow: 'hidden' }}>
                    {/* Toolbar */}
                    <div style={{ padding: '16px 24px', borderBottom: '1px solid var(--admin-border)', display: 'flex', justifyContent: 'space-between', alignItems: 'center', flexWrap: 'wrap', gap: '16px' }}>
                        <div style={{ position: 'relative', maxWidth: '300px', width: '100%' }}>
                            <Search size={18} style={{ position: 'absolute', left: '12px', top: '50%', transform: 'translateY(-50%)', color: 'var(--admin-text-secondary)' }} />
                            <input
                                type="text"
                                placeholder="Search movies..."
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
                                    <th>Genre</th>
                                    <th>Duration</th>
                                    <th>Rating</th>
                                    <th>Release Status</th>
                                    <th style={{ textAlign: 'right' }}>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                {movies.filter(m => m.title.toLowerCase().includes(searchTerm.toLowerCase())).map((movie, index) => (
                                    <tr key={movie.id}>
                                        <td style={{ color: 'var(--admin-text-secondary)' }}>#{index + 1}</td>
                                        <td>
                                            <div style={{ display: 'flex', alignItems: 'center', gap: '16px' }}>
                                                {movie.bgImg || movie.image ? (
                                                    <img
                                                        src={movie.bgImg || movie.image}
                                                        alt={movie.title}
                                                        style={{ width: '40px', height: '60px', objectFit: 'cover', borderRadius: '4px' }}
                                                    />
                                                ) : <div style={{ width: 40, height: 60, background: '#333', borderRadius: 4 }}></div>}
                                                <span style={{ fontWeight: '500' }}>{movie.title}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div style={{ display: 'flex', flexWrap: 'wrap', gap: '4px' }}>
                                                {movie.genres && movie.genres.map((genre, idx) => (
                                                    <span key={idx} style={{ padding: '2px 8px', background: 'rgba(255,255,255,0.1)', borderRadius: '4px', fontSize: '0.75rem', color: 'var(--admin-text-secondary)' }}>
                                                        {genre}
                                                    </span>
                                                ))}
                                            </div>
                                        </td>
                                        <td>{movie.duration}</td>
                                        <td>
                                            <span style={{ display: 'inline-flex', alignItems: 'center', gap: '4px', fontWeight: '500', color: '#eab308' }}>
                                                ★ {movie.rating || 'N/A'}
                                            </span>
                                        </td>
                                        <td>
                                            <span className="status-badge status-active">
                                                Active
                                            </span>
                                        </td>
                                        <td style={{ textAlign: 'right' }}>
                                            <div style={{ display: 'flex', justifyContent: 'flex-end', gap: '8px' }}>
                                                <button className="action-btn edit" title="Edit" onClick={() => handleEdit(movie)}>
                                                    <Edit size={18} />
                                                </button>
                                                <button className="action-btn delete" title="Delete" onClick={() => handleDelete(movie.id)}>
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
                                <h3 className="modal-title">{editingMovie ? 'Edit Movie' : 'Add New Movie'}</h3>
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
                                    <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '16px' }}>
                                        <div className="form-group">
                                            <label className="form-label">Rating</label>
                                            <input
                                                type="text"
                                                className="form-control"
                                                value={formData.rating}
                                                onChange={(e) => setFormData({ ...formData, rating: e.target.value })}
                                                placeholder="e.g. 8.5"
                                            />
                                        </div>
                                        <div className="form-group">
                                            <label className="form-label">Duration</label>
                                            <input
                                                type="text"
                                                className="form-control"
                                                value={formData.duration}
                                                onChange={(e) => setFormData({ ...formData, duration: e.target.value })}
                                                placeholder="e.g. 2h 15m"
                                            />
                                        </div>
                                    </div>
                                    <div className="form-group">
                                        <label className="form-label">Genres (comma separated)</label>
                                        <input
                                            type="text"
                                            className="form-control"
                                            value={formData.genres}
                                            onChange={(e) => setFormData({ ...formData, genres: e.target.value })}
                                            placeholder="Action, Drama, Sci-Fi"
                                        />
                                    </div>
                                    <div className="form-group">
                                        <label className="form-label">Image URL</label>
                                        <input
                                            type="text"
                                            className="form-control"
                                            value={formData.bgImg}
                                            onChange={(e) => setFormData({ ...formData, bgImg: e.target.value })}
                                            placeholder="https://example.com/poster.jpg"
                                        />
                                        {formData.bgImg && (
                                            <div style={{ marginTop: '10px' }}>
                                                <img src={formData.bgImg} alt="Preview" style={{ height: '80px', borderRadius: '4px', objectFit: 'cover' }} onError={(e) => e.target.style.display = 'none'} />
                                            </div>
                                        )}
                                    </div>
                                    <div className="form-group">
                                        <label className="form-label">Description</label>
                                        <textarea
                                            className="form-control"
                                            rows="3"
                                            value={formData.description}
                                            onChange={(e) => setFormData({ ...formData, description: e.target.value })}
                                        ></textarea>
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

export default ManageMovies;

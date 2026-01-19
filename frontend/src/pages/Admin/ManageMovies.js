import React, { useState, useEffect } from 'react';
import { useToast } from '../../context/ToastContext';
import AdminHeader from '../../components/Admin/AdminHeader';
import adminService from '../../services/adminService';
import { Edit, Trash2, Plus, Search, Filter, X, Loader2, Star } from 'lucide-react';

const ManageMovies = () => {
    const toast = useToast();
    const [searchTerm, setSearchTerm] = useState('');
    const [movies, setMovies] = useState([]);
    const [loading, setLoading] = useState(true);
    const [showModal, setShowModal] = useState(false);
    const [editingMovie, setEditingMovie] = useState(null);

    const [genresList, setGenresList] = useState([]);
    const [languagesList, setLanguagesList] = useState([]);

    // Initial Form State
    const [formData, setFormData] = useState({
        title: '',
        age_rating: '',
        duration: '',
        release_date: '',
        status: 'now_showing',
        description: '',
        synopsis: '',
        content: '',
        poster_url: '',
        banner_url: '',
        trailer_url: '',
        is_featured: false,
        genre_ids: [],
        language_ids: []
    });

    const fetchMovies = async () => {
        setLoading(true);
        try {
            const response = await adminService.getMovies({ search: searchTerm });
            if (response.success) {
                setMovies(response.data.data);
            }
        } catch (error) {
            console.error("Failed to fetch movies", error);
        } finally {
            setLoading(false);
        }
    };

    const fetchMetadata = async () => {
        try {
            const [genresRes, langsRes] = await Promise.all([
                adminService.getGenres(),
                adminService.getLanguages()
            ]);
            if (genresRes.success) setGenresList(genresRes.data);
            if (langsRes.success) setLanguagesList(langsRes.data);
        } catch (error) {
            console.error("Failed to fetch metadata", error);
        }
    };

    useEffect(() => {
        fetchMetadata();
    }, []);

    useEffect(() => {
        const timeoutId = setTimeout(() => {
            fetchMovies();
        }, 500);
        return () => clearTimeout(timeoutId);
    }, [searchTerm]);

    const handleAdd = () => {
        setEditingMovie(null);
        setFormData({
            title: '',
            age_rating: 'P',
            duration: '',
            release_date: '',
            status: 'now_showing',
            description: '',
            synopsis: '',
            content: '',
            poster_url: '',
            banner_url: '',
            trailer_url: '',
            is_featured: false,
            genre_ids: [],
            language_ids: []
        });
        setShowModal(true);
    };

    const handleEdit = (movie) => {
        setEditingMovie(movie);
        setFormData({
            title: movie.title,
            age_rating: movie.age_rating || 'P',
            duration: movie.duration,
            release_date: movie.release_date ? movie.release_date.split('T')[0] : '',
            status: movie.status,
            description: movie.description || '',
            synopsis: movie.synopsis || '',
            content: movie.content || '',
            poster_url: movie.poster_url || '',
            banner_url: movie.banner_url || '',
            trailer_url: movie.trailer_url || '',
            is_featured: movie.is_featured === 1 || movie.is_featured === true,
            genre_ids: movie.genres ? movie.genres.map(g => g.genre_id) : [],
            language_ids: movie.languages ? movie.languages.map(l => l.language_id) : []
        });
        setShowModal(true);
    };

    const handleDelete = async (id) => {
        if (window.confirm('Are you sure you want to delete this movie?')) {
            try {
                await adminService.deleteMovie(id);
                fetchMovies();
                toast.success('Movie deleted successfully');
            } catch (error) {
                toast.error('Failed to delete movie. It might have active showtimes.');
            }
        }
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        try {
            const payload = { ...formData, is_featured: formData.is_featured ? 1 : 0 };

            if (editingMovie) {
                await adminService.updateMovie(editingMovie.id || editingMovie.movie_id, payload);
            } else {
                await adminService.createMovie(payload);
            }
            setShowModal(false);
            fetchMovies();
            toast.success(editingMovie ? 'Movie updated successfully' : 'Movie created successfully');
        } catch (error) {
            console.error("Failed to save movie", error);
            toast.error('Failed to save movie. Please check your input.');
        }
    };

    // Helper for multi-select
    const toggleSelection = (list, id, key) => {
        const current = formData[key];
        const newSelection = current.includes(id)
            ? current.filter(item => item !== id)
            : [...current, id];
        setFormData({ ...formData, [key]: newSelection });
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
                    </div>

                    <div className="admin-table-container" style={{ border: 'none', borderRadius: '0', minHeight: '300px' }}>
                        {loading ? (
                            <div style={{ display: 'flex', justifyContent: 'center', alignItems: 'center', height: '100%', padding: '40px' }}>
                                <Loader2 className="animate-spin text-white" size={32} />
                            </div>
                        ) : (
                            <table className="admin-table">
                                <thead>
                                    <tr>
                                        <th style={{ width: '60px' }}>ID</th>
                                        <th>Title</th>
                                        <th>Duration</th>
                                        <th>Genres</th>
                                        <th>Release Date</th>
                                        <th>Status</th>
                                        <th style={{ textAlign: 'right' }}>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {movies.length > 0 ? movies.map((movie, index) => (
                                        <tr key={movie.id || movie.movie_id}>
                                            <td style={{ color: 'var(--admin-text-secondary)' }}>#{movie.id || movie.movie_id}</td>
                                            <td>
                                                <div style={{ display: 'flex', alignItems: 'center', gap: '16px' }}>
                                                    {movie.poster_url ? (
                                                        <img
                                                            src={movie.poster_url}
                                                            alt={movie.title}
                                                            style={{ width: '40px', height: '60px', objectFit: 'cover', borderRadius: '4px' }}
                                                        />
                                                    ) : <div style={{ width: 40, height: 60, background: '#333', borderRadius: 4 }}></div>}
                                                    <div>
                                                        <div style={{ fontWeight: '500' }}>{movie.title}</div>
                                                        <div style={{ fontSize: '0.8rem', color: 'var(--admin-text-secondary)', display: 'flex', alignItems: 'center', gap: '8px', marginTop: '4px' }}>
                                                            <span style={{
                                                                padding: '2px 6px',
                                                                borderRadius: '4px',
                                                                backgroundColor: movie.age_rating === 'P' ? '#10b981' :
                                                                    movie.age_rating === 'C18' || movie.age_rating === 'T18' ? '#ef4444' :
                                                                        movie.age_rating === 'C16' || movie.age_rating === 'T16' ? '#f97316' :
                                                                            movie.age_rating === 'C13' || movie.age_rating === 'T13' ? '#eab308' : '#6b7280',
                                                                color: 'white',
                                                                fontWeight: 'bold',
                                                                fontSize: '0.7rem'
                                                            }}>
                                                                {movie.age_rating || 'N/A'}
                                                            </span>
                                                            <div style={{ display: 'flex', alignItems: 'center', gap: '4px', color: '#ffc107' }}>
                                                                <Star size={12} fill="#ffc107" />
                                                                <span>{movie.reviews_avg_rating ? parseFloat(movie.reviews_avg_rating).toFixed(1) : 'N/A'}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{movie.duration} min</td>
                                            <td>
                                                {movie.genres && movie.genres.map(g => (
                                                    <span key={g.genre_id} style={{ display: 'inline-block', fontSize: '0.75rem', background: '#333', padding: '2px 6px', borderRadius: '4px', marginRight: '4px', marginBottom: '4px' }}>
                                                        {g.name}
                                                    </span>
                                                ))}
                                            </td>
                                            <td>{movie.release_date ? new Date(movie.release_date).toLocaleDateString() : 'N/A'}</td>
                                            <td>
                                                <span className={`status-badge ${movie.status === 'now_showing' ? 'status-active' :
                                                    movie.status === 'coming_soon' ? 'status-inactive' : 'status-inactive'
                                                    }`} style={{ textTransform: 'uppercase' }}>
                                                    {movie.status?.replace('_', ' ')}
                                                </span>
                                            </td>
                                            <td style={{ textAlign: 'right' }}>
                                                <div style={{ display: 'flex', justifyContent: 'flex-end', gap: '8px' }}>
                                                    <button className="action-btn edit" title="Edit" onClick={() => handleEdit(movie)}>
                                                        <Edit size={18} />
                                                    </button>
                                                    <button className="action-btn delete" title="Delete" onClick={() => handleDelete(movie.id || movie.movie_id)}>
                                                        <Trash2 size={18} />
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    )) : (
                                        <tr>
                                            <td colSpan="7" style={{ textAlign: 'center', padding: '20px', color: '#888' }}>No movies found</td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                        )}
                    </div>
                </div>

                {/* Modal */}
                {showModal && (
                    <div className="admin-modal-overlay" onClick={(e) => {
                        if (e.target === e.currentTarget) setShowModal(false);
                    }}>
                        <div className="admin-modal" style={{ maxWidth: '800px' }}>
                            <div className="modal-header">
                                <h3 className="modal-title">{editingMovie ? 'Edit Movie' : 'Add New Movie'}</h3>
                                <button className="modal-close" onClick={() => setShowModal(false)}>
                                    <X size={24} />
                                </button>
                            </div>
                            <form onSubmit={handleSubmit}>
                                <div className="modal-body" style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '24px' }}>

                                    {/* Left Column */}
                                    <div style={{ display: 'flex', flexDirection: 'column', gap: '16px' }}>
                                        <div className="form-group">
                                            <label className="form-label">Title</label>
                                            <input type="text" className="form-control" value={formData.title} onChange={(e) => setFormData({ ...formData, title: e.target.value })} required />
                                        </div>

                                        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '16px' }}>
                                            <div className="form-group">
                                                <label className="form-label">Age Rating</label>
                                                <select className="form-control" value={formData.age_rating} onChange={(e) => setFormData({ ...formData, age_rating: e.target.value })}>
                                                    <option value="P">P (General)</option>
                                                    <option value="K">K (Under 13 with Guardian)</option>
                                                    <option value="C13">C13 (13+)</option>
                                                    <option value="C16">C16 (16+)</option>
                                                    <option value="C18">C18 (18+)</option>
                                                    <option value="T13">T13 (13+)</option>
                                                    <option value="T16">T16 (16+)</option>
                                                    <option value="T18">T18 (18+)</option>
                                                </select>
                                            </div>
                                            <div className="form-group">
                                                <label className="form-label">Duration (min)</label>
                                                <input type="number" className="form-control" value={formData.duration} onChange={(e) => setFormData({ ...formData, duration: e.target.value })} required />
                                            </div>
                                        </div>

                                        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '16px' }}>
                                            <div className="form-group">
                                                <label className="form-label">Release Date</label>
                                                <input type="date" className="form-control" value={formData.release_date} onChange={(e) => setFormData({ ...formData, release_date: e.target.value })} required />
                                            </div>
                                            <div className="form-group">
                                                <label className="form-label">Status</label>
                                                <select className="form-control" value={formData.status} onChange={(e) => setFormData({ ...formData, status: e.target.value })}>
                                                    <option value="now_showing">Now Showing</option>
                                                    <option value="coming_soon">Coming Soon</option>
                                                    <option value="ended">Ended</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div className="form-group">
                                            <label className="form-label">Genres</label>
                                            <div style={{ display: 'flex', flexWrap: 'wrap', gap: '8px', maxHeight: '100px', overflowY: 'auto', padding: '8px', border: '1px solid var(--admin-border)', borderRadius: '8px' }}>
                                                {genresList.map(g => (
                                                    <div
                                                        key={g.genre_id}
                                                        onClick={() => toggleSelection(genresList, g.genre_id, 'genre_ids')}
                                                        style={{
                                                            padding: '4px 8px',
                                                            borderRadius: '4px',
                                                            fontSize: '0.85rem',
                                                            cursor: 'pointer',
                                                            backgroundColor: formData.genre_ids.includes(g.genre_id) ? 'var(--admin-primary)' : 'rgba(255,255,255,0.1)',
                                                            color: 'white'
                                                        }}
                                                    >
                                                        {g.name}
                                                    </div>
                                                ))}
                                            </div>
                                        </div>

                                        <div className="form-group">
                                            <label className="form-label">Languages/Format</label>
                                            <div style={{ display: 'flex', flexWrap: 'wrap', gap: '8px' }}>
                                                {languagesList.map(l => (
                                                    <div
                                                        key={l.language_id}
                                                        onClick={() => toggleSelection(languagesList, l.language_id, 'language_ids')}
                                                        style={{
                                                            padding: '4px 8px',
                                                            borderRadius: '4px',
                                                            fontSize: '0.85rem',
                                                            cursor: 'pointer',
                                                            backgroundColor: formData.language_ids.includes(l.language_id) ? 'var(--admin-primary)' : 'rgba(255,255,255,0.1)',
                                                            color: 'white'
                                                        }}
                                                    >
                                                        {l.name}
                                                    </div>
                                                ))}
                                            </div>
                                        </div>
                                    </div>

                                    {/* Right Column */}
                                    <div style={{ display: 'flex', flexDirection: 'column', gap: '16px' }}>
                                        <div className="form-group">
                                            <label className="form-label">Poster URL</label>
                                            <input type="text" className="form-control" value={formData.poster_url} onChange={(e) => setFormData({ ...formData, poster_url: e.target.value })} placeholder="https://example.com/poster.jpg" />
                                        </div>
                                        <div className="form-group">
                                            <label className="form-label">Banner URL</label>
                                            <input type="text" className="form-control" value={formData.banner_url} onChange={(e) => setFormData({ ...formData, banner_url: e.target.value })} placeholder="https://example.com/banner.jpg" />
                                        </div>
                                        <div className="form-group">
                                            <label className="form-label">Trailer URL</label>
                                            <input type="text" className="form-control" value={formData.trailer_url} onChange={(e) => setFormData({ ...formData, trailer_url: e.target.value })} placeholder="Youtube URL" />
                                        </div>
                                        <div className="form-group">
                                            <label className="form-label">Description</label>
                                            <textarea className="form-control" rows="2" value={formData.description} onChange={(e) => setFormData({ ...formData, description: e.target.value })}></textarea>
                                        </div>
                                        <div className="form-group">
                                            <label className="form-label">Synopsis</label>
                                            <textarea className="form-control" rows="3" value={formData.synopsis} onChange={(e) => setFormData({ ...formData, synopsis: e.target.value })}></textarea>
                                        </div>
                                        <div className="form-group">
                                            <label className="form-label">Content</label>
                                            <textarea className="form-control" rows="2" value={formData.content} onChange={(e) => setFormData({ ...formData, content: e.target.value })}></textarea>
                                        </div>
                                        <div className="form-group">
                                            <label className="form-label" style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
                                                <input type="checkbox" checked={formData.is_featured} onChange={(e) => setFormData({ ...formData, is_featured: e.target.checked })} style={{ width: 'auto', margin: 0 }} />
                                                Is Featured?
                                            </label>
                                        </div>
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

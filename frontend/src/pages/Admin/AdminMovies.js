import React, { useEffect, useState } from 'react';
import AdminHeader from '../../components/Admin/AdminHeader';
import { Plus, Edit2, Trash2, Search, Loader2 } from 'lucide-react';
import adminService from '../../services/adminService';

const AdminMovies = () => {
    const [movies, setMovies] = useState([]);
    const [loading, setLoading] = useState(true);
    const [searchTerm, setSearchTerm] = useState('');

    useEffect(() => {
        fetchMovies();
    }, []);

    const fetchMovies = async () => {
        try {
            setLoading(true);
            const response = await adminService.getMovies({ search: searchTerm });
            if (response.success) {
                setMovies(response.data.data); // data.data because of pagination
            }
        } catch (error) {
            console.error("Failed to load movies", error);
        } finally {
            setLoading(false);
        }
    };

    const handleSearch = (e) => {
        e.preventDefault();
        fetchMovies();
    }

    return (
        <>
            <AdminHeader title="Movie Management" />
            <div className="admin-content">
                <div className="admin-page-header">
                    <h2 className="admin-page-title">Movies List</h2>
                    <button className="btn-primary">
                        <Plus size={18} /> Add New Movie
                    </button>
                </div>

                <div className="admin-card">
                    <div style={{ paddingBottom: '24px', display: 'flex', gap: '12px' }}>
                        <form onSubmit={handleSearch} style={{ display: 'flex', gap: '12px', flex: 1 }}>
                            <div style={{ position: 'relative', flex: 1, maxWidth: '400px' }}>
                                <Search size={18} style={{ position: 'absolute', left: '12px', top: '50%', transform: 'translateY(-50%)', color: 'var(--admin-text-secondary)' }} />
                                <input
                                    type="text"
                                    className="form-control"
                                    placeholder="Search movies by title..."
                                    style={{ paddingLeft: '40px' }}
                                    value={searchTerm}
                                    onChange={(e) => setSearchTerm(e.target.value)}
                                />
                            </div>
                            <button type="button" onClick={fetchMovies} className="btn-secondary">Search</button>
                        </form>
                    </div>

                    <div className="admin-table-container">
                        {loading ? (
                            <div className="p-8 flex justify-center">
                                <Loader2 className="animate-spin text-red-500" />
                            </div>
                        ) : (
                            <table className="admin-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Poster</th>
                                        <th>Title</th>
                                        <th>Duration</th>
                                        <th>Release Date</th>
                                        <th>Status</th>
                                        <th style={{ textAlign: 'right' }}>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {movies.map(movie => (
                                        <tr key={movie.id}>
                                            <td>#{movie.id}</td>
                                            <td>
                                                <div style={{ width: '40px', height: '60px', borderRadius: '4px', overflow: 'hidden', background: '#333' }}>
                                                    <img src={movie.poster_url} alt="" style={{ width: '100%', height: '100%', objectFit: 'cover' }} />
                                                </div>
                                            </td>
                                            <td style={{ fontWeight: 500 }}>{movie.title}</td>
                                            <td>{movie.duration} min</td>
                                            <td>{movie.release_date}</td>
                                            <td>
                                                <span className={`status-badge ${movie.status === 'now_showing' ? 'status-active' : 'status-inactive'}`}>
                                                    {movie.status === 'now_showing' ? 'Now Showing' : (movie.status === 'coming_soon' ? 'Coming Soon' : 'Ended')}
                                                </span>
                                            </td>
                                            <td style={{ textAlign: 'right' }}>
                                                <button className="action-btn edit" title="Edit">
                                                    <Edit2 size={16} />
                                                </button>
                                                <button className="action-btn delete" title="Delete">
                                                    <Trash2 size={16} />
                                                </button>
                                            </td>
                                        </tr>
                                    ))}
                                    {movies.length === 0 && (
                                        <tr>
                                            <td colSpan="7" style={{ textAlign: 'center', padding: '32px', color: 'var(--admin-text-secondary)' }}>
                                                No movies found
                                            </td>
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

export default AdminMovies;

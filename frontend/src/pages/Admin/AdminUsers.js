import React, { useEffect, useState } from 'react';
import AdminHeader from '../../components/Admin/AdminHeader';
import { Search, Loader2 } from 'lucide-react';
import adminService from '../../services/adminService';

const AdminUsers = () => {
    const [users, setUsers] = useState([]);
    const [loading, setLoading] = useState(true);
    const [searchTerm, setSearchTerm] = useState('');

    useEffect(() => {
        fetchUsers();
    }, []);

    const fetchUsers = async () => {
        try {
            setLoading(true);
            const response = await adminService.getUsers({ search: searchTerm });
            if (response.success) {
                setUsers(response.data.data);
            }
        } catch (error) {
            console.error("Failed to load users", error);
        } finally {
            setLoading(false);
        }
    };

    const handleSearch = (e) => {
        e.preventDefault();
        fetchUsers();
    }

    return (
        <>
            <AdminHeader title="User Management" />
            <div className="admin-content">
                <div className="admin-page-header">
                    <h2 className="admin-page-title">Users List</h2>
                </div>

                <div className="admin-card">
                    <div style={{ paddingBottom: '24px', display: 'flex', gap: '12px' }}>
                        <form onSubmit={handleSearch} style={{ display: 'flex', gap: '12px', flex: 1 }}>
                            <div style={{ position: 'relative', flex: 1, maxWidth: '400px' }}>
                                <Search size={18} style={{ position: 'absolute', left: '12px', top: '50%', transform: 'translateY(-50%)', color: 'var(--admin-text-secondary)' }} />
                                <input
                                    type="text"
                                    className="form-control"
                                    placeholder="Search users by name or email..."
                                    style={{ paddingLeft: '40px' }}
                                    value={searchTerm}
                                    onChange={(e) => setSearchTerm(e.target.value)}
                                />
                            </div>
                            <button type="button" onClick={fetchUsers} className="btn-secondary">Search</button>
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
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Joined At</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {users.map(user => (
                                        <tr key={user.id}>
                                            <td>#{user.id}</td>
                                            <td style={{ fontWeight: 500 }}>{user.name}</td>
                                            <td>{user.email}</td>
                                            <td>
                                                <span className={`status-badge ${user.role === 'admin' ? 'status-active' : 'status-inactive'}`}>
                                                    {user.role}
                                                </span>
                                            </td>
                                            <td>{new Date(user.created_at).toLocaleDateString()}</td>
                                        </tr>
                                    ))}
                                    {users.length === 0 && (
                                        <tr>
                                            <td colSpan="5" style={{ textAlign: 'center', padding: '32px', color: 'var(--admin-text-secondary)' }}>
                                                No users found
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

export default AdminUsers;

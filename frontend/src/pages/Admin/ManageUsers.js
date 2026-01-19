import React, { useState, useEffect } from 'react';
import AdminHeader from '../../components/Admin/AdminHeader';
import adminService from '../../services/adminService';
import { Loader2 } from 'lucide-react';

const ManageUsers = () => {
    const [users, setUsers] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        const fetchUsers = async () => {
            try {
                const response = await adminService.getUsers();
                if (response.success) {
                    setUsers(response.data);
                }
            } catch (error) {
                console.error("Failed to fetch users", error);
            } finally {
                setLoading(false);
            }
        };

        fetchUsers();
    }, []);

    if (loading) {
        return (
            <>
                <AdminHeader title="Manage Users" />
                <div className="admin-content flex justify-center items-center h-64">
                    <Loader2 className="animate-spin text-white" size={48} />
                </div>
            </>
        );
    }

    return (
        <>
            <AdminHeader title="Manage Users" />
            <div className="admin-content">
                <div className="admin-card">
                    <div className="admin-table-container">
                        <table className="admin-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Joined Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                {users.length > 0 ? (
                                    users.map(user => (
                                        <tr key={user.id}>
                                            <td>{user.name}</td>
                                            <td style={{ color: 'var(--admin-text-secondary)' }}>{user.email}</td>
                                            <td>
                                                <span style={{
                                                    padding: '4px 8px',
                                                    borderRadius: '4px',
                                                    fontSize: '0.85rem',
                                                    backgroundColor: user.role === 'admin' ? 'rgba(59, 130, 246, 0.2)' : 'rgba(255, 255, 255, 0.1)',
                                                    color: user.role === 'admin' ? '#60a5fa' : 'var(--admin-text-secondary)',
                                                    textTransform: 'capitalize'
                                                }}>
                                                    {user.role}
                                                </span>
                                            </td>
                                            <td>{new Date(user.created_at).toLocaleDateString()}</td>
                                            <td>
                                                <span className="status-badge status-active">
                                                    Active
                                                </span>
                                            </td>
                                        </tr>
                                    ))
                                ) : (
                                    <tr>
                                        <td colSpan="5" className="text-center py-8 text-gray-500">
                                            No users found.
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </>
    );
};

export default ManageUsers;

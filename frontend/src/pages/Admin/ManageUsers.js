import React from 'react';
import AdminHeader from '../../components/Admin/AdminHeader';

const ManageUsers = () => {
    // Mock user data
    const users = [
        { id: 1, name: 'John Doe', email: 'john@example.com', role: 'User', date: '2023-12-01' },
        { id: 2, name: 'Jane Smith', email: 'jane@example.com', role: 'Premium', date: '2023-12-05' },
        { id: 3, name: 'Admin User', email: 'admin@cinebook.com', role: 'Admin', date: '2023-11-20' },
    ];

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
                                {users.map(user => (
                                    <tr key={user.id}>
                                        <td>{user.name}</td>
                                        <td style={{ color: 'var(--admin-text-secondary)' }}>{user.email}</td>
                                        <td>
                                            <span style={{
                                                padding: '4px 8px',
                                                borderRadius: '4px',
                                                fontSize: '0.85rem',
                                                backgroundColor: user.role === 'Admin' ? 'rgba(59, 130, 246, 0.2)' : 'rgba(255, 255, 255, 0.1)',
                                                color: user.role === 'Admin' ? '#60a5fa' : 'var(--admin-text-secondary)'
                                            }}>
                                                {user.role}
                                            </span>
                                        </td>
                                        <td>{user.date}</td>
                                        <td>
                                            <span className="status-badge status-active">
                                                Active
                                            </span>
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

export default ManageUsers;

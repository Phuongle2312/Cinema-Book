import React from 'react';

const AdminHeader = ({ title }) => {
    return (
        <header className="admin-header">
            <h1 className="header-title">{title}</h1>
            <div className="admin-user-profile">
                <div className="admin-avatar">A</div>
                <div style={{ display: 'flex', flexDirection: 'column' }}>
                    <span style={{ fontSize: '0.875rem', fontWeight: 600 }}>Admin User</span>
                    <span style={{ fontSize: '0.75rem', color: '#64748b' }}>Super Admin</span>
                </div>
            </div>
        </header>
    );
};

export default AdminHeader;

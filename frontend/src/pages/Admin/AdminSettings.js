import React from 'react';
import AdminHeader from '../../components/Admin/AdminHeader';

const AdminSettings = () => {
    return (
        <>
            <AdminHeader title="Settings" />
            <div className="admin-content">
                <div className="admin-card">
                    <h2>General Settings</h2>
                    <p>This is a placeholder for admin settings.</p>
                </div>
            </div>
        </>
    );
};

export default AdminSettings;

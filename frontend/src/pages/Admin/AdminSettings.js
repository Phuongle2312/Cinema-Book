import React from 'react';
import AdminHeader from '../../components/Admin/AdminHeader';
import { Save } from 'lucide-react';

const AdminSettings = () => {
    return (
        <>
            <AdminHeader title="Settings" />
            <div className="admin-content">
                <div className="admin-page-header">
                    <h2 className="admin-page-title">General Settings</h2>
                </div>

                <div className="admin-card" style={{ maxWidth: '800px' }}>
                    <form onSubmit={(e) => e.preventDefault()}>
                        <h3 style={{ marginBottom: '20px', color: 'var(--admin-text-main)', fontSize: '1.2rem' }}>System Configuration</h3>

                        <div className="form-group">
                            <label className="form-label">Site Name</label>
                            <input type="text" className="form-control" defaultValue="CineBook" />
                        </div>

                        <div className="form-group">
                            <label className="form-label">Support Email</label>
                            <input type="email" className="form-control" defaultValue="support@cinebook.com" />
                        </div>

                        <div className="form-group">
                            <label className="form-label">Booking Hold Time (Minutes)</label>
                            <input type="number" className="form-control" defaultValue="10" />
                        </div>

                        <div className="form-group">
                            <label className="form-label">Maintenance Mode</label>
                            <div style={{ display: 'flex', alignItems: 'center', gap: '12px' }}>
                                <label style={{ display: 'flex', alignItems: 'center', gap: '8px', color: 'var(--admin-text-main)', cursor: 'pointer' }}>
                                    <input type="radio" name="maintenance" defaultChecked /> Off
                                </label>
                                <label style={{ display: 'flex', alignItems: 'center', gap: '8px', color: 'var(--admin-text-main)', cursor: 'pointer' }}>
                                    <input type="radio" name="maintenance" /> On
                                </label>
                            </div>
                        </div>

                        <div style={{ marginTop: '32px' }}>
                            <button className="btn-primary">
                                <Save size={18} /> Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </>
    );
};

export default AdminSettings;

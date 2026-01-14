import React, { useEffect, useState } from 'react';
import AdminHeader from '../../components/Admin/AdminHeader';
import { Plus, MapPin, Loader2, Building, Edit2 } from 'lucide-react';
import adminService from '../../services/adminService';

const AdminCinemas = () => {
    const [cinemas, setCinemas] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        fetchCinemas();
    }, []);

    const fetchCinemas = async () => {
        try {
            setLoading(true);
            const response = await adminService.getTheaters();
            // Checking structure, usually Laravel pagination returns data inside 'data' again
            if (response.success) {
                setCinemas(response.data.data || response.data);
            }
        } catch (error) {
            console.error("Failed to load cinemas", error);
        } finally {
            setLoading(false);
        }
    };

    return (
        <>
            <AdminHeader title="Cinemas Management" />
            <div className="admin-content">
                <div className="admin-page-header">
                    <h2 className="admin-page-title">Theaters List</h2>
                    <button className="btn-primary">
                        <Plus size={18} /> Add New Theater
                    </button>
                </div>

                <div className="admin-card">
                    <div className="admin-table-container">
                        {loading ? (
                            <div className="p-8 flex justify-center">
                                <Loader2 className="animate-spin text-red-500" />
                            </div>
                        ) : (
                            <table className="admin-table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>City</th>
                                        <th>Address</th>
                                        <th>Total Rooms</th>
                                        <th style={{ textAlign: 'right' }}>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {cinemas.map(theater => (
                                        <tr key={theater.id}>
                                            <td style={{ fontWeight: 500 }}>
                                                <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
                                                    <Building size={16} className="text-gray-400" />
                                                    {theater.name}
                                                </div>
                                            </td>
                                            <td>{theater.city ? theater.city.name : 'Unknown'}</td>
                                            <td style={{ maxWidth: '300px', whiteSpace: 'nowrap', overflow: 'hidden', textOverflow: 'ellipsis' }}>
                                                <div style={{ display: 'flex', alignItems: 'center', gap: '6px' }}>
                                                    <MapPin size={14} className="text-gray-500" />
                                                    {theater.address}
                                                </div>
                                            </td>
                                            <td>{theater.total_rooms} Rooms</td>
                                            <td style={{ textAlign: 'right' }}>
                                                <button className="action-btn edit">
                                                    <Edit2 size={16} />
                                                </button>
                                            </td>
                                        </tr>
                                    ))}
                                    {cinemas.length === 0 && (
                                        <tr>
                                            <td colSpan="5" style={{ textAlign: 'center', padding: '32px', color: 'gray' }}>No theaters found</td>
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

export default AdminCinemas;

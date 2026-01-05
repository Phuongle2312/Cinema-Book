import React from 'react';
import { NavLink } from 'react-router-dom';
import { LayoutDashboard, Film, Users, Settings, LogOut, Ticket, Building, CalendarClock, MessageSquare } from 'lucide-react';

const AdminSidebar = () => {
  const handleLogout = () => {
    if (window.confirm('Are you sure you want to logout?')) {
      // Stub for logout logic
      console.log('Logging out...');
      window.location.href = '/login'; // Temporary redirect
    }
  };

  return (
    <aside className="admin-sidebar">
      <div className="sidebar-header">
        CINEBOOK <span>ADMIN</span>
      </div>
      <nav className="sidebar-nav">
        <NavLink
          to="/admin/dashboard"
          className={({ isActive }) => `nav-item ${isActive ? 'active' : ''}`}
        >
          <LayoutDashboard size={20} />
          Dashboard
        </NavLink>
        <NavLink
          to="/admin/movies"
          className={({ isActive }) => `nav-item ${isActive ? 'active' : ''}`}
        >
          <Film size={20} />
          Movies
        </NavLink>
        <NavLink
          to="/admin/users"
          className={({ isActive }) => `nav-item ${isActive ? 'active' : ''}`}
        >
          <Users size={20} />
          Users
        </NavLink>
        <NavLink
          to="/admin/offers"
          className={({ isActive }) => `nav-item ${isActive ? 'active' : ''}`}
        >
          <Ticket size={20} />
          Offers & Events
        </NavLink>
        <NavLink
          to="/admin/cinemas"
          className={({ isActive }) => `nav-item ${isActive ? 'active' : ''}`}
        >
          <Building size={20} />
          Cinemas
        </NavLink>
        <NavLink
          to="/admin/showtimes"
          className={({ isActive }) => `nav-item ${isActive ? 'active' : ''}`}
        >
          <CalendarClock size={20} />
          Showtimes
        </NavLink>
        <NavLink
          to="/admin/reviews"
          className={({ isActive }) => `nav-item ${isActive ? 'active' : ''}`}
        >
          <MessageSquare size={20} />
          Reviews
        </NavLink>
        <NavLink
          to="/admin/settings"
          className={({ isActive }) => `nav-item ${isActive ? 'active' : ''}`}
        >
          <Settings size={20} />
          Settings
        </NavLink>
      </nav>
      <div style={{ padding: '16px' }}>
        <button className="nav-item logout-btn" onClick={handleLogout}>
          <LogOut size={20} />
          Logout
        </button>
      </div>
    </aside>
  );
};

export default AdminSidebar;

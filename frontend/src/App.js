import React from 'react';
import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import Home from './pages/Home';
import LoginPage from './pages/LoginPage';
import RegisterPage from './pages/RegisterPage';
import './App.css';

import { AuthProvider } from './context/AuthContext';
import { ToastProvider } from './context/ToastContext';

import MovieDetails from './pages/MovieDetails';
import Movies from './pages/Movies';
import Cinemas from './pages/Cinemas';

import Events from './pages/Events';
import OfferDetails from './pages/OfferDetails';
import Booking from './pages/Booking';
import Payment from './pages/Payment';
import BookingSuccess from './pages/BookingSuccess';
import Profile from './pages/Profile';
import Sitemap from './pages/Sitemap';
import PrivateRoute from './components/PrivateRoute';
import AdminDashboard from './pages/Admin/AdminDashboard';
import ManageMovies from './pages/Admin/ManageMovies';
import ManageCinemas from './pages/Admin/ManageCinemas';
import ManageOffers from './pages/Admin/ManageOffers';
import ManageShowtimes from './pages/Admin/ManageShowtimes';
import ManageReviews from './pages/Admin/ManageReviews';
import ManageUsers from './pages/Admin/ManageUsers';
import ManageBookings from './pages/Admin/ManageBookings';
import ManagePayments from './pages/Admin/ManagePayments';
import AdminSettings from './pages/Admin/AdminSettings';
import AdminLayout from './components/Admin/AdminLayout';

import CustomerRoute from './components/CustomerRoute';
import { Navigate } from 'react-router-dom';
import OfferPopup from './components/OfferPopup';

function App() {
  return (
    <AuthProvider>
      <ToastProvider>
        <Router>
          <div className="App">
            <OfferPopup />
            <Routes>
              <Route path="/" element={<Home />} />
              <Route path="/login" element={<LoginPage />} />
              <Route path="/register" element={<RegisterPage />} />
              <Route path="/movies" element={<Movies />} />
              <Route path="/movies/:id" element={<MovieDetails />} />
              <Route path="/cinemas" element={<Cinemas />} />

              <Route path="/events" element={<Events />} />
              <Route path="/offers" element={<Events />} />
              <Route path="/offers/:id" element={<OfferDetails />} />
              <Route path="/offers/:id" element={<OfferDetails />} />
              <Route path="/promotion/:id" element={<OfferDetails />} />
              <Route path="/sitemap" element={<Sitemap />} />

              {/* Customer Routes (Protected from Admin) */}
              <Route element={<CustomerRoute />}>
                <Route path="/booking/movie/:movieId" element={<Booking />} />
                <Route path="/payment/:bookingId" element={<Payment />} />
                <Route path="/eticket/:bookingId" element={<BookingSuccess />} />
                <Route path="/profile" element={<PrivateRoute><Profile /></PrivateRoute>} />
              </Route>

              {/* Admin Routes */}
              <Route path="/admin" element={
                <PrivateRoute role="admin">
                  <AdminLayout />
                </PrivateRoute>
              }>
                <Route index element={<Navigate to="dashboard" replace />} />
                <Route path="dashboard" element={<AdminDashboard />} />
                <Route path="movies" element={<ManageMovies />} />
                <Route path="cinemas" element={<ManageCinemas />} />
                <Route path="offers" element={<ManageOffers />} />
                <Route path="showtimes" element={<ManageShowtimes />} />
                <Route path="reviews" element={<ManageReviews />} />
                <Route path="users" element={<ManageUsers />} />
                <Route path="bookings" element={<ManageBookings />} />
                <Route path="payments" element={<ManagePayments />} />
                <Route path="settings" element={<AdminSettings />} />
              </Route>

            </Routes>
          </div>

        </Router>
      </ToastProvider>
    </AuthProvider>
  );
}

export default App;

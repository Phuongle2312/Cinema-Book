import React from 'react';
import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import Home from './pages/Home';
import LoginPage from './pages/LoginPage';
import RegisterPage from './pages/RegisterPage';
import './App.css';

import { AuthProvider } from './context/AuthContext';
import { PopupProvider } from './context/PopupContext';

import MovieDetails from './pages/MovieDetails';
import Movies from './pages/Movies';
import Cinemas from './pages/Cinemas';
import Offers from './pages/Offers';
import Events from './pages/Events';
import PromotionDetails from './pages/PromotionDetails';
import Booking from './pages/Booking';
import Payment from './pages/Payment';
import BookingSuccess from './pages/BookingSuccess';
import Profile from './pages/Profile';
import PrivateRoute from './components/PrivateRoute';
import AdminLayout from './components/Admin/AdminLayout';
import AdminDashboard from './pages/Admin/AdminDashboard';
import AdminMovies from './pages/Admin/AdminMovies';
import AdminUsers from './pages/Admin/AdminUsers';
import AdminOffers from './pages/Admin/AdminOffers';
import AdminCinemas from './pages/Admin/AdminCinemas';
import AdminShowtimes from './pages/Admin/AdminShowtimes';
import AdminPayments from './pages/Admin/AdminPayments';
import AdminSettings from './pages/Admin/AdminSettings';

function App() {
  return (
    <AuthProvider>
      <PopupProvider>
        <Router>
          <div className="App">
            <Routes>
              <Route path="/" element={<Home />} />
              <Route path="/login" element={<LoginPage />} />
              <Route path="/register" element={<RegisterPage />} />
              <Route path="/movies" element={<Movies />} />
              <Route path="/movies/:slug" element={<MovieDetails />} />
              <Route path="/cinemas" element={<Cinemas />} />
              <Route path="/offers" element={<Offers />} />
              <Route path="/events" element={<Events />} />
              <Route path="/promotion/:id" element={<PromotionDetails />} />

              <Route path="/booking/movie/:slug" element={<Booking />} />
              <Route path="/payment/:bookingId" element={<Payment />} />
              <Route path="/eticket/:bookingId" element={<BookingSuccess />} />
              <Route path="/profile" element={<PrivateRoute><Profile /></PrivateRoute>} />
              <Route path="/user/profile" element={<PrivateRoute><Profile /></PrivateRoute>} />
              <Route path="/user/bookings" element={<PrivateRoute><Profile /></PrivateRoute>} />

              {/* Admin Routes */}
              <Route path="/admin" element={<AdminLayout />}>
                <Route index element={<AdminDashboard />} />
                <Route path="dashboard" element={<AdminDashboard />} />
                <Route path="movies" element={<AdminMovies />} />
                <Route path="users" element={<AdminUsers />} />
                <Route path="offers" element={<AdminOffers />} />
                <Route path="cinemas" element={<AdminCinemas />} />
                <Route path="showtimes" element={<AdminShowtimes />} />
                <Route path="payments" element={<AdminPayments />} />
                <Route path="settings" element={<AdminSettings />} />
              </Route>
            </Routes>
          </div>
        </Router>
      </PopupProvider>
    </AuthProvider>
  );
}

export default App;

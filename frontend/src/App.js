import React from 'react';
import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import Home from './pages/Home';
import LoginPage from './pages/LoginPage';
import RegisterPage from './pages/RegisterPage';
import './App.css';

import { AuthProvider } from './context/AuthContext';

import MovieDetails from './pages/MovieDetails';
import Movies from './pages/Movies';
import Cinemas from './pages/Cinemas';
import Offers from './pages/Offers';
import Events from './pages/Events';
import OfferDetails from './pages/OfferDetails';
import Booking from './pages/Booking';
import Payment from './pages/Payment';
import BookingSuccess from './pages/BookingSuccess';
import Profile from './pages/Profile';
import PrivateRoute from './components/PrivateRoute';

function App() {
  return (
    <AuthProvider>
      <Router>
        <div className="App">
          <Routes>
            <Route path="/" element={<Home />} />
            <Route path="/login" element={<LoginPage />} />
            <Route path="/register" element={<RegisterPage />} />
            <Route path="/movies" element={<Movies />} />
            <Route path="/movies/:id" element={<MovieDetails />} />
            <Route path="/cinemas" element={<Cinemas />} />
            <Route path="/offers" element={<Offers />} />
            <Route path="/events" element={<Events />} />
            <Route path="/offers/:id" element={<OfferDetails />} />

            <Route path="/booking/movie/:movieId" element={<Booking />} />
            <Route path="/payment/:bookingId" element={<Payment />} />
            <Route path="/eticket/:bookingId" element={<BookingSuccess />} />
            <Route path="/profile" element={<PrivateRoute><Profile /></PrivateRoute>} />

          </Routes>
        </div>
      </Router>
    </AuthProvider>
  );
}

export default App;

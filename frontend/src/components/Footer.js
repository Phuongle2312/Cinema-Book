import React from 'react';
import './Footer.css';
import { Facebook, Instagram, Youtube, Twitter, Mail, Phone, MapPin, Popcorn } from 'lucide-react';

const Footer = () => {
    return (
        <footer className="footer">
            <div className="footer-container">
                {/* About Section */}
                <div className="footer-section">
                    <div className="footer-logo">
                        <Popcorn className="logo-icon" />
                        <h3 className="footer-title">CINE<span>BOOK</span></h3>
                    </div>
                    <p className="footer-description">
                        Experience the magic of cinema with premium screens,
                        luxury seating, and cutting-edge technology.
                        Your ultimate destination for entertainment.
                    </p>
                    <div className="social-icons">
                        <a href="https://facebook.com" target="_blank" rel="noopener noreferrer" className="social-icon">
                            <Facebook size={20} />
                        </a>
                        <a href="https://instagram.com" target="_blank" rel="noopener noreferrer" className="social-icon">
                            <Instagram size={20} />
                        </a>
                        <a href="https://youtube.com" target="_blank" rel="noopener noreferrer" className="social-icon">
                            <Youtube size={20} />
                        </a>
                        <a href="https://twitter.com" target="_blank" rel="noopener noreferrer" className="social-icon">
                            <Twitter size={20} />
                        </a>
                    </div>
                </div>

                {/* Quick Links */}
                <div className="footer-section">
                    <h4 className="footer-subtitle">Quick Links</h4>
                    <ul className="footer-links">
                        <li><a href="/">Home</a></li>
                        <li><a href="/movies">Now Showing</a></li>
                        <li><a href="/coming-soon">Coming Soon</a></li>
                        <li><a href="/membership">Membership</a></li>
                        <li><a href="/promotions">Promotions</a></li>
                    </ul>
                </div>

                {/* Support */}
                <div className="footer-section">
                    <h4 className="footer-subtitle">Support</h4>
                    <ul className="footer-links">
                        <li><a href="/faq">FAQ</a></li>
                        <li><a href="/terms">Terms & Conditions</a></li>
                        <li><a href="/privacy">Privacy Policy</a></li>
                        <li><a href="/booking-guide">Booking Guide</a></li>
                        <li><a href="/booking-guide">Booking Guide</a></li>
                        <li><a href="/refund">Refund Policy</a></li>
                        <li><a href="/sitemap">Sitemap</a></li>
                    </ul>
                </div>

                {/* Contact Info */}
                <div className="footer-section">
                    <h4 className="footer-subtitle">Contact Us</h4>
                    <div className="contact-info">
                        <div className="contact-item">
                            <MapPin size={18} />
                            <span>123 Cinema Street, District 1, Ha Noi City</span>
                        </div>
                        <div className="contact-item">
                            <Phone size={18} />
                            <span>1900-6017</span>
                        </div>
                        <div className="contact-item">
                            <Mail size={18} />
                            <span>support@cinebook.com</span>
                        </div>
                    </div>
                </div>
            </div>

            {/* Bottom Bar */}
            <div className="footer-bottom">
                <div className="footer-bottom-container">
                    <p>&copy; 2025 CINEBOOK. All rights reserved.</p>
                    <div className="payment-methods">
                        <span>We accept:</span>
                        <div className="payment-icons">
                            <span className="payment-badge">VISA</span>
                            <span className="payment-badge">MasterCard</span>
                            <span className="payment-badge">Momo</span>
                            <span className="payment-badge">ZaloPay</span>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
    );
};

export default Footer;

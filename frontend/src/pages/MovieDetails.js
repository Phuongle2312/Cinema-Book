import React, { useState, useEffect } from 'react';
import { useParams, useNavigate, Link } from 'react-router-dom';
import { Star, Clock, Calendar, Globe, Play, ChevronLeft, User, Heart, MapPin, Info } from 'lucide-react';
import movieService from '../services/movieService';
import wishlistService from '../services/wishlistService';
import { getYouTubeEmbedUrl } from '../utils/videoUtils';
import { useAuth } from '../context/AuthContext';
import Navbar from '../components/Navbar';
import Footer from '../components/Footer';
import './MovieDetails.css';

const MovieDetails = () => {
    const { id } = useParams(); // This can be slug or ID
    const navigate = useNavigate();
    const { isAuthenticated } = useAuth();

    const [movie, setMovie] = useState(null);
    const [isFavorite, setIsFavorite] = useState(false);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [wishlistLoading, setWishlistLoading] = useState(false);

    useEffect(() => {
        const fetchMovie = async () => {
            try {
                setLoading(true);
                const data = await movieService.getMovieById(id);
                if (data.success) {
                    const movieData = data.data;
                    setMovie(movieData);

                    // Check if in wishlist if logged in
                    if (isAuthenticated && movieData.movie_id) {
                        const wishStatus = await wishlistService.checkWishlist(movieData.movie_id);
                        setIsFavorite(wishStatus.is_favorite);
                    }
                } else {
                    setError(data.message || "Movie not found.");
                }
            } catch (err) {
                console.error("Failed to fetch movie details:", err);
                setError("Could not load movie details.");
            } finally {
                setLoading(false);
            }
        };

        fetchMovie();
    }, [id, isAuthenticated]);

    const handleToggleWishlist = async () => {
        if (!isAuthenticated) {
            alert("vui lòng đăng nhập để sử dụng tính năng này");
            navigate('/login');
            return;
        }

        try {
            setWishlistLoading(true);
            const result = await wishlistService.toggleWishlist(movie.movie_id);
            if (result.success) {
                setIsFavorite(result.is_favorite);
            }
        } catch (err) {
            console.error("Error toggling wishlist:", err);
        } finally {
            setWishlistLoading(false);
        }
    };

    if (loading) {
        return (
            <div className="movie-details-loading bg-[#0a0a0a] min-h-screen flex flex-col">
                <Navbar />
                <div className="flex-1 flex flex-col items-center justify-center">
                    <div className="w-12 h-12 border-4 border-red-600 border-t-transparent rounded-full animate-spin"></div>
                    <p className="mt-4 text-gray-400">Loading movie details...</p>
                </div>
                <Footer />
            </div>
        );
    }

    if (error || !movie) {
        return (
            <div className="movie-details-error bg-[#0a0a0a] min-h-screen">
                <Navbar />
                <div className="container py-40 text-center">
                    <h2 className="text-4xl font-bold text-white mb-4">Mất tích rồi!</h2>
                    <p className="text-gray-400 mb-8">{error || "Không tìm thấy phim này trong hệ thống."}</p>
                    <button onClick={() => navigate('/')} className="px-6 py-3 bg-red-600 text-white rounded-lg flex items-center gap-2 mx-auto">
                        <ChevronLeft size={20} />
                        Quay lại trang chủ
                    </button>
                </div>
                <Footer />
            </div>
        );
    }

    // Filter actors and directors
    const directors = (movie.cast || []).filter(c => c.pivot?.role === 'director' || c.role === 'director');
    const actors = (movie.cast || []).filter(c => c.pivot?.role === 'actor' || c.role === 'actor');

    return (
        <div className="movie-details-page bg-[#0a0a0a] text-white">
            <Navbar />

            {/* Hero Section */}
            <div className="relative h-[80vh] w-full overflow-hidden">
                <div className="absolute inset-0">
                    <img
                        src={movie.banner_url || movie.poster_url}
                        alt={movie.title}
                        className="w-full h-full object-cover opacity-30 scale-105 blur-sm"
                    />
                    <div className="absolute inset-0 bg-gradient-to-t from-[#0a0a0a] via-transparent to-transparent"></div>
                    <div className="absolute inset-0 bg-gradient-to-r from-[#0a0a0a] via-transparent to-transparent"></div>
                </div>

                <div className="container relative h-full flex items-end pb-12">
                    <div className="flex flex-col md:flex-row gap-8 items-center md:items-end">
                        <div className="w-64 md:w-72 rounded-xl overflow-hidden shadow-2xl shadow-red-900/20 border border-white/10 shrink-0">
                            <img src={movie.poster_url} alt={movie.title} className="w-full h-auto" />
                        </div>

                        <div className="flex-1 text-center md:text-left">
                            <div className="flex flex-wrap gap-2 mb-4 justify-center md:justify-start">
                                {movie.genres?.map(genre => (
                                    <span key={genre.genre_id} className="px-3 py-1 bg-white/10 backdrop-blur-md rounded-full text-xs font-semibold border border-white/10">
                                        {genre.name}
                                    </span>
                                ))}
                            </div>

                            <h1 className="text-4xl md:text-6xl font-black mb-4 tracking-tight">{movie.title}</h1>

                            <div className="flex flex-wrap items-center gap-6 text-sm text-gray-300 mb-8 justify-center md:justify-start">
                                <div className="flex items-center gap-2">
                                    <Clock size={18} className="text-red-600" />
                                    <span>{movie.duration} phút</span>
                                </div>
                                <div className="flex items-center gap-2">
                                    <Calendar size={18} className="text-red-600" />
                                    <span>{new Date(movie.release_date).getFullYear()}</span>
                                </div>
                                <div className="flex items-center gap-2">
                                    <Globe size={18} className="text-red-600" />
                                    <span>{movie.languages?.[0]?.name || 'Phụ đề'}</span>
                                </div>
                            </div>

                            <div className="flex flex-wrap gap-4 justify-center md:justify-start">
                                <Link
                                    to={`/booking/movie/${movie.movie_id}`}
                                    className="px-8 py-4 bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl transition-all hover:scale-105 active:scale-95 shadow-lg shadow-red-600/30"
                                >
                                    ĐẶT VÉ NGAY
                                </Link>
                                <button
                                    onClick={handleToggleWishlist}
                                    disabled={wishlistLoading}
                                    className={`p-4 rounded-xl border transition-all ${isFavorite ? 'bg-red-600/20 border-red-600 text-red-600' : 'bg-white/5 border-white/10 text-white hover:bg-white/10'}`}
                                >
                                    <Heart size={24} fill={isFavorite ? "currentColor" : "none"} />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div className="container py-12">
                <div className="grid grid-cols-1 lg:grid-cols-3 gap-12">
                    {/* Main Content */}
                    <div className="lg:col-span-2 space-y-12">
                        {/* Synopsis */}
                        <section>
                            <h2 className="text-2xl font-bold mb-6 flex items-center gap-3">
                                <span className="w-1 h-8 bg-red-600 rounded-full"></span>
                                Nội dung phim
                            </h2>
                            <p className="text-gray-400 leading-relaxed text-lg">
                                {movie.description || movie.content || "Đang cập nhật nội dung..."}
                            </p>
                            {movie.content && movie.description && movie.content !== movie.description && (
                                <p className="mt-4 text-gray-400 leading-relaxed">
                                    {movie.content}
                                </p>
                            )}
                        </section>

                        {/* Trailer */}
                        {movie.trailer_url && (
                            <section>
                                <h2 className="text-2xl font-bold mb-6 flex items-center gap-3">
                                    <span className="w-1 h-8 bg-red-600 rounded-full"></span>
                                    Trailer
                                </h2>
                                <div className="aspect-video w-full rounded-2xl overflow-hidden border border-white/10 shadow-2xl">
                                    <iframe
                                        src={getYouTubeEmbedUrl(movie.trailer_url)}
                                        title="Trailer"
                                        className="w-full h-full"
                                        allowFullScreen
                                    ></iframe>
                                </div>
                            </section>
                        )}
                    </div>

                    {/* Sidebar */}
                    <div className="space-y-12">
                        {/* Director & Cast */}
                        <section>
                            <h2 className="text-2xl font-bold mb-6 flex items-center gap-3">
                                <span className="w-1 h-8 bg-red-600 rounded-full"></span>
                                Đạo diễn & Diễn viên
                            </h2>

                            <div className="space-y-6">
                                {directors.length > 0 && (
                                    <div>
                                        <h3 className="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Đạo diễn</h3>
                                        {directors.map((p, i) => (
                                            <div key={i} className="flex items-center gap-4 bg-white/5 p-3 rounded-xl border border-white/5">
                                                <div className="w-12 h-12 rounded-full overflow-hidden bg-white/10 flex items-center justify-center">
                                                    {p.image_url ? <img src={p.image_url} alt={p.name} /> : <User size={20} />}
                                                </div>
                                                <span className="font-medium">{p.name}</span>
                                            </div>
                                        ))}
                                    </div>
                                )}

                                {actors.length > 0 && (
                                    <div>
                                        <h3 className="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Diễn viên</h3>
                                        <div className="grid grid-cols-1 gap-3">
                                            {actors.map((p, i) => (
                                                <div key={i} className="flex items-center gap-4 bg-white/5 p-3 rounded-xl border border-white/5">
                                                    <div className="w-12 h-12 rounded-full overflow-hidden bg-white/10 flex items-center justify-center">
                                                        {p.image_url ? <img src={p.image_url} alt={p.name} /> : <User size={20} />}
                                                    </div>
                                                    <div>
                                                        <div className="font-medium">{p.name}</div>
                                                        {p.pivot?.character_name && <div className="text-xs text-gray-500 italic">{p.pivot.character_name}</div>}
                                                    </div>
                                                </div>
                                            ))}
                                        </div>
                                    </div>
                                )}
                            </div>
                        </section>

                        {/* Wishlist Info */}
                        <section className="bg-red-600/10 border border-red-600/20 p-6 rounded-2xl text-center">
                            <Heart size={40} className="mx-auto text-red-600 mb-4" fill={isFavorite ? "currentColor" : "none"} />
                            <h3 className="font-bold mb-2">{isFavorite ? "Đã trong danh sách yêu thích" : "Yêu thích phim này?"}</h3>
                            <p className="text-sm text-gray-400 mb-4">
                                {isFavorite ? "Phim đã được lưu vào danh sách xem sau của bạn." : "Thêm vào danh sách yêu thích để nhận thông báo về suất chiếu mới."}
                            </p>
                            <button
                                onClick={handleToggleWishlist}
                                className={`w-full py-2 rounded-lg font-bold transition-all ${isFavorite ? 'bg-white/10 text-white' : 'bg-red-600 text-white'}`}
                            >
                                {isFavorite ? "Xóa khỏi yêu thích" : "Thêm vào yêu thích"}
                            </button>
                        </section>
                    </div>
                </div>
            </div>

            <Footer />
        </div>
    );
};

export default MovieDetails;

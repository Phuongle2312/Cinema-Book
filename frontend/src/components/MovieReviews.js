import React, { useState, useEffect } from 'react';
import { Star, User, MessageCircle, Send, AlertCircle } from 'lucide-react';
import { useAuth } from '../context/AuthContext';
import { useToast } from '../context/ToastContext';
import api from '../services/api';
import './MovieReviews.css';

const MovieReviews = ({ movieId }) => {
    const { isAuthenticated } = useAuth();
    const toast = useToast();

    // State
    const [reviews, setReviews] = useState([]);
    const [stats, setStats] = useState(null);
    const [userStatus, setUserStatus] = useState(null);
    const [loading, setLoading] = useState(true);
    const [page, setPage] = useState(1);
    const [hasMore, setHasMore] = useState(false);

    // Form State
    const [rating, setRating] = useState(5);
    const [comment, setComment] = useState('');
    const [submitting, setSubmitting] = useState(false);
    const [permissionError, setPermissionError] = useState(null);

    useEffect(() => {
        fetchReviews();
    }, [movieId]);

    const fetchReviews = async () => {
        try {
            setLoading(true);
            const res = await api.get(`/movies/${movieId}/reviews?page=${page}`);
            if (res.data.success) {
                const newReviews = res.data.data.data;
                setReviews(prev => page === 1 ? newReviews : [...prev, ...newReviews]);
                setStats(res.data.stats);
                if (res.data.user_status) {
                    setUserStatus(res.data.user_status);
                }
                setHasMore(res.data.data.next_page_url !== null);
            }
        } catch (err) {
            console.error("Failed to fetch reviews", err);
        } finally {
            setLoading(false);
        }
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        if (!isAuthenticated) {
            toast.info("Vui lòng đăng nhập để đánh giá.");
            return;
        }

        if (!comment.trim()) {
            toast.warning("Vui lòng nhập nội dung đánh giá.");
            return;
        }

        try {
            setSubmitting(true);
            setPermissionError(null);

            const res = await api.post(`/movies/${movieId}/reviews`, {
                rating,
                comment
            });

            if (res.data.success) {
                toast.success(res.data.message);
                setComment('');
                setRating(5);
                fetchReviews(); // Refresh list
            }
        } catch (err) {
            const errorMsg = err.response?.data?.message || "Gửi đánh giá thất bại.";
            toast.error(errorMsg);
        } finally {
            setSubmitting(false);
        }
    };

    const StarRating = ({ value, onChange, readOnly = false }) => {
        return (
            <div className="flex gap-1">
                {[1, 2, 3, 4, 5].map((star) => (
                    <button
                        key={star}
                        type="button"
                        disabled={readOnly}
                        onClick={() => !readOnly && onChange(star)}
                        className={`transition-all star-rating-btn ${readOnly ? 'cursor-default' : ''}`}
                    >
                        <Star
                            size={readOnly ? 16 : 24}
                            fill={star <= value ? "#facc15" : "none"}
                            color={star <= value ? "#facc15" : "#4b5563"}
                            className="transition-colors"
                        />
                    </button>
                ))}
            </div>
        );
    };

    if (loading && reviews.length === 0) return <div className="p-8 text-center text-gray-500">Đang tải đánh giá...</div>;

    return (
        <div className="reviews-section w-full">
            <h3 className="section-title-reviews">
                <MessageCircle className="text-red-600" />
                Reviews & Comments
                {stats && <span className="text-sm font-normal text-gray-400">({stats.total_reviews} reviews)</span>}
            </h3>

            {/* Stats Overview */}
            {stats && (
                <div className="stats-card mb-8 flex flex-col md:flex-row items-center gap-8">
                    <div className="text-center md:text-left">
                        <div className="text-5xl font-black text-yellow-400 mb-2">
                            {stats.average_rating ? parseFloat(stats.average_rating).toFixed(1) : "0.0"}
                        </div>
                        <StarRating value={Math.round(stats.average_rating || 0)} readOnly />
                    </div>
                </div>
            )}

            {/* Review Form */}
            {/* Review Form Condition Logic */}
            {isAuthenticated ? (
                <div className="review-form-card mb-10">
                    <h4 className="review-form-title">Write your review</h4>

                    {/* Show error only if submission failed due to other reasons */}

                    {permissionError && (
                        <div className="mb-4 p-4 bg-red-500/10 border border-red-500/30 rounded-lg flex items-center gap-3 text-red-400">
                            <AlertCircle size={20} />
                            <span>{permissionError}</span>
                        </div>
                    )}

                    <form onSubmit={handleSubmit}>
                        <div className="mb-4">
                            <label className="block text-sm text-gray-400 mb-2">Your rating</label>
                            <StarRating value={rating} onChange={setRating} readOnly={false} />
                        </div>

                        <div className="mb-4">
                            <textarea
                                value={comment}
                                onChange={(e) => setComment(e.target.value)}
                                placeholder="Share your thoughts on this movie..."
                                className="review-textarea"
                                disabled={false}
                            />
                        </div>

                        <div className="flex justify-end">
                            <button
                                type="submit"
                                disabled={submitting}
                                className={`btn-submit-review`}
                            >
                                {submitting ? 'Submitting...' : (
                                    <>
                                        <Send size={18} /> Submit Review
                                    </>
                                )}
                            </button>
                        </div>
                    </form>
                </div>
            ) : (
                <div className="login-prompt mb-10 text-center">
                    <p className="text-gray-400">Please <span className="text-red-500 font-bold cursor-pointer">login</span> to write a review.</p>
                </div>
            )}

            {/* Reviews List */}
            <div className="reviews-list space-y-6">
                {reviews.map(review => (
                    <div key={review.review_id} className="review-item">
                        <div className="flex items-start justify-between mb-4">
                            <div className="flex items-center gap-3">
                                <div className="avatar-circle">
                                    {review.user?.avatar ? (
                                        <img src={review.user.avatar} alt={review.user.name} className="w-full h-full object-cover" />
                                    ) : (
                                        <User size={24} className="text-gray-400" />
                                    )}
                                </div>
                                <div>
                                    <div className="user-name-review">{review.user?.name || 'Anonymous User'}</div>
                                    <div className="review-date">{new Date(review.created_at).toLocaleDateString('en-GB')}</div>
                                </div>
                            </div>
                            <StarRating value={review.rating} readOnly />
                        </div>
                        <p className="review-content">{review.comment}</p>
                        {review.is_verified_purchase && (
                            <div className="verified-badge">
                                <div className="w-4 h-4 rounded-full bg-green-500/20 flex items-center justify-center border border-green-500/30">✓</div>
                                Verified Purchase
                            </div>
                        )}
                    </div>
                ))}

                {reviews.length === 0 && (
                    <div className="text-center text-gray-500 py-10">
                        No reviews yet. Be the first!
                    </div>
                )}
            </div>
        </div>
    );
};

export default MovieReviews;

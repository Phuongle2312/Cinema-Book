import React, { useState, useEffect, useMemo } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { Clock, CreditCard, ShieldCheck, TicketPercent, CheckCircle2, ChevronRight, MapPin, Calendar, Info } from 'lucide-react';
import Navbar from '../components/Navbar';
import Footer from '../components/Footer';
import bookingService from '../services/bookingService';
import offerService from '../services/offerService';
import './Payment.css';

const Payment = () => {
    const { bookingId } = useParams();
    const navigate = useNavigate();

    const [booking, setBooking] = useState(null);
    const [timeLeft, setTimeLeft] = useState(0);
    const [paymentMethod, setPaymentMethod] = useState('credit_card');
    const [loading, setLoading] = useState(true);
    const [processing, setProcessing] = useState(false);
    const [error, setError] = useState(null);

    // Offers State
    const [systemOffers, setSystemOffers] = useState([]);
    const [appliedOffers, setAppliedOffers] = useState([]);
    const [totalDiscount, setTotalDiscount] = useState(0);

    useEffect(() => {
        const fetchData = async () => {
            try {
                setLoading(true);
                const [bookingRes, offersRes] = await Promise.all([
                    bookingService.getBookingById(bookingId),
                    offerService.getSystemOffers()
                ]);

                if (bookingRes.success) {
                    setBooking(bookingRes.data);

                    // Timer logic
                    if (bookingRes.data.expires_at) {
                        const diff = Math.floor((new Date(bookingRes.data.expires_at).getTime() - new Date().getTime()) / 1000);
                        setTimeLeft(diff > 0 ? diff : 0);
                    }

                    // Auto-apply system offers
                    const offers = offersRes.data || [];
                    setSystemOffers(offers);

                    let discount = 0;
                    const applied = [];
                    const currentTotal = parseFloat(bookingRes.data.total_price);

                    offers.forEach(offer => {
                        // Check min purchase
                        if (!offer.min_purchase_amount || currentTotal >= parseFloat(offer.min_purchase_amount)) {
                            let offAmt = 0;
                            if (offer.discount_type === 'percentage') {
                                offAmt = currentTotal * (parseFloat(offer.discount_value) / 100);
                                if (offer.max_discount_amount && offAmt > parseFloat(offer.max_discount_amount)) {
                                    offAmt = parseFloat(offer.max_discount_amount);
                                }
                            } else {
                                offAmt = Math.min(parseFloat(offer.discount_value), currentTotal - discount);
                            }

                            if (offAmt > 0) {
                                discount += offAmt;
                                applied.push(offer);
                            }
                        }
                    });

                    setAppliedOffers(applied);
                    setTotalDiscount(discount);
                }
            } catch (err) {
                setError("Không thể tải thông tin thanh toán.");
            } finally {
                setLoading(false);
            }
        };
        fetchData();
    }, [bookingId]);

    useEffect(() => {
        if (timeLeft <= 0) return;
        const interval = setInterval(() => {
            setTimeLeft(prev => prev > 0 ? prev - 1 : 0);
        }, 1000);
        return () => clearInterval(interval);
    }, [timeLeft]);

    const formatTime = (seconds) => {
        const min = Math.floor(seconds / 60);
        const sec = seconds % 60;
        return `${min}:${sec < 10 ? '0' : ''}${sec}`;
    };

    const handlePayment = async () => {
        if (timeLeft === 0) {
            alert("Giao dịch đã hết hạn.");
            navigate('/');
            return;
        }

        setProcessing(true);
        try {
            const result = await bookingService.processPayment(bookingId, { payment_method: paymentMethod });
            if (result.success) {
                navigate(`/eticket/${bookingId}`);
            } else {
                alert(result.message || "Thanh toán thất bại");
            }
        } catch (err) {
            alert("Lỗi hệ thống khi thanh toán");
        } finally {
            setProcessing(false);
        }
    };

    const finalPrice = useMemo(() => {
        if (!booking) return 0;
        return Math.max(0, parseFloat(booking.total_price) - totalDiscount);
    }, [booking, totalDiscount]);

    if (loading) return (
        <div className="payment-page bg-[#0a0a0a] min-h-screen flex items-center justify-center">
            <Navbar />
            <div className="text-center">
                <div className="w-12 h-12 border-4 border-red-600 border-t-transparent rounded-full animate-spin mx-auto mb-4"></div>
                <p className="text-gray-400">Đang khởi tạo giao dịch...</p>
            </div>
        </div>
    );

    if (error || !booking) return (
        <div className="payment-page bg-[#0a0a0a] min-h-screen flex items-center justify-center p-20">
            <Navbar />
            <div className="text-center">
                <Info size={48} className="text-red-600 mx-auto mb-4" />
                <h2 className="text-2xl font-bold mb-4">{error || "Không tìm thấy thông tin đặt vé."}</h2>
                <button onClick={() => navigate('/')} className="px-8 py-3 bg-red-600 rounded-xl font-bold">Về trang chủ</button>
            </div>
        </div>
    );

    return (
        <div className="payment-page bg-[#0a0a0a] min-h-screen text-white">
            <Navbar />

            <div className="container pt-32 pb-24">
                <div className="max-w-6xl mx-auto">
                    <div className="flex flex-col md:flex-row gap-4 justify-between items-center mb-12">
                        <h1 className="text-4xl font-black uppercase tracking-tight">Thanh toán</h1>
                        <div className={`flex items-center gap-3 px-6 py-3 rounded-2xl border ${timeLeft < 60 ? 'bg-red-600/20 border-red-600 text-red-600' : 'bg-white/5 border-white/10 text-gray-400'}`}>
                            <Clock size={20} />
                            <span className="font-bold">Hết hạn sau: {formatTime(timeLeft)}</span>
                        </div>
                    </div>

                    <div className="grid grid-cols-1 lg:grid-cols-3 gap-10">
                        {/* Order Summary */}
                        <div className="lg:col-span-2 space-y-8">
                            <div className="bg-[#141414] border border-white/5 rounded-3xl overflow-hidden p-8">
                                <h2 className="text-xl font-bold mb-8 flex items-center gap-2">
                                    <div className="w-2 h-6 bg-red-600 rounded-full"></div>
                                    Thông tin vé
                                </h2>

                                <div className="flex flex-col md:flex-row gap-8">
                                    <img
                                        src={booking.showtime?.movie?.poster_url}
                                        className="w-full md:w-40 aspect-[2/3] object-cover rounded-2xl shadow-2xl"
                                        alt="Poster"
                                    />
                                    <div className="flex-1 space-y-4">
                                        <h3 className="text-2xl font-black uppercase">{booking.showtime?.movie?.title}</h3>
                                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div className="flex items-center gap-3 text-gray-400">
                                                <MapPin size={18} className="text-red-600" />
                                                <span>{booking.showtime?.room?.theater?.name}</span>
                                            </div>
                                            <div className="flex items-center gap-3 text-gray-400">
                                                <Calendar size={18} className="text-red-600" />
                                                <span>{new Date(booking.showtime?.start_time).toLocaleDateString('vi-VN')}</span>
                                            </div>
                                            <div className="flex items-center gap-3 text-gray-400">
                                                <div className="w-4 h-4 rounded-sm border border-red-600"></div>
                                                <span>Phòng: {booking.showtime?.room?.name}</span>
                                            </div>
                                            <div className="flex items-center gap-3 text-gray-400">
                                                <Clock size={18} className="text-red-600" />
                                                <span>{booking.showtime?.start_time?.split(' ')[1]?.substring(0, 5)}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div className="mt-12 space-y-4 pt-8 border-t border-white/5">
                                    <div className="flex justify-between text-gray-400">
                                        <span>Ghế ({booking.seats?.length}): {booking.seats?.map(s => `${s.row}${s.number}`).join(', ')}</span>
                                        <span className="font-bold text-white">{parseFloat(booking.seats_total).toLocaleString()} đ</span>
                                    </div>
                                    {booking.combo_total > 0 && (
                                        <div className="flex justify-between text-gray-400">
                                            <span>Combo</span>
                                            <span className="font-bold text-white">{parseFloat(booking.combo_total).toLocaleString()} đ</span>
                                        </div>
                                    )}
                                    <div className="flex justify-between items-center text-xl font-bold pt-4">
                                        <span>Tạm tính</span>
                                        <span className="text-red-600">{parseFloat(booking.total_price).toLocaleString()} đ</span>
                                    </div>
                                </div>
                            </div>

                            {/* Applied Offers Section */}
                            <div className="bg-red-600/5 border border-red-600/20 rounded-3xl p-8">
                                <h2 className="text-xl font-bold mb-6 flex items-center gap-2 text-red-500">
                                    <TicketPercent size={24} />
                                    Ưu đãi được áp dụng
                                </h2>

                                {appliedOffers.length > 0 ? (
                                    <div className="space-y-4">
                                        {appliedOffers.map(offer => (
                                            <div key={offer.id} className="flex items-center justify-between p-4 bg-white/5 rounded-2xl border border-white/5">
                                                <div className="flex items-center gap-4">
                                                    <div className="w-10 h-10 bg-red-600 rounded-xl flex items-center justify-center">
                                                        <CheckCircle2 size={20} className="text-white" />
                                                    </div>
                                                    <div>
                                                        <p className="font-bold">{offer.description}</p>
                                                        <p className="text-xs text-gray-500">Ưu đãi hệ thống - Tự động áp dụng</p>
                                                    </div>
                                                </div>
                                                {offer.discount_type === 'percentage' && (
                                                    <span className="text-red-500 font-bold">-{offer.discount_value}%</span>
                                                )}
                                            </div>
                                        ))}
                                        <div className="flex justify-between items-center pt-4 text-green-500 font-bold">
                                            <span>Tổng cộng giảm giá:</span>
                                            <span>-{totalDiscount.toLocaleString()} đ</span>
                                        </div>
                                    </div>
                                ) : (
                                    <p className="text-gray-500">Hiện tại không có ưu đãi nào phù hợp với đơn hàng của bạn.</p>
                                )}
                            </div>
                        </div>

                        {/* Payment Selection */}
                        <div className="space-y-8">
                            <div className="bg-[#141414] border border-white/5 rounded-3xl p-8">
                                <h2 className="text-xl font-bold mb-8">Phương thức thanh toán</h2>
                                <div className="space-y-3">
                                    {[
                                        { id: 'credit_card', name: 'Thẻ tín dụng', icon: <CreditCard size={20} /> },
                                        { id: 'momo', name: 'Ví MoMo', img: 'https://img.mservice.com.vn/app/img/helper/logo-momo.png' },
                                        { id: 'zalopay', name: 'ZaloPay', img: 'https://cdn.haitrieu.com/wp-content/uploads/2022/10/Logo-ZaloPay-Square.png' }
                                    ].map(method => (
                                        <div
                                            key={method.id}
                                            className={`flex items-center justify-between p-4 rounded-2xl cursor-pointer border transition-all ${paymentMethod === method.id ? 'bg-red-600/10 border-red-600' : 'bg-white/5 border-transparent hover:bg-white/10'}`}
                                            onClick={() => setPaymentMethod(method.id)}
                                        >
                                            <div className="flex items-center gap-4">
                                                {method.img ? <img src={method.img} className="w-6 h-6 object-contain" alt="" /> : method.icon}
                                                <span className="font-bold">{method.name}</span>
                                            </div>
                                            {paymentMethod === method.id && <CheckCircle2 size={18} className="text-red-600" />}
                                        </div>
                                    ))}
                                </div>
                            </div>

                            <div className="bg-[#141414] border border-white/5 rounded-3xl p-8 space-y-6">
                                <div className="flex justify-between items-center">
                                    <span className="text-gray-400">Số tiền cần trả</span>
                                    <span className="text-3xl font-black text-red-600">{finalPrice.toLocaleString()} đ</span>
                                </div>
                                <button
                                    className="w-full py-4 bg-red-600 hover:bg-red-700 rounded-2xl font-black text-lg transition-all shadow-xl shadow-red-600/20 active:scale-95 disabled:opacity-50"
                                    onClick={handlePayment}
                                    disabled={processing || timeLeft === 0}
                                >
                                    {processing ? 'ĐANG XỬ LÝ...' : 'THANH TOÁN NGAY'}
                                </button>
                                <div className="flex items-center justify-center gap-2 text-xs text-gray-500">
                                    <ShieldCheck size={14} />
                                    <span>Giao dịch an toàn và bảo mật</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <Footer />
        </div>
    );
};

export default Payment;

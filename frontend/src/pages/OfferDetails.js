import React, { useEffect, useState } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import Navbar from '../components/Navbar';
import Footer from '../components/Footer';
import eventsData from '../data/events.json';
import { Calendar, Tag, ArrowLeft, Share2 } from 'lucide-react';
import './Promotions.css'; // Updated to use Promotions.css or similar

const OfferDetails = () => {
    const { id } = useParams();
    const navigate = useNavigate();
    const [offer, setOffer] = useState(null);
    const [actionState, setActionState] = useState({ loading: false, completed: false });

    const handleAction = () => {
        setActionState({ loading: true, completed: false });

        // Simulate API call
        setTimeout(() => {
            setActionState({ loading: false, completed: true });

            if (offer.type === 'offer') {
                alert(`Ưu đãi đã được lưu!\n\nHệ thống sẽ tự động áp dụng khi bạn thanh toán.`);
            } else {
                alert("Đăng ký thành công!\n\nChúng tôi đã gửi email xác nhận cho bạn.");
            }
        }, 1500);
    };

    useEffect(() => {
        // Find the offer by ID
        const found = eventsData.find(item => item.id === parseInt(id));
        if (found) {
            setOffer(found);
        } else {
            navigate('/offers');
        }
        window.scrollTo(0, 0);
    }, [id, navigate]);

    if (!offer) return null;

    return (
        <div className="promotion-details-page bg-[#0a0a0a] text-white min-h-screen">
            <Navbar />

            <div className="relative h-[50vh] w-full overflow-hidden">
                <img src={offer.image} alt={offer.title} className="w-full h-full object-cover opacity-50" />
                <div className="absolute inset-0 bg-gradient-to-t from-[#0a0a0a] to-transparent"></div>
                <div className="container relative h-full flex flex-col justify-end pb-12">
                    <button className="flex items-center gap-2 text-gray-400 hover:text-white mb-6 transition-colors" onClick={() => navigate(-1)}>
                        <ArrowLeft size={20} />
                        Quay lại
                    </button>
                    <div className="flex gap-3 mb-4">
                        <span className="px-3 py-1 bg-red-600 rounded-lg text-xs font-bold uppercase tracking-wider">
                            {offer.type === 'offer' ? 'Ưu đãi' : 'Sự kiện'}
                        </span>
                        <span className="px-3 py-1 bg-white/10 rounded-lg text-xs font-bold flex items-center gap-1">
                            <Tag size={12} />
                            {offer.tag}
                        </span>
                    </div>
                    <h1 className="text-4xl md:text-6xl font-black mb-4">{offer.title}</h1>
                    <div className="flex gap-6 text-gray-300">
                        <div className="flex items-center gap-2">
                            <Calendar size={18} className="text-red-600" />
                            <span>{offer.date}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div className="container py-12">
                <div className="grid grid-cols-1 lg:grid-cols-3 gap-12">
                    <div className="lg:col-span-2 space-y-8">
                        <section>
                            <h2 className="text-2xl font-bold mb-4">Chi tiết</h2>
                            <p className="text-gray-400 leading-relaxed text-lg">{offer.description}</p>
                        </section>

                        <section>
                            <h2 className="text-2xl font-bold mb-4">Điều khoản & Điều kiện</h2>
                            <ul className="space-y-3 text-gray-400">
                                <li className="flex items-start gap-2">
                                    <div className="w-1.5 h-1.5 bg-red-600 rounded-full mt-2"></div>
                                    Áp dụng tại tất cả các hệ thống rạp CineBook trên toàn quốc.
                                </li>
                                <li className="flex items-start gap-2">
                                    <div className="w-1.5 h-1.5 bg-red-600 rounded-full mt-2"></div>
                                    Không áp dụng cùng lúc với các chương trình khuyến mãi khác.
                                </li>
                                <li className="flex items-start gap-2">
                                    <div className="w-1.5 h-1.5 bg-red-600 rounded-full mt-2"></div>
                                    Ưu đãi có thể kết thúc sớm hơn dự kiến.
                                </li>
                            </ul>
                        </section>
                    </div>

                    <div className="lg:col-span-1">
                        <div className="bg-[#141414] border border-white/5 p-8 rounded-2xl sticky top-24">
                            <h3 className="text-xl font-bold mb-4">Tham gia ngay?</h3>
                            <p className="text-gray-400 text-sm mb-8">Đừng bỏ lỡ cơ hội nhận ưu đãi đặc biệt này từ CineBook.</p>

                            <button
                                className={`w-full py-4 rounded-xl font-bold transition-all mb-4 ${actionState.completed ? 'bg-green-600 text-white' : 'bg-red-600 hover:bg-red-700 text-white shadow-lg shadow-red-600/20'}`}
                                onClick={handleAction}
                                disabled={actionState.loading || actionState.completed}
                            >
                                {actionState.loading ? 'Đang xử lý...' : (
                                    actionState.completed ? 'Đã tham gia' : (offer.type === 'offer' ? 'Nhận ưu đãi' : 'Đăng ký ngay')
                                )}
                            </button>

                            <button className="w-full py-4 bg-white/5 hover:bg-white/10 text-white rounded-xl font-bold flex items-center justify-center gap-2 transition-all">
                                <Share2 size={18} />
                                Chia sẻ
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <Footer />
        </div>
    );
};

export default OfferDetails;

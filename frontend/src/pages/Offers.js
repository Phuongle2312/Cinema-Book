import React from 'react';
import Navbar from '../components/Navbar';
import Footer from '../components/Footer';
import eventsData from '../data/events.json';
import { Calendar, TicketPercent, ChevronRight } from 'lucide-react';
import { useNavigate } from 'react-router-dom';
import './Offers.css';

const Offers = () => {
    const navigate = useNavigate();
    // Filter for offers
    const offers = eventsData.filter(item => item.type === 'offer');

    return (
        <div className="offers-page bg-[#0a0a0a] min-h-screen text-white">
            <Navbar />

            <div className="relative py-32 overflow-hidden">
                <div className="absolute inset-0 bg-red-600/10 blur-[120px] rounded-full -translate-x-1/2 -translate-y-1/2"></div>
                <div className="container relative text-center">
                    <h1 className="text-5xl md:text-7xl font-black mb-6 tracking-tight uppercase">Ưu đãi độc quyền</h1>
                    <p className="text-gray-400 max-w-2xl mx-auto text-lg">
                        Khám phá những ưu đãi hấp dẫn, mã giảm giá đặc biệt và các chương trình dành riêng cho thành viên CineBook.
                    </p>
                </div>
            </div>

            <div className="container pb-24">
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    {offers.length > 0 ? (
                        offers.map(offer => (
                            <div
                                key={offer.id}
                                className="group bg-[#141414] border border-white/5 rounded-2xl overflow-hidden hover:border-red-600/50 transition-all cursor-pointer"
                                onClick={() => navigate(`/offers/${offer.id}`)}
                            >
                                <div className="relative h-56 overflow-hidden">
                                    <img src={offer.image} alt={offer.title} className="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" />
                                    <div className="absolute top-4 left-4 px-3 py-1 bg-yellow-500 text-black text-xs font-black rounded-lg flex items-center gap-1">
                                        <TicketPercent size={14} />
                                        {offer.tag}
                                    </div>
                                </div>

                                <div className="p-6">
                                    <div className="text-gray-500 text-xs font-bold flex items-center gap-2 mb-3">
                                        <Calendar size={14} className="text-red-600" />
                                        {offer.date}
                                    </div>
                                    <h3 className="text-xl font-bold mb-3 group-hover:text-red-600 transition-colors uppercase line-clamp-1">{offer.title}</h3>
                                    <p className="text-gray-400 text-sm line-clamp-2 mb-6 leading-relaxed">{offer.description}</p>

                                    <div className="flex items-center justify-between">
                                        <button className="text-red-600 font-bold text-sm flex items-center gap-1 group-hover:gap-2 transition-all">
                                            CHI TIẾT <ChevronRight size={16} />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        ))
                    ) : (
                        <div className="col-span-full py-20 text-center border border-dashed border-white/10 rounded-2xl">
                            <p className="text-gray-500">Hiện tại chưa có ưu đãi nào mới. Quay lại sau nhé!</p>
                        </div>
                    )}
                </div>
            </div>

            <Footer />
        </div>
    );
};

export default Offers;

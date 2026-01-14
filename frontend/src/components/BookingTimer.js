import React, { useState, useEffect } from 'react';
import { Clock } from 'lucide-react';

const BookingTimer = ({ expiresAt, onExpire }) => {
    const [timeLeft, setTimeLeft] = useState(0);

    useEffect(() => {
        if (!expiresAt) return;

        const calculateTimeLeft = () => {
            const expireTime = new Date(expiresAt).getTime();
            const now = new Date().getTime();
            const diff = Math.floor((expireTime - now) / 1000);
            return diff > 0 ? diff : 0;
        };

        setTimeLeft(calculateTimeLeft());

        const interval = setInterval(() => {
            const remaining = calculateTimeLeft();
            setTimeLeft(remaining);

            if (remaining <= 0) {
                clearInterval(interval);
                if (onExpire) onExpire();
            }
        }, 1000);

        return () => clearInterval(interval);
    }, [expiresAt, onExpire]);

    const formatTime = (seconds) => {
        const min = Math.floor(seconds / 60);
        const sec = seconds % 60;
        return `${min}:${sec < 10 ? '0' : ''}${sec}`;
    };

    if (timeLeft <= 0) return <span className="text-red-500 font-bold ml-2">Expired</span>;

    return (
        <span className="flex items-center text-yellow-500 font-bold ml-3" style={{ fontSize: '0.9rem' }}>
            <Clock size={14} className="mr-1" />
            {formatTime(timeLeft)}
        </span>
    );
};

export default BookingTimer;

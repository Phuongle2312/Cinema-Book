import React, { useState, useEffect, useCallback } from 'react';
import { useToast } from '../context/ToastContext';
import './SeatSelection.css'; // Assuming you will create CSS

// Mock data or API call would go here
import bookingService from '../services/bookingService';

const SeatSelection = ({ showtimeId, onSeatSelect, selectedSeats }) => {
    const toast = useToast();
    const [seats, setSeats] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [rows, setRows] = useState([]);

    // Fetch seats from API
    useEffect(() => {
        const fetchSeats = async () => {
            try {
                // API returns { showtime, seats: [...], ... }
                const responseData = await bookingService.getSeats(showtimeId);
                const seatsList = responseData.seats || [];
                setSeats(seatsList);

                // Process seats into rows for rendering
                const rowMap = {};
                seatsList.forEach(seat => {
                    if (!rowMap[seat.row]) {
                        rowMap[seat.row] = [];
                    }
                    rowMap[seat.row].push(seat);
                });

                // Sort rows and seats
                const sortedRows = Object.keys(rowMap).sort();
                const processedRows = sortedRows.map(rowLabel => ({
                    label: rowLabel,
                    seats: rowMap[rowLabel].sort((a, b) => a.number - b.number)
                }));

                setRows(processedRows);
                setLoading(false);
            } catch (err) {
                setError("Failed to load seats.");
                setLoading(false);
            }
        };

        fetchSeats();

        // Polling for real-time updates (every 10 seconds)
        const interval = setInterval(fetchSeats, 10000);
        return () => clearInterval(interval);
    }, [showtimeId]);

    const handleSeatClick = useCallback(async (seat) => {
        if (seat.status === 'booked' || seat.status === 'locked') {
            return;
        }

        // Toggle selection locally first for UX
        const isSelected = selectedSeats.includes(seat.seat_id);

        if (isSelected) {
            onSeatSelect(seat.seat_id, false);
            // Optionally release lock API call here if needed immediately
        } else {
            // Check if selected seat matches the type of already selected seats
            if (selectedSeats.length > 0) {
                // Find the type of the first selected seat using loose equality for ID
                const firstSelectedSeatId = selectedSeats[0];
                const firstSelectedSeat = seats.find(s => s.seat_id == firstSelectedSeatId); // Loose equality

                // If we can't find the seat in the list (rare), rely on seats state if possible or skip check
                if (firstSelectedSeat && seat.type && firstSelectedSeat.type.toLowerCase() !== seat.type.toLowerCase()) {
                    const typeLabel = firstSelectedSeat.type.toLowerCase() === 'vip' ? 'VIP' : 'Standard';
                    toast.warning(`You can only select seats of the same type (${typeLabel}). Please deselect current seats to choose another type.`);
                    return;
                }
            }

            // Check concurrency via API (Hold Seat)
            try {
                // Optimistic UI update
                onSeatSelect(seat.seat_id, true);

                // Call API to hold
                await bookingService.holdSeats(showtimeId, [seat.seat_id]);
            } catch (err) {
                // Revert if failed
                onSeatSelect(seat.seat_id, false);
                toast.error("Seat already selected by another user!");
                // Refresh map
                // fetchSeats(); 
            }
        }
    }, [selectedSeats, showtimeId, onSeatSelect, seats]);

    if (loading) return <div>Loading seats...</div>;
    if (error) return <div>{error}</div>;

    return (
        <div className="seat-map-container">
            <div className="screen-display">SCREEN</div>

            <div className="seat-grid">
                {rows.map(row => (
                    <div key={row.label} className="seat-row">
                        <span className="row-label">{row.label}</span>
                        <div className="seats">
                            {row.seats.map(seat => {
                                const isSelected = selectedSeats.includes(seat.seat_id);
                                const statusClass = (seat.status || 'available').toLowerCase();
                                const typeClass = (seat.type || 'standard').toLowerCase();

                                return (
                                    <button
                                        key={seat.seat_id}
                                        className={`seat-item ${statusClass} ${typeClass} ${isSelected ? 'selected' : ''}`}
                                        onClick={() => handleSeatClick(seat)}
                                        disabled={statusClass === 'booked' || (statusClass === 'locked' && !isSelected)}
                                        title={`${row.label}${seat.number} - ${seat.type}`}
                                    >
                                        {seat.number}
                                    </button>
                                );
                            })}
                        </div>
                    </div>
                ))}
            </div>

            <div className="seat-legend">
                <div className="legend-item"><span className="seat-item available standard"></span> Standard</div>
                <div className="legend-item"><span className="seat-item available vip"></span> VIP</div>
                <div className="legend-item"><span className="seat-item selected"></span> Selecting</div>
                <div className="legend-item"><span className="seat-item booked"></span> Sold</div>
                <div className="legend-item"><span className="seat-item locked"></span> Held</div>
            </div>
        </div>
    );
};

export default SeatSelection;

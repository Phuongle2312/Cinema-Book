/**
 * Mock Data for Frontend-Only Testing
 * Now powered by the consolidated details.json
 */
import movieDetails from '../data/details.json';

// Helper to extract YouTube ID and return embed URL
const getEmbedUrl = (url) => {
    if (!url) return null;
    let videoId = '';
    if (url.includes('youtu.be/')) {
        videoId = url.split('youtu.be/')[1].split(/[?#]/)[0];
    } else if (url.includes('youtube.com/watch?v=')) {
        videoId = url.split('v=')[1].split(/[&?#]/)[0];
    } else if (url.includes('youtube.com/embed/')) {
        return url;
    }
    return videoId ? `https://www.youtube.com/embed/${videoId}` : url;
};

// Map and merge data from JSON files
export const MOCK_MOVIES = movieDetails.map(movie => {
    const formattedGenres = movie.genres.map(g => ({ name: g }));

    return {
        movie_id: movie.id,
        id: movie.id, // Add id for consistency with Link paths
        title: movie.title,
        slug: movie.title.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)+/g, ''),
        description: movie.description,
        duration: movie.duration, // Use string from JSON
        release_date: `${movie.year}-01-01`,
        poster_url: movie.image,
        banner_url: movie.bannerImage || movie.image,
        trailer_url: getEmbedUrl(movie.trailer),
        rating: parseFloat(movie.rating),
        status: movie.genres.includes('In Theaters') ? 'now_showing' : 'coming_soon',
        genres: formattedGenres,
        languages: [{ name: "English" }, { name: "Vietnamese" }],
        year: parseInt(movie.year),
        cast: (movie['cast&crew'] || []).map(person => ({
            name: person.name,
            image: person.img || person.image_url,
            role: person.role || (person.description && person.description.includes('Combo') ? 'Placeholder' : 'Actor')
        }))
    };
});


// Update Showtimes to use the Movie IDs (1 to 6)
export const MOCK_SHOWTIMES = [
    {
        showtime_id: 101,
        movie_id: 1, // Chainsaw Man
        start_time: "2025-12-30T07:10:00",
        base_price: 80000,
        format: "Rạp 2D",
        room: { name: "Cinema 01", theater: { name: "CGV Hùng Vương Plaza", city: "Hồ Chí Minh" } }
    },
    {
        showtime_id: 102,
        movie_id: 1,
        start_time: "2025-12-30T13:50:00",
        base_price: 90000,
        format: "Rạp 2D",
        room: { name: "Cinema 01", theater: { name: "CGV Hùng Vương Plaza", city: "Hồ Chí Minh" } }
    },
    {
        showtime_id: 103,
        movie_id: 2, // Sisu
        start_time: "2025-12-30T18:30:00",
        base_price: 100000,
        format: "Rạp 2D",
        room: { name: "Cinema 02", theater: { name: "CGV Hùng Vương Plaza", city: "Hồ Chí Minh" } }
    },
    {
        showtime_id: 104,
        movie_id: 2,
        start_time: "2025-12-30T23:10:00",
        base_price: 110000,
        format: "Rạp 2D",
        room: { name: "Cinema 02", theater: { name: "CGV Hùng Vương Plaza", city: "Hồ Chí Minh" } }
    },
    {
        showtime_id: 105,
        movie_id: 3, // Now You See Me 3
        start_time: "2025-12-30T08:20:00",
        base_price: 80000,
        format: "2D Phụ Đề Anh",
        room: { name: "Cinema 03", theater: { name: "CGV Hùng Vương Plaza", city: "Hồ Chí Minh" } }
    },
    {
        showtime_id: 106,
        movie_id: 3,
        start_time: "2025-12-30T08:00:00",
        base_price: 80000,
        format: "Rạp 2D",
        room: { name: "Cinema 01", theater: { name: "CGV Aeon Tân Phú", city: "Hồ Chí Minh" } }
    },
    {
        showtime_id: 107,
        movie_id: 4, // Zootopia 2
        start_time: "2025-12-30T09:10:00",
        base_price: 80000,
        format: "Rạp 2D",
        room: { name: "Cinema 01", theater: { name: "CGV Aeon Tân Phú", city: "Hồ Chí Minh" } }
    },
    {
        showtime_id: 108,
        movie_id: 5, // Knives Out 3
        start_time: "2025-12-30T12:10:00",
        base_price: 90000,
        format: "Rạp 2D",
        room: { name: "Cinema 02", theater: { name: "CGV Aeon Tân Phú", city: "Hồ Chí Minh" } }
    },
    {
        showtime_id: 109,
        movie_id: 6, // Wike
        start_time: "2025-12-30T10:00:00",
        base_price: 120000,
        format: "IMAX",
        room: { name: "IMAX Room", theater: { name: "CGV Metropolis Liễu Giai", city: "Hà Nội" } }
    }
];

export const generateMockSeats = () => {
    const rows = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];
    const seatsPerRow = 12;
    const seatMap = {};
    const allSeats = [];

    rows.forEach(row => {
        seatMap[row] = [];
        // Row H is for Couple seats (fewer seats)
        const count = row === 'H' ? 6 : seatsPerRow;

        for (let i = 1; i <= count; i++) {
            const seat = {
                seat_id: `${row}${i}`,
                row: row,
                number: i,
                status: Math.random() > 0.85 ? 'booked' : 'available',
                seat_type: { name: row === 'G' ? 'VIP' : (row === 'H' ? 'Couple' : 'Standard') },
                extra_price: row === 'G' ? 20000 : (row === 'H' ? 50000 : 0)
            };
            seatMap[row].push(seat);
            allSeats.push(seat);
        }
    });

    return { seat_map: seatMap, seats: allSeats };
};

export const MOCK_BOOKINGS = [
    {
        booking_id: 'BK-HISTORY-01',
        user_id: 1,
        showtime_id: 101, // Points to Chainsaw Man
        status: 'confirmed',
        booking_code: 'BK999999',
        total_price: 180000,
        created_at: new Date(Date.now() - 86400000).toISOString(),
        seats: [
            { seat_id: 'F5', row: 'F', number: 5, seat_type: { name: 'Standard' } },
            { seat_id: 'F6', row: 'F', number: 6, seat_type: { name: 'Standard' } }
        ],
        combos: []
    }
];

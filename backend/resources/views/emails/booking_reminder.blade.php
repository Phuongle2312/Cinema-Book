<!DOCTYPE html>
<html>

<head>
    <title>Booking Reminder</title>
</head>

<body>
    <h1>🎬 Movie Reminder: {{ $booking->showtime->movie->title }}</h1>
    <p>Hi {{ $booking->user->name }},</p>
    <p>Your movie is starting soon!</p>

    <p><strong>Movie:</strong> {{ $booking->showtime->movie->title }}</p>
    <p><strong>Time:</strong> {{ $booking->showtime->start_time->format('H:i d/m/Y') }}</p>
    <p><strong>Theater:</strong> {{ $booking->showtime->room->theater->name }} - {{ $booking->showtime->room->name }}
    </p>
    <p><strong>Seats:</strong>
        @foreach($booking->seats as $seat)
            {{ $seat->row }}{{ $seat->number }}@if(!$loop->last), @endif
        @endforeach
    </p>

    <p>Booking Code: <strong>{{ $booking->booking_code }}</strong></p>

    <p>See you there!</p>
    <p><em>CineBook Team</em></p>
</body>

</html>
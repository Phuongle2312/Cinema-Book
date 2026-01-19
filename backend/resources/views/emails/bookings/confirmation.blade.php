<x-mail::message>
    # Booking Confirmed!

    Hi {{ $booking->user->name }},

    Thank you for booking with **CineBook**. Your payment has been processed successfully.

    ## Ticket Details
    **Movie:** {{ $booking->showtime->movie->title }}
    **Theater:** {{ $booking->showtime->room->theater->name }} - Room {{ $booking->showtime->room->name }}
    **Time:** {{ $booking->showtime->start_time->format('H:i d/m/Y') }}
    **Seats:** {{ $booking->seats->pluck('row')->map(fn($r, $k) => $r . $booking->seats[$k]->number)->implode(', ') }}

    <x-mail::panel>
        # {{ $booking->booking_code }}
        Present this code at the counter to receive your tickets.
    </x-mail::panel>

    <x-mail::button :url="$url">
        View E-Ticket
    </x-mail::button>

    Thanks,<br>
    {{ config('app.name') }}
</x-mail::message>
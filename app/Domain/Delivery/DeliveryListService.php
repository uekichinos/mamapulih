<?php

namespace App\Domain\Delivery;

use App\Models\Delivery;
use Illuminate\Support\Carbon;

class DeliveryListService
{
    /**
     * Deliveries for the date, grouped by run then zone name — matching
     * the documented delivery-list columns (name, address, phone, notes,
     * one-trip flag).
     *
     * @return array<string, array<string, array<int, array<string, mixed>>>>
     */
    public function forDate(Carbon $date): array
    {
        $deliveries = Delivery::query()
            ->whereHas('bookingDay', fn ($q) => $q->where('date', $date->toDateString())->where('status', 'scheduled'))
            ->with(['zone', 'bookingDay.booking.customer', 'bookingDay.booking.address'])
            ->get();

        $grouped = [];

        foreach ($deliveries as $delivery) {
            $booking = $delivery->bookingDay->booking;
            $address = $booking->address;
            $zoneName = $delivery->zone->name ?? 'Unzoned';

            $grouped[$delivery->run][$zoneName][] = [
                'name' => $booking->customer->name,
                'address' => trim($address->line1.($address->line2 ? ', '.$address->line2 : '').', '.$address->postcode),
                'phone' => $booking->customer->phone,
                'notes' => $address->notes,
                'one_trip' => $delivery->includes_dinner,
            ];
        }

        return $grouped;
    }
}

<?php

namespace App\Services;

use App\Jobs\SendReservationReceipt;
use App\Models\Hall;
use App\Models\Reservation;
use App\Models\ReservationItem;
use App\Models\Room;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BookingService
{
    /**
     * Buat reservasi kamar (snapshot harga dari database).
     *
     * @param  array<string, mixed>  $data
     *
     * @throws ValidationException
     */
    public function createRoom(?User $customer, array $data): Reservation
    {
        $room = Room::findOrFail($data['room_id']);
        $checkIn = Carbon::parse($data['check_in_date']);
        $checkOut = Carbon::parse($data['check_out_date']);

        $this->ensureRoomIsAvailable($room, $checkIn, $checkOut);

        $nights = (int) $checkIn->diffInDays($checkOut);
        $extraGuests = max(0, (int) $data['guest_count'] - $room->base_capacity);
        $subtotal = round(($room->base_price + $extraGuests * $room->extra_person_rate) * $nights);
        $downPayment = $this->resolveDownPayment((string) $data['payment_scheme'], $subtotal, $data);

        $reservation = $this->newReservation($customer, $data['payment_scheme'], $subtotal, [
            'check_in_date' => $checkIn->toDateString(),
            'check_out_date' => $checkOut->toDateString(),
        ], $this->metaFrom($data), $downPayment);

        $reservation->items()->create([
            'item_type' => 'room',
            'room_id' => $room->id,
            'unit_price_snapshot' => $room->base_price,
            'cost_components' => $room->cost_components,
            'guest_count' => $data['guest_count'],
            'extra_guests' => $extraGuests,
            'extra_person_rate_snapshot' => $room->extra_person_rate,
            'subtotal' => $subtotal,
        ]);

        $this->notifyCustomer($reservation);

        return $reservation;
    }

    /**
     * Buat reservasi sewa aula untuk satu tanggal. Pemakaian aula mengikuti
     * jam operasional (08:00-16:00) dan harganya adalah harga dasar per hari.
     *
     * Batas check-out = jam selesai operasional supaya tidak kena denda
     * keterlambatan saat di-check-out tepat waktu. Snapshot tarif item diisi
     * tarif denda per jam karena itu satu-satunya harga satuan aula.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws ValidationException
     */
    public function createHall(?User $customer, array $data): Reservation
    {
        $hall = Hall::findOrFail($data['hall_id']);
        $date = Carbon::parse($data['hall_date'])->startOfDay();
        $start = $date->copy()->setTimeFromTimeString(Hall::OPERATIONAL_START);
        $end = $date->copy()->setTimeFromTimeString(Hall::OPERATIONAL_END);

        $this->ensureHallIsAvailable($hall, $start, $end);

        $subtotal = $hall->dailyRate();
        $downPayment = $this->resolveDownPayment((string) $data['payment_scheme'], $subtotal, $data);

        $reservation = $this->newReservation($customer, $data['payment_scheme'], $subtotal, [
            'check_in_date' => $date->toDateString(),
            'check_out_date' => $date->toDateString(),
            'scheduled_checkout_time' => Hall::OPERATIONAL_END . ':00',
        ], $this->metaFrom($data), $downPayment);

        $reservation->items()->create([
            'item_type' => 'hall',
            'hall_id' => $hall->id,
            'unit_price_snapshot' => $hall->lateChargeRate(),
            'cost_components' => $hall->costComponents(),
            'hall_start_time' => $start,
            'hall_end_time' => $end,
            'hall_hours' => Hall::OPERATIONAL_HOURS,
            'subtotal' => $subtotal,
        ]);

        $this->notifyCustomer($reservation);

        return $reservation;
    }

    /**
     * @param  array<string, mixed>  $dates
     */
    private function newReservation(?User $customer, string $scheme, float $total, array $dates, array $meta = [], ?float $downPayment = null): Reservation
    {
        return Reservation::create($meta + $dates + [
            'booking_code' => $this->generateBookingCode(),
            'customer_id' => $customer?->id,
            'status' => 'booked',
            'payment_scheme' => $scheme,
            'down_payment_amount' => $downPayment,
            'total_amount' => $total,
        ]);
    }

    /**
     * Tentukan nominal DP yang wajib diisi manual.
     *
     * @param  array<string, mixed>  $data
     */
    private function resolveDownPayment(string $scheme, float $total, array $data): ?float
    {
        if ($scheme !== 'dp') {
            return null;
        }

        $requested = $data['down_payment_amount'] ?? null;

        if ($requested === null || $requested === '') {
            throw ValidationException::withMessages([
                'down_payment_amount' => 'Nominal DP wajib diisi untuk skema DP.',
            ]);
        }

        $amount = round((float) $requested, 2);

        if ($amount < Reservation::MIN_DOWN_PAYMENT) {
            throw ValidationException::withMessages([
                'down_payment_amount' => 'Nominal DP minimal Rp' . number_format(Reservation::MIN_DOWN_PAYMENT, 0, ',', '.') . '.',
            ]);
        }

        if ($amount > $total) {
            throw ValidationException::withMessages([
                'down_payment_amount' => 'Nominal DP tidak boleh melebihi total tagihan.',
            ]);
        }

        return $amount;
    }

    private function metaFrom(array $data): array
    {
        return [
            'customer_name' => $data['customer_name'] ?? null,
            'customer_phone' => $data['customer_phone'] ?? null,
            'payment_proof' => $data['payment_proof'] ?? null,
            'created_by' => $data['created_by'] ?? null,
        ];
    }

    /**
     * Antrekan notifikasi WhatsApp berisi resi ke pelanggan (jika diaktifkan).
     */
    private function notifyCustomer(Reservation $reservation): void
    {
        if (!config('whatsapp.enabled')) {
            return;
        }

        SendReservationReceipt::dispatch($reservation);
    }

    private function ensureRoomIsAvailable(Room $room, Carbon $checkIn, Carbon $checkOut): void
    {
        if ($room->status === 'maintenance') {
            throw ValidationException::withMessages([
                'room_id' => 'Kamar sedang dalam perawatan.',
            ]);
        }

        $hasConflict = Reservation::query()
            ->where('status', '!=', 'cancelled')
            ->where('check_in_date', '<', $checkOut->toDateString())
            ->where('check_out_date', '>', $checkIn->toDateString())
            ->whereHas('items', fn($query) => $query->where('room_id', $room->id))
            ->exists();

        if ($hasConflict) {
            throw ValidationException::withMessages([
                'check_in_date' => 'Kamar sudah dipesan pada rentang tanggal tersebut.',
            ]);
        }
    }

    private function ensureHallIsAvailable(Hall $hall, Carbon $start, Carbon $end): void
    {
        if ($hall->status === 'maintenance') {
            throw ValidationException::withMessages([
                'hall_id' => 'Aula sedang dalam perawatan.',
            ]);
        }

        $hasConflict = ReservationItem::query()
            ->where('hall_id', $hall->id)
            ->where('hall_start_time', '<', $end)
            ->where('hall_end_time', '>', $start)
            ->whereHas('reservation', fn($query) => $query->where('status', '!=', 'cancelled'))
            ->exists();

        if ($hasConflict) {
            throw ValidationException::withMessages([
                'hall_date' => 'Aula sudah dibooking pada tanggal tersebut.',
            ]);
        }
    }

    private function generateBookingCode(): string
    {
        do {
            $code = 'BK' . now()->format('ymd') . strtoupper(Str::random(6));
        } while (Reservation::where('booking_code', $code)->exists());

        return $code;
    }
}

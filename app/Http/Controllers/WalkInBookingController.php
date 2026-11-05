<?php

namespace App\Http\Controllers;

use App\Models\Hall;
use App\Models\Reservation;
use App\Models\ReservationItem;
use App\Models\Room;
use App\Services\BookingService;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Js;
use Illuminate\View\View;

/**
 * Resepsionis membuatkan reservasi untuk pelanggan datang langsung (walk-in).
 * Minimum DP dibayar di tempat; sisa tagihan / denda dibayar cash saat check-out.
 */
class WalkInBookingController extends Controller
{
    public function __construct(
        private readonly BookingService $bookingService,
        private readonly PaymentService $paymentService,
    ) {
    }

    public function chooser(): View
    {
        return view('admin.walkin.choose');
    }

    public function roomForm(Request $request): View
    {
        $rooms = Room::bookable()->orderBy('room_number')->get();

        $selectedRoomId = (int) $request->integer('room');

        if (!$rooms->contains('id', $selectedRoomId)) {
            $selectedRoomId = '';
        }

        return view('admin.walkin.room', [
            'rooms' => $rooms,
            'preselectedRoomId' => $selectedRoomId,
            'payload' => Js::from([
                'form' => [
                    'room_id' => old('room_id', $selectedRoomId),
                    'check_in_date' => old('check_in_date', ''),
                    'check_out_date' => old('check_out_date', ''),
                    'guest_count' => old('guest_count', 4),
                    'payment_scheme' => old('payment_scheme', 'dp'),
                    'down_payment_amount' => old('down_payment_amount', ''),
                    'payment_method' => old('payment_method', 'cash'),
                ],
                'rooms' => $rooms->map(fn(Room $room) => [
                    'id' => $room->id,
                    'base_price' => (float) $room->base_price,
                    'extra_person_rate' => (float) $room->extra_person_rate,
                    'base_capacity' => $room->base_capacity,
                ])->values(),
                'minDownPayment' => Reservation::MIN_DOWN_PAYMENT,
                'minDate' => now()->toDateString(),
                'availabilityUrl' => route('walkin.room.availability'),
            ]),
        ]);
    }

    /**
     * Daftar kamar yang tidak tersedia pada rentang tanggal tertentu.
     * Dipakai form walk-in agar kamar yang bentrok dinonaktifkan secara
     * dinamis, tanpa menyembunyikan kamar yang masih bisa dipesan pada
     * tanggal lain.
     */
    public function roomAvailability(Request $request): JsonResponse
    {
        $data = $request->validate([
            'check_in_date' => ['required', 'date'],
            'check_out_date' => ['required', 'date', 'after:check_in_date'],
        ]);

        $availableRoomIds = Room::bookable()
            ->availableBetween($data['check_in_date'], $data['check_out_date'])
            ->pluck('id');

        $unavailableRoomIds = Room::query()
            ->whereNotIn('id', $availableRoomIds)
            ->pluck('id');

        return response()->json(['unavailable_room_ids' => $unavailableRoomIds]);
    }

    public function hallForm(Request $request): View
    {
        $halls = Hall::where('status', '!=', 'maintenance')->orderBy('hall_name')->get();

        $selectedHallId = (int) $request->integer('hall');

        if (!$halls->contains('id', $selectedHallId)) {
            // Penginapan hanya punya satu aula, jadi langsung terisi otomatis.
            $selectedHallId = $halls->count() === 1 ? (int) $halls->first()->id : '';
        }

        return view('admin.walkin.hall', [
            'halls' => $halls,
            'hall' => $halls->first(),
            'preselectedHallId' => $selectedHallId,
            'availabilityUrl' => route('walkin.hall.availability'),
            'payload' => Js::from([
                'form' => [
                    'hall_id' => old('hall_id', $selectedHallId),
                    'hall_date' => old('hall_date', ''),
                    'payment_scheme' => old('payment_scheme', 'dp'),
                    'down_payment_amount' => old('down_payment_amount', ''),
                    'payment_method' => old('payment_method', 'cash'),
                ],
                'halls' => $halls->map(fn(Hall $hall) => [
                    'id' => $hall->id,
                    'daily_rate' => $hall->dailyRate(),
                ])->values(),
                'minDownPayment' => Reservation::MIN_DOWN_PAYMENT,
                'availabilityUrl' => route('walkin.hall.availability'),
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $room = Room::find($request->input('room_id'));
        $maxGuests = ($room?->base_capacity ?? 4) + 1;

        $data = $request->validate([
            'room_id' => ['required', 'exists:rooms,id'],
            'check_in_date' => ['required', 'date', 'after_or_equal:today'],
            'check_out_date' => ['required', 'date', 'after:check_in_date'],
            'guest_count' => ['required', 'integer', 'min:1', 'max:' . $maxGuests],
            'customer_name' => ['required', 'string', 'max:100'],
            'customer_phone' => ['nullable', 'string', 'max:30'],
            'payment_scheme' => ['required', 'in:dp,lunas'],
            'down_payment_amount' => ['exclude_unless:payment_scheme,dp', 'required_if:payment_scheme,dp', 'numeric', 'min:' . Reservation::MIN_DOWN_PAYMENT],
            'payment_method' => ['required', 'in:cash,bank_transfer'],
            'payment_proof' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048', 'required_if:payment_method,bank_transfer'],
        ], [
            'check_in_date.after_or_equal' => 'Tanggal check-in tidak boleh sebelum hari ini.',
            'check_out_date.after' => 'Tanggal check-out harus setelah tanggal check-in.',
            'guest_count.max' => "Maksimal {$maxGuests} orang per kamar (ekstra paling banyak 1 orang).",
            'down_payment_amount.required_if' => 'Nominal DP wajib diisi untuk skema DP.',
            'down_payment_amount.min' => 'Nominal DP minimal Rp' . number_format(Reservation::MIN_DOWN_PAYMENT, 0, ',', '.') . '.',
            'payment_proof.mimes' => 'Bukti transfer harus berupa gambar (JPG/PNG) atau PDF.',
            'payment_proof.required_if' => 'Unggah bukti transfer pembayaran.',
        ]);

        if ($request->hasFile('payment_proof')) {
            $data['payment_proof'] = $request->file('payment_proof')->store('payment_proofs', 'public');
        }

        $data['created_by'] = $request->user()->id;

        $reservation = $this->bookingService->createRoom(null, $data);
        $this->recordInitialPayment($reservation, $data['payment_method']);

        return redirect()
            ->route('cashier.show', $reservation)
            ->with('success', "Reservasi walk-in {$reservation->booking_code} berhasil dibuat.");
    }

    public function storeHall(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'hall_id' => ['required', 'exists:halls,id'],
            'hall_date' => ['required', 'date', 'after_or_equal:today'],
            'customer_name' => ['required', 'string', 'max:100'],
            'customer_phone' => ['nullable', 'string', 'max:30'],
            'payment_scheme' => ['required', 'in:dp,lunas'],
            'down_payment_amount' => ['exclude_unless:payment_scheme,dp', 'required_if:payment_scheme,dp', 'numeric', 'min:' . Reservation::MIN_DOWN_PAYMENT],
            'payment_method' => ['required', 'in:cash,bank_transfer'],
            'payment_proof' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048', 'required_if:payment_method,bank_transfer'],
        ], [
            'hall_date.after_or_equal' => 'Tanggal reservasi aula tidak boleh sebelum hari ini.',
            'customer_name.required' => 'Nama pelanggan wajib diisi.',
            'down_payment_amount.required_if' => 'Nominal DP wajib diisi untuk skema DP.',
            'down_payment_amount.min' => 'Nominal DP minimal Rp' . number_format(Reservation::MIN_DOWN_PAYMENT, 0, ',', '.') . '.',
            'payment_proof.required_if' => 'Unggah bukti transfer pembayaran.',
            'payment_proof.mimes' => 'Bukti transfer harus berupa gambar (JPG/PNG) atau PDF.',
        ]);

        if ($request->hasFile('payment_proof')) {
            $data['payment_proof'] = $request->file('payment_proof')->store('payment_proofs', 'public');
        }

        $data['created_by'] = $request->user()->id;

        $reservation = $this->bookingService->createHall(null, $data);
        $this->recordInitialPayment($reservation, $data['payment_method']);

        return redirect()
            ->route('cashier.show', $reservation)
            ->with('success', "Reservasi walk-in {$reservation->booking_code} berhasil dibuat.");
    }

    public function hallAvailability(Request $request): JsonResponse
    {
        $hallId = $request->integer('hall_id') ?: null;
        $from = now()->startOfDay();
        $to = now()->addMonths(3)->endOfDay();

        $query = ReservationItem::query()
            ->where('item_type', 'hall')
            ->whereHas('reservation', fn($q) => $q->where('status', '!=', 'cancelled'))
            ->whereNotNull('hall_start_time');

        if ($hallId) {
            $query->where('hall_id', $hallId);
        }

        $dates = $query
            ->whereBetween('hall_start_time', [$from, $to])
            ->get()
            ->map(fn(ReservationItem $item) => $item->hall_start_time?->toDateString())
            ->filter()
            ->unique()
            ->sort()
            ->values();

        return response()->json(['dates' => $dates]);
    }

    private function recordInitialPayment(Reservation $reservation, string $method): void
    {
        $amount = $reservation->payment_scheme === 'dp'
            ? (float) $reservation->down_payment_amount
            : (float) $reservation->total_amount;

        $label = $reservation->payment_scheme === 'dp'
            ? 'DP walk-in (pembayaran di tempat)'
            : 'Pelunasan walk-in (pembayaran di tempat)';

        $this->paymentService->record($reservation, [
            'amount' => $amount,
            'payment_method' => $method,
            'payment_proof' => $reservation->payment_proof,
            'notes' => $label,
        ], request()->user());
    }
}

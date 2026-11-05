<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CashierController extends Controller
{
    public function __construct(private readonly PaymentService $paymentService)
    {
    }

    public function show(Reservation $reservation): View
    {
        $reservation->load(['customer', 'items.room', 'items.hall', 'checkoutLog', 'payments.receivedBy']);

        return view('admin.cashier.show', ['reservation' => $reservation]);
    }

    public function pay(Request $request, Reservation $reservation): RedirectResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'payment_method' => ['required', 'in:cash,bank_transfer'],
            'payment_proof' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048', 'required_if:payment_method,bank_transfer'],
            'notes' => ['nullable', 'string', 'max:255'],
        ], [
            'payment_proof.required_if' => 'Unggah bukti transfer pembayaran.',
            'payment_proof.mimes' => 'Bukti transfer harus berupa gambar (JPG/PNG) atau PDF.',
        ]);

        if ($request->hasFile('payment_proof')) {
            $data['payment_proof'] = $request->file('payment_proof')->store('payment_proofs', 'public');
        }

        $this->paymentService->record($reservation, $data, $request->user());

        return redirect()
            ->route('cashier.show', $reservation)
            ->with('success', 'Pembayaran berhasil dicatat.');
    }
}

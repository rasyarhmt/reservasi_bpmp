<?php

namespace App\Http\Controllers;

use App\Models\Hall;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HallController extends Controller
{
    /**
     * Detail internal aula (komponen perhitungan & inventaris) — hanya untuk admin.
     */
    public function show(): View
    {
        return view('admin.halls.show', [
            'hall' => Hall::firstOrFail(),
        ]);
    }

    /**
     * Form edit komponen biaya aula — hanya untuk admin.
     */
    public function edit(): View
    {
        return view('admin.halls.form', [
            'hall' => Hall::firstOrFail(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $hall = Hall::firstOrFail();

        $data = $request->validate([
            'hall_name' => ['required', 'string', 'max:100'],
            'capacity' => ['required', 'integer', 'min:1'],
            'status' => ['required', 'in:available,in_use,maintenance'],
            'cost_components' => ['required', 'array', 'min:1'],
            'cost_components.*.label' => ['required', 'string', 'max:100'],
            'cost_components.*.amount' => ['required', 'numeric', 'min:0'],
        ]);

        $components = collect($data['cost_components'])
            ->mapWithKeys(fn(array $component) => [
                trim($component['label']) => round((float) $component['amount'], 2),
            ])
            ->all();

        $basePrice = round(array_sum($components), 2);

        $hall->update([
            'hall_name' => $data['hall_name'],
            'capacity' => $data['capacity'],
            'status' => $data['status'],
            'cost_components' => $components,
            'base_price' => $basePrice,
            'price_per_hour' => round($basePrice / Hall::OPERATIONAL_HOURS, 2),
        ]);

        return redirect()
            ->route('halls.show')
            ->with('success', "Aula {$hall->hall_name} berhasil diperbarui.");
    }
}

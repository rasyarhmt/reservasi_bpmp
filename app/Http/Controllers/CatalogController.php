<?php

namespace App\Http\Controllers;

use App\Models\Hall;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function index(Request $request): View
    {
        $withRelations = [
            'amenities',
            'reservations' => fn($query) => $query->whereIn('status', ['checked_in', 'booked'])
                ->where('check_out_date', '>=', now()->toDateString())
                ->orderBy('check_in_date')
        ];

        $rooms = $request->user()?->isAdmin()
            ? Room::with($withRelations)->orderBy('room_number')->paginate(10)->withQueryString()
            : Room::bookable()->with($withRelations)->orderBy('room_number')->paginate(10)->withQueryString();

        return view('catalog.index', [
            'rooms' => $rooms,
            'halls' => Hall::all(),
        ]);
    }
}
<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Delivery\DeliveryListService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class DeliveryListController extends Controller
{
    public function __construct(private DeliveryListService $deliveryLists) {}

    public function index(Request $request): Response
    {
        $date = $request->filled('date') ? Carbon::parse($request->string('date')) : now()->addDay();

        return Inertia::render('Admin/Delivery/Lists', [
            'date' => $date->toDateString(),
            'runs' => $this->deliveryLists->forDate($date),
        ]);
    }
}

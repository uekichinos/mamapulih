<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Kitchen\PrepSheetService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class PrepSheetController extends Controller
{
    public function __construct(private PrepSheetService $prepSheet) {}

    public function index(Request $request): Response
    {
        $date = $request->filled('date') ? Carbon::parse($request->string('date')) : now()->addDay();

        return Inertia::render('Admin/Kitchen/PrepSheet', [
            'date' => $date->toDateString(),
            'meals' => $this->prepSheet->forDate($date),
        ]);
    }
}

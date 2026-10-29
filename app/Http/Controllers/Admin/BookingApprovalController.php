<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Booking\BookingService;
use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class BookingApprovalController extends Controller
{
    public function __construct(private BookingService $bookings) {}

    public function index(): Response
    {
        $bookings = Booking::query()
            ->with(['customer', 'variant.package'])
            ->where('status', BookingStatus::PendingApproval)
            ->oldest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/BookingApprovals/Index', [
            'bookings' => $bookings,
        ]);
    }

    public function approve(Booking $booking): RedirectResponse
    {
        abort_unless($booking->status === BookingStatus::PendingApproval, 409);

        $this->bookings->approve($booking, Auth::guard('web')->user());

        return back()->with('status', 'approved');
    }

    public function reject(Request $request, Booking $booking): RedirectResponse
    {
        abort_unless($booking->status === BookingStatus::PendingApproval, 409);

        $data = $request->validate(['reason' => ['required', 'string', 'max:500']]);

        $this->bookings->reject($booking, Auth::guard('web')->user(), $data['reason']);

        return back()->with('status', 'rejected');
    }
}

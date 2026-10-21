<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Booking\BookingService;
use App\Domain\Capacity\CapacityFullException;
use App\Http\Controllers\Controller;
use App\Models\Addon;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Package;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class BookingController extends Controller
{
    public function __construct(private BookingService $bookings) {}

    public function index(): Response
    {
        $bookings = Booking::query()
            ->with(['customer', 'variant.package'])
            ->latest()
            ->paginate(20);

        return Inertia::render('Admin/Bookings/Index', [
            'bookings' => $bookings,
        ]);
    }

    public function show(Booking $booking): Response
    {
        $booking->load(['customer', 'variant.package', 'address.zone', 'addons.addon', 'approver']);

        return Inertia::render('Admin/Bookings/Show', [
            'booking' => $booking,
        ]);
    }

    public function create(): Response
    {
        $packages = Package::query()
            ->where('active', true)
            ->with(['variants' => fn ($q) => $q->where('active', true)])
            ->get();

        $addons = Addon::query()->where('active', true)->get();

        return Inertia::render('Admin/Bookings/Create', [
            'packages' => $packages,
            'addons' => $addons,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'customer.name' => ['required', 'string', 'max:255'],
            'customer.email' => ['required', 'email', 'max:255'],
            'customer.phone' => ['nullable', 'string', 'max:30'],
            'variant_id' => ['required', 'integer', 'exists:variants,id'],
            'expected_birth_type' => ['required', 'in:normal,csec'],
            'is_twins' => ['boolean'],
            'edd' => ['required', 'date', 'after_or_equal:today'],
            'address.line1' => ['required', 'string', 'max:255'],
            'address.line2' => ['nullable', 'string', 'max:255'],
            'address.postcode' => ['required', 'string', 'max:10'],
            'address.notes' => ['nullable', 'string', 'max:255'],
            'diet.allergies' => ['array'],
            'diet.exclusions' => ['array'],
            'diet.notes' => ['nullable', 'string', 'max:1000'],
            'addons' => ['array'],
            'addons.*.addon_id' => ['required', 'integer', 'exists:addons,id'],
            'addons.*.qty' => ['required', 'integer', 'min:1'],
        ]);

        $customer = Customer::query()->firstOrCreate(
            ['email' => $data['customer']['email']],
            ['name' => $data['customer']['name'], 'phone' => $data['customer']['phone'] ?? null],
        );

        try {
            $booking = $this->bookings->submit($customer, $data, Auth::guard('web')->user());
        } catch (CapacityFullException $e) {
            return back()->withErrors(['edd' => $e->getMessage()])->withInput();
        }

        return redirect()->route('admin.bookings.show', $booking)->with('status', 'created');
    }
}

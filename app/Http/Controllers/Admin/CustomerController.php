<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Inertia\Inertia;
use Inertia\Response;

class CustomerController extends Controller
{
    public function index(): Response
    {
        $customers = Customer::query()
            ->withCount('bookings')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/Customers/Index', [
            'customers' => $customers,
        ]);
    }

    public function show(Customer $customer): Response
    {
        $customer->load([
            'addresses' => fn ($query) => $query->with('zone')->latest(),
            'bookings' => fn ($query) => $query->with(['variant.package', 'dietProfile'])->latest(),
        ]);

        return Inertia::render('Admin/Customers/Show', [
            'customer' => $customer,
        ]);
    }
}

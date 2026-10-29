<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Inertia\Inertia;
use Inertia\Response;

class AuditLogController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/AuditLog/Index', [
            'logs' => AuditLog::query()
                ->with('actor:id,name')
                ->latest('created_at')
                ->paginate(20)
                ->withQueryString(),
        ]);
    }
}

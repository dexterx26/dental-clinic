<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::orderBy('category')->orderBy('name')->get();
        return view('services.index', compact('services'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'service_code' => 'required|string|unique:services,service_code',
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'standard_price' => 'required|numeric|min:0',
            'duration_minutes' => 'required|integer|min:5',
            'description' => 'nullable|string',
        ]);

        $service = Service::create($validated);

        AuditLog::log('service_created', "Created new service {$service->name} (₱" . number_format($service->standard_price, 2) . ")", Service::class, $service->id);

        return back()->with('success', "Service {$service->name} added successfully.");
    }

    public function update(Request $request, Service $service)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'standard_price' => 'required|numeric|min:0',
            'duration_minutes' => 'required|integer|min:5',
            'description' => 'nullable|string',
            'is_active' => 'required|boolean',
        ]);

        $service->update($validated);

        AuditLog::log('service_updated', "Updated service {$service->name}.", Service::class, $service->id);

        return back()->with('success', "Service {$service->name} updated successfully.");
    }
}

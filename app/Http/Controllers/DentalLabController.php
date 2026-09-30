<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\DentalLabCase;
use App\Models\Patient;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DentalLabController extends Controller
{
    public function index(Request $request)
    {
        $query = DentalLabCase::with(['patient', 'dentist'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('dentist_id')) {
            $query->where('dentist_id', $request->dentist_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('case_number', 'like', "%{$search}%")
                  ->orWhere('lab_name', 'like', "%{$search}%")
                  ->orWhere('appliance_type', 'like', "%{$search}%")
                  ->orWhereHas('patient', function ($pq) use ($search) {
                      $pq->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('patient_number', 'like', "%{$search}%");
                  });
            });
        }

        $labCases = $query->paginate(15)->withQueryString();

        // Metrics
        $totalCases = DentalLabCase::count();
        $inProgressCount = DentalLabCase::whereIn('status', ['sent', 'in_progress'])->count();
        $deliveredCount = DentalLabCase::where('status', 'delivered')->count();
        $fittedCount = DentalLabCase::where('status', 'fitted')->count();

        $dentists = User::where('role', 'dentist')->orderBy('name')->get();

        return view('lab_cases.index', compact('labCases', 'totalCases', 'inProgressCount', 'deliveredCount', 'fittedCount', 'dentists'));
    }

    public function create(Request $request)
    {
        $selectedPatient = null;
        if ($request->filled('patient_id')) {
            $selectedPatient = Patient::find($request->patient_id);
        }

        $patients = Patient::orderBy('last_name')->get();
        $dentists = User::where('role', 'dentist')->orderBy('name')->get();

        $applianceTypes = [
            'Crown - Porcelain Fused to Metal (PFM)',
            'Crown - Full Zirconia',
            'Crown - E-Max Ceramic',
            'Bridge - 3-Unit Porcelain',
            'Bridge - Zirconia Multi-Unit',
            'Denture - Complete Acrylic Upper/Lower',
            'Denture - Removable Partial Denture (RPD)',
            'Denture - Flexible Valplast',
            'Implant Custom Abutment & Crown',
            'Orthodontic Hawley Retainer',
            'Orthodontic Clear Aligner',
            'Occlusal Night Guard / Splint',
            'Porcelain Laminate Veneer',
            'Custom Inlay / Onlay',
            'Other Appliance'
        ];

        return view('lab_cases.create', compact('patients', 'dentists', 'selectedPatient', 'applianceTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'dentist_id' => 'required|exists:users,id',
            'lab_name' => 'required|string|max:255',
            'technician_name' => 'nullable|string|max:255',
            'appliance_type' => 'required|string|max:255',
            'tooth_number' => 'nullable|string|max:50',
            'shade' => 'nullable|string|max:50',
            'sent_date' => 'required|date',
            'expected_delivery_date' => 'required|date|after_or_equal:sent_date',
            'cost' => 'nullable|numeric|min:0',
            'instructions' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $caseNumber = 'LAB-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

        $validated['case_number'] = $caseNumber;
        $validated['cost'] = $validated['cost'] ?? 0;
        $validated['created_by_id'] = auth()->id();
        $validated['status'] = 'sent';

        $labCase = DentalLabCase::create($validated);

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'lab_case_created',
            'model_type' => DentalLabCase::class,
            'model_id' => $labCase->id,
            'description' => "Logged laboratory case {$labCase->case_number} ({$labCase->appliance_type}) to {$labCase->lab_name}",
            'new_values' => $labCase->toArray(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return redirect()->route('lab-cases.show', $labCase)->with('success', "Lab case {$labCase->case_number} registered successfully.");
    }

    public function show(DentalLabCase $labCase)
    {
        $labCase->load(['patient', 'dentist', 'createdBy']);
        return view('lab_cases.show', compact('labCase'));
    }

    public function updateStatus(Request $request, DentalLabCase $labCase)
    {
        $validated = $request->validate([
            'status' => 'required|in:sent,in_progress,delivered,fitted,adjustment_needed,rejected,cancelled',
            'actual_delivery_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $updates = ['status' => $validated['status']];
        if (in_array($validated['status'], ['delivered', 'fitted']) && !$labCase->actual_delivery_date) {
            $updates['actual_delivery_date'] = $validated['actual_delivery_date'] ?? Carbon::today();
        }
        if (!empty($validated['notes'])) {
            $updates['notes'] = ($labCase->notes ? $labCase->notes . "\n" : "") . "[" . Carbon::now()->format('Y-m-d H:i') . "] Status changed to " . $validated['status'] . ": " . $validated['notes'];
        }

        $labCase->update($updates);

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'lab_case_status_updated',
            'model_type' => DentalLabCase::class,
            'model_id' => $labCase->id,
            'description' => "Lab case {$labCase->case_number} status changed to {$validated['status']}",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return back()->with('success', "Lab case status updated to " . ucfirst(str_replace('_', ' ', $validated['status'])) . ".");
    }
}

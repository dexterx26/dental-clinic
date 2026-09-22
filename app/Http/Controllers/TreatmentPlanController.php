<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Patient;
use App\Models\Service;
use App\Models\TreatmentPlan;
use App\Models\TreatmentPlanItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TreatmentPlanController extends Controller
{
    public function create(Patient $patient)
    {
        $dentists = User::where('role', 'dentist')->where('is_active', true)->get();
        $services = Service::where('is_active', true)->orderBy('name')->get();

        return view('treatment_plans.create', compact('patient', 'dentists', 'services'));
    }

    public function store(Request $request, Patient $patient)
    {
        $validated = $request->validate([
            'dentist_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'diagnosis' => 'nullable|string',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.procedure_name' => 'required|string',
            'items.*.service_id' => 'nullable|exists:services,id',
            'items.*.tooth_number' => 'nullable|string',
            'items.*.surface' => 'nullable|string',
            'items.*.estimated_cost' => 'required|numeric|min:0',
            'items.*.priority' => 'required|in:high,medium,low',
            'items.*.sessions_required' => 'required|integer|min:1',
            'items.*.notes' => 'nullable|string',
        ]);

        $year = date('Y');
        $count = TreatmentPlan::whereYear('created_at', $year)->count() + 1;
        $planNumber = sprintf('TRP-%s-%04d', $year, $count);

        $totalEstimatedCost = 0;
        foreach ($validated['items'] as $item) {
            $totalEstimatedCost += (float)$item['estimated_cost'];
        }

        $plan = TreatmentPlan::create([
            'plan_number' => $planNumber,
            'patient_id' => $patient->id,
            'dentist_id' => $validated['dentist_id'],
            'title' => $validated['title'],
            'diagnosis' => $validated['diagnosis'] ?? null,
            'total_estimated_cost' => $totalEstimatedCost,
            'status' => 'proposed',
            'notes' => $validated['notes'] ?? null,
        ]);

        foreach ($validated['items'] as $itemData) {
            TreatmentPlanItem::create([
                'treatment_plan_id' => $plan->id,
                'service_id' => $itemData['service_id'] ?? null,
                'procedure_name' => $itemData['procedure_name'],
                'tooth_number' => $itemData['tooth_number'] ?? null,
                'surface' => $itemData['surface'] ?? null,
                'estimated_cost' => $itemData['estimated_cost'],
                'priority' => $itemData['priority'],
                'sessions_required' => $itemData['sessions_required'],
                'status' => 'pending',
                'notes' => $itemData['notes'] ?? null,
            ]);
        }

        AuditLog::log('treatment_plan_created', "Created treatment plan {$plan->plan_number} for {$patient->full_name} (Total: ₱" . number_format($totalEstimatedCost, 2) . ")", TreatmentPlan::class, $plan->id);

        return redirect()->route('treatment-plans.show', $plan)->with('success', "Treatment Plan {$plan->plan_number} created successfully.");
    }

    public function show(TreatmentPlan $treatmentPlan)
    {
        $treatmentPlan->load(['patient', 'dentist', 'items.service', 'items.treatment']);
        return view('treatment_plans.show', compact('treatmentPlan'));
    }

    public function updateStatus(Request $request, TreatmentPlan $treatmentPlan)
    {
        $validated = $request->validate([
            'status' => 'required|in:proposed,presented,accepted,partially_completed,completed,declined,cancelled',
            'patient_decision' => 'nullable|string|max:500',
        ]);

        $treatmentPlan->status = $validated['status'];
        if (!empty($validated['patient_decision'])) {
            $treatmentPlan->patient_decision = $validated['patient_decision'];
            $treatmentPlan->decision_date = Carbon::today();
        }
        $treatmentPlan->save();

        AuditLog::log('treatment_plan_status', "Treatment plan {$treatmentPlan->plan_number} marked as {$treatmentPlan->status}.", TreatmentPlan::class, $treatmentPlan->id);

        return back()->with('success', "Treatment plan updated to " . ucfirst($treatmentPlan->status) . ".");
    }
}

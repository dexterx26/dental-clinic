<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\AuditLog;
use App\Models\Patient;
use App\Models\Treatment;
use App\Models\TreatmentPlanItem;
use App\Models\User;
use Illuminate\Http\Request;

class TreatmentRecordController extends Controller
{
    public function create(Patient $patient, Request $request)
    {
        $dentists = User::where('role', 'dentist')->where('is_active', true)->get();
        $planItemId = $request->query('plan_item_id');
        $planItem = $planItemId ? TreatmentPlanItem::find($planItemId) : null;
        $appointmentId = $request->query('appointment_id');
        $appointment = $appointmentId ? Appointment::find($appointmentId) : null;

        return view('treatments.create', compact('patient', 'dentists', 'planItem', 'appointment'));
    }

    public function store(Request $request, Patient $patient)
    {
        $validated = $request->validate([
            'dentist_id' => 'required|exists:users,id',
            'appointment_id' => 'nullable|exists:appointments,id',
            'treatment_plan_item_id' => 'nullable|exists:treatment_plan_items,id',
            'procedure_name' => 'required|string|max:255',
            'tooth_number' => 'nullable|string|max:50',
            'surface' => 'nullable|string|max:50',
            'diagnosis' => 'nullable|string',
            'procedure_notes' => 'required|string',
            'materials_used' => 'nullable|string',
            'anesthesia' => 'nullable|string|max:255',
            'complications' => 'nullable|string',
            'follow_up_instructions' => 'nullable|string',
            'cost' => 'required|numeric|min:0',
        ]);

        $year = date('Y');
        $count = Treatment::whereYear('created_at', $year)->count() + 1;
        $trtNumber = sprintf('TRT-%s-%04d', $year, $count);

        $treatment = Treatment::create([
            'treatment_number' => $trtNumber,
            'patient_id' => $patient->id,
            'dentist_id' => $validated['dentist_id'],
            'appointment_id' => $validated['appointment_id'] ?? null,
            'treatment_plan_item_id' => $validated['treatment_plan_item_id'] ?? null,
            'procedure_name' => $validated['procedure_name'],
            'tooth_number' => $validated['tooth_number'] ?? null,
            'surface' => $validated['surface'] ?? null,
            'diagnosis' => $validated['diagnosis'] ?? null,
            'procedure_notes' => $validated['procedure_notes'],
            'materials_used' => $validated['materials_used'] ?? null,
            'anesthesia' => $validated['anesthesia'] ?? null,
            'complications' => $validated['complications'] ?? null,
            'follow_up_instructions' => $validated['follow_up_instructions'] ?? null,
            'cost' => $validated['cost'],
            'payment_status' => 'unpaid',
        ]);

        if (!empty($validated['treatment_plan_item_id'])) {
            $planItem = TreatmentPlanItem::find($validated['treatment_plan_item_id']);
            if ($planItem) {
                $planItem->update(['status' => 'completed']);
            }
        }

        AuditLog::log('procedure_executed', "Recorded clinical procedure {$treatment->treatment_number} ({$treatment->procedure_name}) for patient {$patient->full_name}.", Treatment::class, $treatment->id);

        return redirect()->route('treatments.show', $treatment)->with('success', "Procedure record {$treatment->treatment_number} saved successfully.");
    }

    public function show(Treatment $treatment)
    {
        $treatment->load(['patient', 'dentist', 'appointment', 'treatmentPlanItem']);
        return view('treatments.show', compact('treatment'));
    }
}

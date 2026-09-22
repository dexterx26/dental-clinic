<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Patient;
use App\Models\PatientMedicalHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MedicalHistoryController extends Controller
{
    public function update(Request $request, Patient $patient)
    {
        $validated = $request->validate([
            'conditions' => 'nullable|array',
            'conditions.*' => 'nullable|string',
            'custom_condition' => 'nullable|string|max:255',
            'allergies' => 'nullable|string|max:1000',
            'current_medications' => 'nullable|string|max:1000',
            'past_surgeries' => 'nullable|string|max:1000',
            'family_history' => 'nullable|string|max:1000',
            'lifestyle_notes' => 'nullable|string|max:1000',
        ]);

        $conditions = $validated['conditions'] ?? [];
        if (!empty($validated['custom_condition'])) {
            $conditions['Other'] = $validated['custom_condition'];
        }

        $medicalHistory = $patient->medicalHistory ?: new PatientMedicalHistory(['patient_id' => $patient->id]);
        $oldValues = $medicalHistory->toArray();

        $medicalHistory->fill([
            'conditions' => $conditions,
            'allergies' => $validated['allergies'] ?? null,
            'current_medications' => $validated['current_medications'] ?? null,
            'past_surgeries' => $validated['past_surgeries'] ?? null,
            'family_history' => $validated['family_history'] ?? null,
            'lifestyle_notes' => $validated['lifestyle_notes'] ?? null,
            'recorded_by_id' => Auth::id(),
        ]);
        $medicalHistory->save();

        AuditLog::log('medical_history_updated', "Updated medical history for patient {$patient->full_name}", PatientMedicalHistory::class, $medicalHistory->id, $oldValues, $medicalHistory->toArray());

        return redirect()->route('patients.show', ['patient' => $patient, 'tab' => 'medical'])->with('success', 'Medical history updated successfully.');
    }
}

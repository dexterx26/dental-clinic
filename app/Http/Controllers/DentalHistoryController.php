<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Patient;
use App\Models\PatientDentalHistory;
use Illuminate\Http\Request;

class DentalHistoryController extends Controller
{
    public function update(Request $request, Patient $patient)
    {
        $validated = $request->validate([
            'previous_dentist' => 'nullable|string|max:255',
            'last_dental_visit' => 'nullable|date',
            'past_treatments' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $dentalHistory = $patient->dentalHistory ?: new PatientDentalHistory(['patient_id' => $patient->id]);
        $oldValues = $dentalHistory->toArray();

        $dentalHistory->fill($validated);
        $dentalHistory->save();

        AuditLog::log('dental_history_updated', "Updated prior dental history for patient {$patient->full_name}", PatientDentalHistory::class, $dentalHistory->id, $oldValues, $dentalHistory->toArray());

        return redirect()->route('patients.show', ['patient' => $patient, 'tab' => 'dental-history'])->with('success', 'Dental history updated successfully.');
    }
}

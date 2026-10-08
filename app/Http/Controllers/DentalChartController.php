<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\DentalChart;
use App\Models\DentalChartHistory;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DentalChartController extends Controller
{
    public function update(Request $request, Patient $patient)
    {
        $validated = $request->validate([
            'tooth_number' => 'required|integer',
            'surface' => 'required|string|in:whole,occlusal,mesial,distal,buccal,lingual,incisal',
            'condition' => 'required|string|in:healthy,caries,filled,crown,bridge,implant,root_canal,missing,extracted,fractured,impacted,decayed,mobile,veneer,sealant',
            'notes' => 'nullable|string|max:500',
        ]);

        $existing = DentalChart::where('patient_id', $patient->id)
            ->where('tooth_number', $validated['tooth_number'])
            ->where('surface', $validated['surface'])
            ->first();

        $previousCondition = $existing ? $existing->condition : 'healthy';

        // Update or create current status
        $chart = DentalChart::updateOrCreate(
            [
                'patient_id' => $patient->id,
                'tooth_number' => $validated['tooth_number'],
                'surface' => $validated['surface'],
            ],
            [
                'condition' => $validated['condition'],
                'notes' => $validated['notes'] ?? null,
                'updated_by_id' => Auth::id(),
            ]
        );

        // Always preserve history snapshot
        DentalChartHistory::create([
            'patient_id' => $patient->id,
            'tooth_number' => $validated['tooth_number'],
            'surface' => $validated['surface'],
            'previous_condition' => $previousCondition,
            'new_condition' => $validated['condition'],
            'notes' => $validated['notes'] ?? null,
            'changed_by_id' => Auth::id(),
        ]);

        AuditLog::log(
            'odontogram_updated',
            "Updated Odontogram: Tooth #{$validated['tooth_number']} ({$validated['surface']}) changed from {$previousCondition} to {$validated['condition']} for patient {$patient->full_name}.",
            DentalChart::class,
            $chart->id
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Tooth #{$validated['tooth_number']} updated to {$validated['condition']}.",
                'chart' => $chart,
            ]);
        }

        return redirect()->route('patients.show', ['patient' => $patient, 'tab' => 'odontogram'])->with('success', "Tooth #{$validated['tooth_number']} condition updated.");
    }

    public function history(Patient $patient)
    {
        $histories = DentalChartHistory::with('changedBy')
            ->where('patient_id', $patient->id)
            ->latest()
            ->get();

        return response()->json(['histories' => $histories]);
    }

    public function compare(Patient $patient)
    {
        $patient->load([
            'preferredDentist',
            'dentalCharts.updatedBy',
            'dentalChartHistories.changedBy',
        ]);

        return view('patients.odontogram_compare', compact('patient'));
    }
}

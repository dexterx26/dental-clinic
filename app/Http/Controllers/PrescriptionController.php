<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\AuditLog;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PrescriptionController extends Controller
{
    public function create(Patient $patient, Request $request)
    {
        $dentists = User::where('role', 'dentist')->where('is_active', true)->get();
        $appointmentId = $request->query('appointment_id');
        $appointment = $appointmentId ? Appointment::find($appointmentId) : null;

        return view('prescriptions.create', compact('patient', 'dentists', 'appointment'));
    }

    public function store(Request $request, Patient $patient)
    {
        $validated = $request->validate([
            'dentist_id' => 'required|exists:users,id',
            'appointment_id' => 'nullable|exists:appointments,id',
            'prescription_date' => 'required|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.medication_name' => 'required|string',
            'items.*.dosage' => 'required|string',
            'items.*.frequency' => 'required|string',
            'items.*.duration' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.instructions' => 'nullable|string',
        ]);

        $year = date('Y');
        $count = Prescription::whereYear('created_at', $year)->count() + 1;
        $rxNumber = sprintf('RX-%s-%04d', $year, $count);

        $prescription = Prescription::create([
            'rx_number' => $rxNumber,
            'patient_id' => $patient->id,
            'dentist_id' => $validated['dentist_id'],
            'appointment_id' => $validated['appointment_id'] ?? null,
            'prescription_date' => $validated['prescription_date'],
            'notes' => $validated['notes'] ?? null,
        ]);

        foreach ($validated['items'] as $itemData) {
            PrescriptionItem::create([
                'prescription_id' => $prescription->id,
                'medication_name' => $itemData['medication_name'],
                'dosage' => $itemData['dosage'],
                'frequency' => $itemData['frequency'],
                'duration' => $itemData['duration'],
                'quantity' => $itemData['quantity'],
                'instructions' => $itemData['instructions'] ?? null,
            ]);
        }

        AuditLog::log('prescription_issued', "Issued prescription {$prescription->rx_number} for {$patient->full_name}.", Prescription::class, $prescription->id);

        return redirect()->route('prescriptions.show', $prescription)->with('success', "Prescription {$prescription->rx_number} created successfully.");
    }

    public function show(Prescription $prescription)
    {
        $prescription->load(['patient', 'dentist', 'items', 'appointment']);
        return view('prescriptions.show', compact('prescription'));
    }
}

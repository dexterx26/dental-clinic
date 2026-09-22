<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\AuditLog;
use App\Models\Examination;
use App\Models\Patient;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExaminationController extends Controller
{
    public function create(Patient $patient, Request $request)
    {
        $dentists = User::where('role', 'dentist')->where('is_active', true)->get();
        $appointmentId = $request->query('appointment_id');
        $appointment = $appointmentId ? Appointment::find($appointmentId) : null;

        return view('examinations.create', compact('patient', 'dentists', 'appointment'));
    }

    public function store(Request $request, Patient $patient)
    {
        $validated = $request->validate([
            'dentist_id' => 'required|exists:users,id',
            'appointment_id' => 'nullable|exists:appointments,id',
            'exam_date' => 'required|date',
            'chief_complaint' => 'nullable|string',
            'history_of_present_complaint' => 'nullable|string',
            'clinical_findings' => 'nullable|string',
            'diagnosis' => 'required|string',
            'treatment_recommendations' => 'nullable|string',
            'clinical_notes' => 'nullable|string',
            'oral_exam' => 'nullable|array',
        ]);

        $year = date('Y');
        $count = Examination::whereYear('created_at', $year)->count() + 1;
        $examNumber = sprintf('EXAM-%s-%04d', $year, $count);

        $examination = Examination::create([
            'examination_number' => $examNumber,
            'patient_id' => $patient->id,
            'dentist_id' => $validated['dentist_id'],
            'appointment_id' => $validated['appointment_id'] ?? null,
            'exam_date' => $validated['exam_date'],
            'chief_complaint' => $validated['chief_complaint'] ?? null,
            'history_of_present_complaint' => $validated['history_of_present_complaint'] ?? null,
            'clinical_findings' => $validated['clinical_findings'] ?? null,
            'diagnosis' => $validated['diagnosis'],
            'treatment_recommendations' => $validated['treatment_recommendations'] ?? null,
            'oral_exam_findings' => $validated['oral_exam'] ?? [],
            'clinical_notes' => $validated['clinical_notes'] ?? null,
        ]);

        AuditLog::log('examination_created', "Recorded examination {$examination->examination_number} for patient {$patient->full_name}", Examination::class, $examination->id);

        return redirect()->route('examinations.show', $examination)->with('success', 'Dental examination recorded successfully.');
    }

    public function show(Examination $examination)
    {
        $examination->load(['patient', 'dentist', 'appointment']);
        return view('examinations.show', compact('examination'));
    }
}

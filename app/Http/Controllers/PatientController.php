<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Patient;
use App\Models\PatientDentalHistory;
use App\Models\PatientMedicalHistory;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PatientController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status');

        $query = Patient::with(['preferredDentist', 'medicalHistory']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('patient_number', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        $patients = $query->orderBy('created_at', 'desc')->paginate(12)->withQueryString();

        return view('patients.index', compact('patients', 'search', 'status'));
    }

    public function create()
    {
        $dentists = User::where('role', 'dentist')->where('is_active', true)->get();
        return view('patients.create', compact('dentists'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'last_name' => 'required|string|max:100',
            'dob' => 'required|date',
            'gender' => 'required|in:Male,Female,Other',
            'civil_status' => 'nullable|string|max:50',
            'nationality' => 'nullable|string|max:50',
            'occupation' => 'nullable|string|max:100',
            'phone' => 'required|string|max:30',
            'email' => 'nullable|email|max:100',
            'address' => 'nullable|string|max:500',
            'emergency_contact_name' => 'nullable|string|max:100',
            'emergency_contact_phone' => 'nullable|string|max:30',
            'emergency_contact_relationship' => 'nullable|string|max:50',
            'preferred_dentist_id' => 'nullable|exists:users,id',
            'referral_source' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'privacy_consent' => 'required|accepted',
            'photo' => 'nullable|image|max:2048',
        ]);

        // Auto-generate patient number PAT-YYYY-0001
        $year = date('Y');
        $count = Patient::whereYear('created_at', $year)->count() + 1;
        $patientNumber = sprintf('PAT-%s-%04d', $year, $count);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('patient_photos', 'public');
        }

        $age = Carbon::parse($validated['dob'])->age;

        $patient = Patient::create([
            'patient_number' => $patientNumber,
            'first_name' => $validated['first_name'],
            'middle_name' => $validated['middle_name'] ?? null,
            'last_name' => $validated['last_name'],
            'dob' => $validated['dob'],
            'age' => $age,
            'gender' => $validated['gender'],
            'civil_status' => $validated['civil_status'] ?? null,
            'nationality' => $validated['nationality'] ?? 'Filipino',
            'occupation' => $validated['occupation'] ?? null,
            'phone' => $validated['phone'],
            'email' => $validated['email'] ?? null,
            'address' => $validated['address'] ?? null,
            'emergency_contact_name' => $validated['emergency_contact_name'] ?? null,
            'emergency_contact_phone' => $validated['emergency_contact_phone'] ?? null,
            'emergency_contact_relationship' => $validated['emergency_contact_relationship'] ?? null,
            'preferred_dentist_id' => $validated['preferred_dentist_id'] ?? null,
            'referral_source' => $validated['referral_source'] ?? null,
            'photo' => $photoPath,
            'status' => 'active',
            'privacy_consent_accepted' => true,
            'privacy_consent_date' => Carbon::now(),
            'registration_date' => Carbon::today(),
            'notes' => $validated['notes'] ?? null,
        ]);

        // Initialize Medical and Dental histories
        PatientMedicalHistory::create([
            'patient_id' => $patient->id,
            'conditions' => [],
            'recorded_by_id' => Auth::id(),
        ]);

        PatientDentalHistory::create([
            'patient_id' => $patient->id,
        ]);

        AuditLog::log('patient_created', "Registered patient {$patient->full_name} ({$patient->patient_number})", Patient::class, $patient->id);

        return redirect()->route('patients.show', $patient)->with('success', "Patient {$patient->full_name} registered successfully!");
    }

    public function show(Patient $patient)
    {
        $patient->load([
            'preferredDentist',
            'medicalHistory',
            'dentalHistory',
            'appointments.dentist',
            'appointments.service',
            'dentalCharts.updatedBy',
            'dentalChartHistories.changedBy',
            'examinations.dentist',
            'treatmentPlans.dentist',
            'treatmentPlans.items.service',
            'treatments.dentist',
            'prescriptions.dentist',
            'prescriptions.items',
            'documents.uploadedBy',
            'invoices.items',
            'invoices.payments',
            'payments.cashier',
            'followUps.dentist',
        ]);

        $dentists = User::where('role', 'dentist')->where('is_active', true)->get();
        $services = Service::where('is_active', true)->orderBy('name')->get();

        return view('patients.show', compact('patient', 'dentists', 'services'));
    }

    public function edit(Patient $patient)
    {
        $dentists = User::where('role', 'dentist')->where('is_active', true)->get();
        return view('patients.edit', compact('patient', 'dentists'));
    }

    public function update(Request $request, Patient $patient)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'last_name' => 'required|string|max:100',
            'dob' => 'required|date',
            'gender' => 'required|in:Male,Female,Other',
            'civil_status' => 'nullable|string|max:50',
            'nationality' => 'nullable|string|max:50',
            'occupation' => 'nullable|string|max:100',
            'phone' => 'required|string|max:30',
            'email' => 'nullable|email|max:100',
            'address' => 'nullable|string|max:500',
            'emergency_contact_name' => 'nullable|string|max:100',
            'emergency_contact_phone' => 'nullable|string|max:30',
            'emergency_contact_relationship' => 'nullable|string|max:50',
            'preferred_dentist_id' => 'nullable|exists:users,id',
            'referral_source' => 'nullable|string|max:100',
            'status' => 'required|in:active,inactive',
            'notes' => 'nullable|string',
            'photo' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('photo')) {
            if ($patient->photo) {
                Storage::disk('public')->delete($patient->photo);
            }
            $validated['photo'] = $request->file('photo')->store('patient_photos', 'public');
        }

        $validated['age'] = Carbon::parse($validated['dob'])->age;

        $oldValues = $patient->toArray();
        $patient->update($validated);

        AuditLog::log('patient_updated', "Updated profile for patient {$patient->full_name}", Patient::class, $patient->id, $oldValues, $patient->toArray());

        return redirect()->route('patients.show', $patient)->with('success', 'Patient details updated successfully.');
    }

    public function destroy(Patient $patient)
    {
        $name = $patient->full_name;
        $id = $patient->id;
        $patient->delete();

        AuditLog::log('patient_deleted', "Soft-deleted patient record {$name}", Patient::class, $id);

        return redirect()->route('patients.index')->with('success', "Patient {$name} was archived successfully.");
    }
}

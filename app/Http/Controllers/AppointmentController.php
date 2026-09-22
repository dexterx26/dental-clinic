<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\AuditLog;
use App\Models\DentistSchedule;
use App\Models\Patient;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->input('date', Carbon::today()->toDateString());
        $dentistId = $request->input('dentist_id');
        $status = $request->input('status');

        $query = Appointment::with(['patient', 'dentist', 'service']);

        if ($date) {
            $query->where('appointment_date', $date);
        }

        if ($dentistId) {
            $query->where('dentist_id', $dentistId);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $appointments = $query->orderBy('start_time', 'asc')->paginate(20)->withQueryString();
        $dentists = User::where('role', 'dentist')->where('is_active', true)->get();

        return view('appointments.index', compact('appointments', 'dentists', 'date', 'dentistId', 'status'));
    }

    public function create(Request $request)
    {
        $patientId = $request->input('patient_id');
        $patient = $patientId ? Patient::find($patientId) : null;
        $dentists = User::where('role', 'dentist')->where('is_active', true)->get();
        $services = Service::where('is_active', true)->orderBy('name')->get();
        $patients = Patient::where('status', 'active')->orderBy('last_name')->get();

        return view('appointments.create', compact('patient', 'patients', 'dentists', 'services'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'dentist_id' => 'required|exists:users,id',
            'service_id' => 'nullable|exists:services,id',
            'appointment_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'reason' => 'nullable|string|max:500',
            'notes' => 'nullable|string',
            'allow_overlap' => 'nullable|boolean',
        ]);

        $dentist = User::findOrFail($validated['dentist_id']);
        $dayOfWeek = Carbon::parse($validated['appointment_date'])->dayOfWeek;

        // Verify dentist schedule availability for this day of the week
        $schedule = DentistSchedule::where('dentist_id', $dentist->id)
            ->where('day_of_week', $dayOfWeek)
            ->where('is_available', true)
            ->first();

        if (!$schedule && !$request->boolean('allow_overlap')) {
            return back()->withInput()->withErrors([
                'dentist_id' => "{$dentist->name} is not scheduled to work on " . Carbon::parse($validated['appointment_date'])->format('l') . "s.",
            ]);
        }

        // Prevent double booking
        $conflict = Appointment::where('dentist_id', $dentist->id)
            ->where('appointment_date', $validated['appointment_date'])
            ->whereNotIn('status', ['cancelled', 'no_show'])
            ->where(function ($q) use ($validated) {
                $q->whereBetween('start_time', [$validated['start_time'], $validated['end_time']])
                  ->orWhereBetween('end_time', [$validated['start_time'], $validated['end_time']])
                  ->orWhere(function ($sub) use ($validated) {
                      $sub->where('start_time', '<=', $validated['start_time'])
                          ->where('end_time', '>=', $validated['end_time']);
                  });
            })
            ->first();

        if ($conflict && !$request->boolean('allow_overlap')) {
            return back()->withInput()->withErrors([
                'start_time' => "Double booking conflict: {$dentist->name} already has an appointment ({$conflict->appointment_number}) from " . date('h:i A', strtotime($conflict->start_time)) . " to " . date('h:i A', strtotime($conflict->end_time)) . ".",
            ]);
        }

        $year = date('Y');
        $count = Appointment::whereYear('created_at', $year)->count() + 1;
        $aptNumber = sprintf('APT-%s-%04d', $year, $count);

        $appointment = Appointment::create([
            'appointment_number' => $aptNumber,
            'patient_id' => $validated['patient_id'],
            'dentist_id' => $validated['dentist_id'],
            'service_id' => $validated['service_id'] ?? null,
            'appointment_date' => $validated['appointment_date'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'status' => 'scheduled',
            'reason' => $validated['reason'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        AuditLog::log('appointment_booked', "Booked appointment {$appointment->appointment_number} for {$appointment->patient->full_name} with {$dentist->name} on {$appointment->appointment_date}", Appointment::class, $appointment->id);

        return redirect()->route('appointments.index', ['date' => $appointment->appointment_date])->with('success', "Appointment {$appointment->appointment_number} scheduled successfully.");
    }

    public function show(Appointment $appointment)
    {
        $appointment->load(['patient', 'dentist', 'service', 'examination', 'treatments', 'prescription', 'invoice']);
        return view('appointments.show', compact('appointment'));
    }

    public function updateStatus(Request $request, Appointment $appointment)
    {
        $validated = $request->validate([
            'status' => 'required|in:scheduled,confirmed,checked_in,waiting,in_consultation,in_treatment,completed,cancelled,no_show,rescheduled',
            'notes' => 'nullable|string',
        ]);

        $oldStatus = $appointment->status;
        $appointment->status = $validated['status'];

        // If checking in or putting into queue, assign queue number if not already present
        if (in_array($validated['status'], ['checked_in', 'waiting', 'in_treatment']) && !$appointment->queue_number) {
            $maxQueue = Appointment::where('appointment_date', $appointment->appointment_date)->max('queue_number') ?? 0;
            $appointment->queue_number = $maxQueue + 1;
            $appointment->checked_in_at = Carbon::now();
        }

        if (!empty($validated['notes'])) {
            $appointment->notes = $appointment->notes ? ($appointment->notes . "\n" . $validated['notes']) : $validated['notes'];
        }

        $appointment->save();

        AuditLog::log(
            'appointment_status_changed',
            "Appointment {$appointment->appointment_number} status changed from {$oldStatus} to {$appointment->status}.",
            Appointment::class,
            $appointment->id
        );

        return back()->with('success', "Appointment status changed to " . ucwords(str_replace('_', ' ', $appointment->status)) . ".");
    }
}

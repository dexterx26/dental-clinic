<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\AuditLog;
use App\Models\Patient;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class QueueController extends Controller
{
    public function index(Request $request)
    {
        $today = Carbon::today()->toDateString();
        $dentistId = $request->input('dentist_id');

        $query = Appointment::with(['patient', 'dentist', 'service'])
            ->where('appointment_date', $today)
            ->whereNotNull('queue_number')
            ->whereIn('status', ['checked_in', 'waiting', 'in_consultation', 'in_treatment', 'completed', 'cancelled', 'no_show']);

        if ($dentistId) {
            $query->where('dentist_id', $dentistId);
        }

        $queue = $query->orderBy('queue_number', 'asc')->get();
        $dentists = User::where('role', 'dentist')->where('is_active', true)->get();

        // Candidates for check-in today who don't have queue numbers yet
        $unassignedAppointments = Appointment::with(['patient', 'dentist', 'service'])
            ->where('appointment_date', $today)
            ->whereNull('queue_number')
            ->whereIn('status', ['scheduled', 'confirmed'])
            ->orderBy('start_time', 'asc')
            ->get();

        return view('queue.index', compact('queue', 'dentists', 'unassignedAppointments', 'dentistId'));
    }

    public function checkIn(Request $request, Appointment $appointment)
    {
        $today = Carbon::today()->toDateString();
        $maxQueue = Appointment::where('appointment_date', $today)->max('queue_number') ?? 0;

        $appointment->update([
            'status' => 'waiting',
            'queue_number' => $maxQueue + 1,
            'checked_in_at' => Carbon::now(),
        ]);

        AuditLog::log('patient_checked_in', "Patient {$appointment->patient->full_name} checked in (Token #{$appointment->queue_number}) for Dr. {$appointment->dentist->name}.", Appointment::class, $appointment->id);

        return back()->with('success', "Patient {$appointment->patient->full_name} checked in! Queue Token #{$appointment->queue_number}");
    }
}

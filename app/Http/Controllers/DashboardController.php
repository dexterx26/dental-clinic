<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\AuditLog;
use App\Models\FollowUp;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\TreatmentPlan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $today = Carbon::today()->toDateString();
        $currentMonthStart = Carbon::now()->startOfMonth()->toDateString();

        // Common appointment metrics
        $todayAppointmentsQuery = Appointment::with(['patient', 'dentist', 'service'])
            ->where('appointment_date', $today);

        if ($user->isDentist()) {
            $todayAppointmentsQuery->where('dentist_id', $user->id);
        }

        $todayAppointments = (clone $todayAppointmentsQuery)->orderBy('start_time', 'asc')->get();
        $waitingCount = $todayAppointments->where('status', 'waiting')->count();
        $inTreatmentCount = $todayAppointments->where('status', 'in_treatment')->count();
        $completedTodayCount = $todayAppointments->where('status', 'completed')->count();

        // Front desk / Dentist live queue
        $queueAppointments = (clone $todayAppointmentsQuery)
            ->whereIn('status', ['waiting', 'in_consultation', 'in_treatment'])
            ->orderBy('queue_number', 'asc')
            ->get();

        // Financial metrics
        $todayRevenue = Payment::whereDate('payment_date', $today)->sum('amount');
        $monthRevenue = Payment::where('payment_date', '>=', $currentMonthStart)->sum('amount');
        $totalOutstanding = Invoice::whereIn('status', ['unpaid', 'partially_paid'])->sum('balance_amount');

        // Patient metrics
        $totalPatients = Patient::count();
        $newPatientsThisMonth = Patient::where('registration_date', '>=', $currentMonthStart)->count();

        // Dentist specific / Pending plans
        $pendingPlansQuery = TreatmentPlan::with(['patient', 'dentist'])
            ->whereIn('status', ['proposed', 'presented']);
        if ($user->isDentist()) {
            $pendingPlansQuery->where('dentist_id', $user->id);
        }
        $pendingPlans = $pendingPlansQuery->latest()->take(5)->get();

        // Recalls / Follow-ups due
        $dueRecalls = FollowUp::with(['patient', 'dentist'])
            ->where('status', 'pending')
            ->where('scheduled_date', '<=', Carbon::now()->addDays(7)->toDateString())
            ->orderBy('scheduled_date', 'asc')
            ->take(5)
            ->get();

        // Recent Audit logs (for Admin)
        $recentAudits = AuditLog::with('user')->latest()->take(8)->get();

        return view('dashboard.index', compact(
            'todayAppointments',
            'queueAppointments',
            'waitingCount',
            'inTreatmentCount',
            'completedTodayCount',
            'todayRevenue',
            'monthRevenue',
            'totalOutstanding',
            'totalPatients',
            'newPatientsThisMonth',
            'pendingPlans',
            'dueRecalls',
            'recentAudits'
        ));
    }
}

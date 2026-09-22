<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Treatment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());

        // Financial KPIs
        $totalBilled = Invoice::whereBetween('invoice_date', [$startDate, $endDate])->sum('total_amount');
        $totalCollected = Payment::whereBetween('payment_date', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])->sum('amount');
        $totalOutstanding = Invoice::whereBetween('invoice_date', [$startDate, $endDate])->sum('balance_amount');

        // Revenue by Payment Method
        $paymentsByMethod = Payment::whereBetween('payment_date', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->select('payment_method', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('payment_method')
            ->get();

        // Revenue by Dentist
        $revenueByDentist = Invoice::whereBetween('invoice_date', [$startDate, $endDate])
            ->whereNotNull('dentist_id')
            ->select('dentist_id', DB::raw('SUM(total_amount) as total_billed'), DB::raw('SUM(paid_amount) as total_collected'))
            ->with('dentist')
            ->groupBy('dentist_id')
            ->get();

        // Top Procedures performed
        $topProcedures = Treatment::whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->select('procedure_name', DB::raw('COUNT(*) as total_count'), DB::raw('SUM(cost) as total_revenue'))
            ->groupBy('procedure_name')
            ->orderByDesc('total_count')
            ->take(8)
            ->get();

        // Appointment Status Breakdown
        $appointmentsBreakdown = Appointment::whereBetween('appointment_date', [$startDate, $endDate])
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get();

        // Patients registered
        $newPatientsCount = Patient::whereBetween('registration_date', [$startDate, $endDate])->count();

        return view('reports.index', compact(
            'startDate',
            'endDate',
            'totalBilled',
            'totalCollected',
            'totalOutstanding',
            'paymentsByMethod',
            'revenueByDentist',
            'topProcedures',
            'appointmentsBreakdown',
            'newPatientsCount'
        ));
    }
}

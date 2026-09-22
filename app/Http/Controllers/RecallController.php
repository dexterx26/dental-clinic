<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\FollowUp;
use App\Models\Patient;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RecallController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status');
        $type = $request->input('type');
        $due = $request->input('due');

        $query = FollowUp::with(['patient', 'dentist', 'treatment']);

        if ($status) {
            $query->where('status', $status);
        }

        if ($type) {
            $query->where('follow_up_type', $type);
        }

        if ($due === 'overdue') {
            $query->where('scheduled_date', '<', Carbon::today()->toDateString())->where('status', 'pending');
        } elseif ($due === 'this_week') {
            $query->whereBetween('scheduled_date', [Carbon::today()->toDateString(), Carbon::today()->addDays(7)->toDateString()]);
        }

        $recalls = $query->orderBy('scheduled_date', 'asc')->paginate(20)->withQueryString();
        $dentists = User::where('role', 'dentist')->where('is_active', true)->get();
        $patients = Patient::where('status', 'active')->orderBy('last_name')->get();

        return view('recalls.index', compact('recalls', 'dentists', 'patients', 'status', 'type', 'due'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'dentist_id' => 'nullable|exists:users,id',
            'follow_up_type' => 'required|in:treatment_check,routine_recall_6mo,annual_checkup,orthodontic_adjustment,suture_removal,other',
            'scheduled_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $recall = FollowUp::create($validated);

        AuditLog::log('recall_scheduled', "Scheduled {$recall->follow_up_type} on {$recall->scheduled_date} for {$recall->patient->full_name}.", FollowUp::class, $recall->id);

        return back()->with('success', 'Follow-up recall scheduled successfully.');
    }

    public function updateStatus(Request $request, FollowUp $recall)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,contacted,confirmed,completed,cancelled,overdue',
            'notes' => 'nullable|string',
        ]);

        $recall->status = $validated['status'];
        if (!empty($validated['notes'])) {
            $recall->notes = $recall->notes ? ($recall->notes . "\n" . $validated['notes']) : $validated['notes'];
        }
        $recall->save();

        AuditLog::log('recall_status_updated', "Updated recall for {$recall->patient->full_name} to {$recall->status}.", FollowUp::class, $recall->id);

        return back()->with('success', "Recall status updated to " . ucfirst($recall->status) . ".");
    }
}

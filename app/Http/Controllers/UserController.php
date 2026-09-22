<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\DentistSchedule;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $role = $request->input('role');
        $query = User::query();

        if ($role) {
            $query->where('role', $role);
        }

        $users = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('users.index', compact('users', 'role'));
    }

    public function create()
    {
        return view('users.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|in:administrator,dentist,receptionist,dental_assistant,cashier',
            'phone' => 'nullable|string|max:50',
            'license_number' => 'nullable|string|max:50',
            'specialization' => 'nullable|string|max:100',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = true;

        $user = User::create($validated);

        // If dentist, initialize default weekly schedule (Mon-Sat 8:30am - 5:30pm)
        if ($user->role === 'dentist') {
            for ($day = 1; $day <= 6; $day++) {
                DentistSchedule::create([
                    'dentist_id' => $user->id,
                    'day_of_week' => $day,
                    'start_time' => '08:30:00',
                    'end_time' => '17:30:00',
                    'break_start' => '12:00:00',
                    'break_end' => '13:00:00',
                    'is_available' => true,
                ]);
            }
        }

        AuditLog::log('user_created', "Created user {$user->name} with role {$user->role}.", User::class, $user->id);

        return redirect()->route('users.index')->with('success', "Staff member {$user->name} created successfully.");
    }

    public function edit(User $user)
    {
        $user->load('schedules');
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6',
            'role' => 'required|in:administrator,dentist,receptionist,dental_assistant,cashier',
            'phone' => 'nullable|string|max:50',
            'license_number' => 'nullable|string|max:50',
            'specialization' => 'nullable|string|max:100',
            'is_active' => 'required|boolean',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        // Update schedule if dentist
        if ($user->role === 'dentist' && $request->has('schedules')) {
            foreach ($request->input('schedules') as $dayOfWeek => $schedData) {
                DentistSchedule::updateOrCreate(
                    ['dentist_id' => $user->id, 'day_of_week' => $dayOfWeek],
                    [
                        'start_time' => $schedData['start_time'] ?? '08:30:00',
                        'end_time' => $schedData['end_time'] ?? '17:30:00',
                        'break_start' => $schedData['break_start'] ?? null,
                        'break_end' => $schedData['break_end'] ?? null,
                        'is_available' => isset($schedData['is_available']),
                    ]
                );
            }
        }

        AuditLog::log('user_updated', "Updated staff account for {$user->name}.", User::class, $user->id);

        return redirect()->route('users.index')->with('success', "User {$user->name} updated successfully.");
    }
}

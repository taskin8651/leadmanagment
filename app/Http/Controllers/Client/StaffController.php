<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use App\Support\ClientPermissions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class StaffController extends Controller
{
    private const ROLES = ['Staff', 'Telecaller'];

    private function client()
    {
        return Auth::user()->client;
    }

    public function index()
    {
        $staff = User::where('client_id', $this->client()->id)
            ->where('id', '!=', Auth::id())
            ->with('roles', 'permissions')
            ->latest()
            ->get();

        return view('client.staff.index', compact('staff'));
    }

    public function create()
    {
        return view('client.staff.create', ['roles' => self::ROLES, 'permissionList' => ClientPermissions::ALL]);
    }

    private function validatedPermissions(Request $request): array
    {
        $selected = array_intersect($request->input('permissions', []), ClientPermissions::keys());
        return collect($selected)->map(fn ($key) => Permission::firstOrCreate(['name' => $key, 'guard_name' => 'web']))->all();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'in:' . implode(',', self::ROLES)],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['in:' . implode(',', ClientPermissions::keys())],
        ]);

        $user = User::create([
            'client_id' => $this->client()->id,
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $role = Role::firstOrCreate(['name' => $data['role'], 'guard_name' => 'web']);
        $user->assignRole($role);
        $user->syncPermissions($this->validatedPermissions($request));
        AuditLog::record('staff.created', "Staff member {$user->name} added as {$data['role']}", [], $user);

        return redirect()->route('client.staff.index')->with('success', 'Staff member added.');
    }

    private function authorizeStaff(User $staff): void
    {
        abort_unless($staff->client_id === $this->client()->id && $staff->id !== Auth::id(), 403);
    }

    public function edit(User $staffMember)
    {
        $this->authorizeStaff($staffMember);
        return view('client.staff.edit', ['staff' => $staffMember, 'roles' => self::ROLES, 'permissionList' => ClientPermissions::ALL]);
    }

    public function update(Request $request, User $staffMember)
    {
        $this->authorizeStaff($staffMember);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'unique:users,email,' . $staffMember->id],
            'password' => ['nullable', 'string', 'min:8'],
            'role' => ['required', 'in:' . implode(',', self::ROLES)],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['in:' . implode(',', ClientPermissions::keys())],
        ]);

        $staffMember->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'] ? Hash::make($data['password']) : $staffMember->password,
        ]);

        $role = Role::firstOrCreate(['name' => $data['role'], 'guard_name' => 'web']);
        $staffMember->syncRoles([$role]);
        $staffMember->syncPermissions($this->validatedPermissions($request));
        AuditLog::record('staff.updated', "Staff member {$staffMember->name} updated", [], $staffMember);

        return redirect()->route('client.staff.index')->with('success', 'Staff member updated.');
    }

    public function destroy(User $staffMember)
    {
        $this->authorizeStaff($staffMember);
        AuditLog::record('staff.deleted', "Staff member {$staffMember->name} removed", [], $staffMember);
        $staffMember->delete();
        return back()->with('success', 'Staff member removed.');
    }

    public function toggleActive(User $staffMember)
    {
        $this->authorizeStaff($staffMember);
        $staffMember->update(['is_active' => !$staffMember->is_active]);
        AuditLog::record($staffMember->is_active ? 'staff.reactivated' : 'staff.suspended', "Staff member {$staffMember->name} " . ($staffMember->is_active ? 'reactivated' : 'suspended'), [], $staffMember);
        return back()->with('success', $staffMember->is_active ? 'Staff member reactivated.' : 'Staff member suspended.');
    }
}

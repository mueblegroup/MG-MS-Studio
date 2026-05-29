<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserApiController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = User::query()->latest();
        $forcedRole = $request->route('role');

        if ($forcedRole) {
            $query->where('role', $forcedRole);
        } elseif ($request->filled('role')) {
            $query->where('role', $request->string('role'));
        }

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone_number', 'like', "%{$search}%");
            });
        }

        return $this->paginated($query->paginate($request->integer('per_page', 25)), 'Users loaded.');
    }

    public function store(Request $request): JsonResponse
    {
        $forcedRole = $request->route('role');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => [$forcedRole ? 'nullable' : 'required', Rule::in(['admin', 'teacher', 'student'])],
            'phone_number' => ['nullable', 'string', 'max:50'],
        ]);

        $validated['role'] = $forcedRole ?: $validated['role'];
        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);

        return $this->success($user, 'User created.', 201);
    }

    public function show(User $user): JsonResponse
    {
        return $this->success($user->load(['plans.plan', 'classSessionAssignments.classSession', 'appNotifications']), 'User loaded.');
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'role' => ['sometimes', 'required', Rule::in(['admin', 'teacher', 'student'])],
            'phone_number' => ['nullable', 'string', 'max:50'],
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return $this->success($user->fresh(), 'User updated.');
    }

    public function destroy(User $user): JsonResponse
    {
        $user->delete();

        return $this->success(null, 'User deleted.');
    }
}

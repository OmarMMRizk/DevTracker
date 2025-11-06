<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;

class UserService
{
    /**
     * Get filtered and paginated users
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getUsers(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = User::query()->with('roles');

        // Filter by role
        if (!empty($filters['role'])) {
            $query->role($filters['role']);
        }

        // Filter by search
        if (!empty($filters['search'])) {
            $query->where(function($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('email', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('phone', 'like', '%' . $filters['search'] . '%');
            });
        }

        // Filter by active status
        if (isset($filters['is_active']) && $filters['is_active'] !== null) {
            $query->where('is_active', $filters['is_active']);
        }

        return $query->paginate($perPage);
    }

    /**
     * Get total users count
     *
     * @return int
     */
    public function getTotalCount(): int
    {
        return User::count();
    }

    /**
     * Get user by ID
     *
     * @param int $id
     * @return User|null
     */
    public function getUserById(int $id): ?User
    {
        return User::with('roles')->find($id);
    }

    /**
     * Create new user
     *
     * @param array $data
     * @return User
     */
    public function createUser(array $data): User
    {
        // Hash password if provided
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user = User::create($data);
        
        // Assign role if provided
        if (!empty($data['role'])) {
            $user->assignRole($data['role']);
        }

        return $user->load('roles');
    }

    /**
     * Update user
     *
     * @param int $id
     * @param array $data
     * @return User
     */
    public function updateUser(int $id, array $data): User
    {
        $user = User::findOrFail($id);

        // Hash password if provided
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            // Remove password from data if not provided
            unset($data['password']);
        }

        $user->update($data);

        // Sync role if provided
        if (!empty($data['role'])) {
            $user->syncRoles([$data['role']]);
        }

        return $user->load('roles');
    }

    /**
     * Delete user
     *
     * @param int $id
     * @return bool
     */
    public function deleteUser(int $id): bool
    {
        $user = User::findOrFail($id);
        
        // Delete all user tokens before deleting user
        $user->tokens()->delete();
        
        return $user->delete();
    }

    /**
     * Toggle user active status
     *
     * @param int $id
     * @return User
     */
    public function toggleUserStatus(int $id): User
    {
        $user = User::findOrFail($id);
        $user->is_active = !$user->is_active;
        $user->save();

        // If deactivating user, revoke all tokens
        if (!$user->is_active) {
            $user->tokens()->delete();
        }

        return $user->load('roles');
    }

    /**
     * Activate user
     *
     * @param int $id
     * @return User
     */
    public function activateUser(int $id): User
    {
        $user = User::findOrFail($id);
        $user->is_active = true;
        $user->save();

        return $user->load('roles');
    }

    /**
     * Deactivate user
     *
     * @param int $id
     * @return User
     */
    public function deactivateUser(int $id): User
    {
        $user = User::findOrFail($id);
        $user->is_active = false;
        $user->save();

        // Revoke all tokens when deactivating
        $user->tokens()->delete();

        return $user->load('roles');
    }
}
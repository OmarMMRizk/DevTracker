<?php

namespace App\Http\Controllers\RolePermission;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;



class RolePermissionController extends Controller
{
    use ApiResponse;

    // ==================== ROLES ====================

    /**
     * Get all roles
     */
    public function getRoles()
    {
        $roles = Role::with('permissions')->get();
        return $this->success($roles, 'تم جلب الأدوار بنجاح');
    }

    /**
     * Get one roles
     */
    public function showRole($id)
    {
        $role = Role::with('permissions')->find($id);

        if (!$role) {
            return $this->error('الدور غير موجود', 404);
        }

        return $this->success($role, 'تم جلب الدور بنجاح');
    }     

    /**
     * Create new role
     */
    public function createRole(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:roles,name',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,name'
        ]);

        $role = Role::create(['name' => $request->name, 'guard_name' => 'sanctum']);

        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        return $this->success($role->load('permissions'), 'تم إنشاء الدور بنجاح', 201);
        
    }

    /**
     * Update role
     */
    public function updateRole(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|unique:roles,name,' . $id,
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,name'
        ]);

        $role = Role::findOrFail($id);
        $role->update(['name' => $request->name]);

        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        return $this->success($role->load('permissions'), 'تم تحديث الدور بنجاح');
    }

    /**
     * Delete role
     */
    public function deleteRole($id)
    {
        $role = Role::find($id);
        
        if (!$role) {

            return $this->error('الدور غير موجود', 404);

        }

        $role->delete();
        return $this->success([$id], 'تم حذف الدور بنجاح');
    }

    // ==================== PERMISSIONS ====================

    /**
     * Get all permissions
     */
    public function getPermissions()
    {
        $permissions = Permission::all();
        return $this->success($permissions, 'تم جلب الصلاحيات بنجاح');
    }

    /**
     * Create new permission
     */
    public function createPermission(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:permissions,name'
        ]);

        $permission = Permission::create(['name' => $request->name, 'guard_name' => 'sanctum']);

        return $this->success($permission, 'تم إنشاء الصلاحية بنجاح', 201);
    }

    /**
     * Delete permission
     */
    public function deletePermission($id)
    {
        $permission = Permission::findOrFail($id);
        $permission->delete();

        return $this->success([], 'تم حذف الصلاحية بنجاح');
    }

    // ==================== USER ROLES & PERMISSIONS ====================

    /**
     * Assign role to user
     */
    public function assignRole(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|exists:roles,name'
        ]);

        $user = User::findOrFail($request->user_id);
        $user->assignRole($request->role);

        return $this->success([
            'user' => $user,
            'roles' => $user->getRoleNames()
        ], 'تم تعيين الدور للمستخدم بنجاح');
    }

    /**
     * Remove role from user
     */
    public function removeRole(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|exists:roles,name'
        ]);

        $user = User::findOrFail($request->user_id);
        $user->removeRole($request->role);

        return $this->success([
            'user' => $user,
            'roles' => $user->getRoleNames()
        ], 'تم إزالة الدور من المستخدم بنجاح');
    }

    /**
     * Assign permission to user
     */
    public function assignPermission(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'permission' => 'required|exists:permissions,name'
        ]);

        $user = User::findOrFail($request->user_id);
        $user->givePermissionTo($request->permission);

        return $this->success([
            'user' => $user,
            'permissions' => $user->getAllPermissions()->pluck('name')
        ], 'تم تعيين الصلاحية للمستخدم بنجاح');
    }

    /**
     * Remove permission from user
     */
    public function removePermission(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'permission' => 'required|exists:permissions,name'
        ]);

        $user = User::findOrFail($request->user_id);
        $user->revokePermissionTo($request->permission);

        return $this->success([
            'user' => $user,
            'permissions' => $user->getAllPermissions()->pluck('name')
        ], 'تم إزالة الصلاحية من المستخدم بنجاح');
    }

    /**
     * Get user roles and permissions
     */
    public function getUserRolesPermissions($userId)
    {
        $user = User::findOrFail($userId);

        return $this->success([
            'user' => $user,
            'roles' => $user->getRoleNames(),
            'permissions' => $user->getAllPermissions()->pluck('name'),
            'direct_permissions' => $user->permissions->pluck('name')
        ], 'تم جلب أدوار وصلاحيات المستخدم بنجاح');
    }
}
<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Services\UserService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    use ApiResponse;

    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }


    /**
     * Display the specified user
     */
    public function show($id)
    {
        $user = $this->userService->getUserById($id);

        if (!$user) {
            return $this->error('المستخدم غير موجود', [], 404);
        }

        return $this->success([
            "user" => new UserResource($user)
        ], "تم جلب بيانات المستخدم بنجاح", 200);
    }

    /**
     * Update the specified user
     */

    public function update(UpdateUserRequest $request, User $user)
    {
        try {
            // $user هنا هو User Model مباشرة بفضل Route Model Binding
            $updatedUser = $this->userService->updateUser($user->id, $request->validated());

            return $this->success([
                "user" => new UserResource($updatedUser)
            ], "تم تحديث بيانات المستخدم بنجاح", 200);
        } catch (\Exception $e) {
            return $this->error('فشل تحديث المستخدم', ['error' => $e->getMessage()], 500);
        }
    }
    /**
     * Remove the specified user
     */
    public function destroy(Request $request, $id)
    {
        try {
            if ($request->user()->id === (int) $id) {
                return $this->error('لا يمكنك حذف حسابك الخاص', [], 403);
            }

            $this->userService->deleteUser($id);

            return $this->success([], "تم حذف المستخدم بنجاح", 200);
        } catch (\Exception $e) {
            return $this->error('فشل حذف المستخدم', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Toggle user active status
     */
    public function toggleStatus(Request $request, $id)
    {
        try {
            if ($request->user()->id === (int) $id) {
                return $this->error('لا يمكنك تعطيل حسابك الخاص', [], 403);
            }

            $user = $this->userService->toggleUserStatus($id);

            $message = $user->is_active 
                ? "تم تفعيل المستخدم بنجاح" 
                : "تم تعطيل المستخدم بنجاح";

            return $this->success([
                "user" => new UserResource($user)
            ], $message, 200);
        } catch (\Exception $e) {
            return $this->error('فشل تغيير حالة المستخدم', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Activate user
     */
    public function activate($id)
    {
        try {
            $user = $this->userService->activateUser($id);

            return $this->success([
                "user" => new UserResource($user)
            ], "تم تفعيل المستخدم بنجاح", 200);
        } catch (\Exception $e) {
            return $this->error('فشل تفعيل المستخدم', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Deactivate user
     */
    public function deactivate(Request $request, $id)
    {
        try {
            if ($request->user()->id === (int) $id) {
                return $this->error('لا يمكنك تعطيل حسابك الخاص', [], 403);
            }

            $user = $this->userService->deactivateUser($id);

            return $this->success([
                "user" => new UserResource($user)
            ], "تم تعطيل المستخدم بنجاح", 200);
        } catch (\Exception $e) {
            return $this->error('فشل تعطيل المستخدم', ['error' => $e->getMessage()], 500);
        }
    }
}
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUserRoleRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        return UserResource::collection(
            User::withCount('subscriptions')->orderBy('created_at', 'desc')->get()
        );
    }

    public function updateRole(UpdateUserRoleRequest $request, int $id)
    {
        $user = User::findOrFail($id);

        abort_if(
            $user->id === $request->user()->id && $request->role === 'client',
            422,
            'No puedes quitarte el rol de administrador a ti mismo.'
        );

        $user->update(['role' => $request->role]);

        return response()->json([
            'message' => 'Rol del usuario actualizado correctamente.',
            'user' => new UserResource($user->loadCount('subscriptions')),
        ]);
    }
}

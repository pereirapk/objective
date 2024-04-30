<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use JWTAuth;
use Illuminate\Support\Facades\Auth;
class AccountController extends Controller
{
    public function register(Request $request)
    {
        // Validation rules
        $validator = Validator::make($request->all(), [
            'conta_id' => 'required|numeric|unique:users,conta_id',
            'password' => 'required|string|min:4',
            'valor' => 'numeric',

        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $user = User::create([
            'conta_id' => $request->conta_id,
            'password' => Hash::make($request->password),
            'saldo' => $request->valor,
        ]);
        $response = [
            'conta_id' => $user->conta_id,
            'saldo' => $user->saldo,
        ];
        return response()->json($response, 201);
    }
    public function getByAccountId(Request $request)
    {
        $conta_id = $request->query('id');

        if (!$conta_id) {
            return response()->json(['error' => 'Conta ID is required'], 400);
        }

        $user = $this->getById($conta_id);
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }
        return response()->json(['user' => $user], 200);
    }
    public function getById($id)
    {
       return  $user = User::where('conta_id', $id)->first();
    }
    public function update($id, $value)
    {
        $user = User::where('conta_id', $id)->first();

        // Update user attributes
        $user->conta_id = $id;
        $user->saldo = $value;
        // Update other attributes as needed

        // Save the updated user
        $user->save();
    }
    public function login(Request $request)
    {
        $credentials = $request->only('conta_id', 'password');

        if (!$token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);

        }
        return response()->json([
            'data' => [
                'token' => $token,
                'token_type' => 'bearer',
            ]
        ]);
    }

    public function logout()
    {
        Auth::logout();

        return response()->json(['message' => 'Logged out successfully']);
    }
}

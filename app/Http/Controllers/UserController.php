<?php

namespace App\Http\Controllers;

use App\Models\User;
use CaliCastle\Cuid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function login(Request $request)
    {
        try {
            $credentials = $request->validate([
                'email' => ['required'],
                'password' => ['required'],
            ]);

            $loginField = str_contains($credentials['email'], '@') ? 'email' : 'username';

            $loginCredentials = [
                $loginField => $credentials['email'],
                'password' => $credentials['password']
            ];

            if (!Auth::attempt($loginCredentials)) {
                return response()->json([
                    'code' => 422,
                    'status' => 'error',
                    'data' => null,
                    'message' => 'Kredensial tidak sesuai atau akun Anda telah dinonaktifkan'
                ], 422);
            }

            $user = User::where('email', $credentials['email'])->orWhere('username', $credentials['email'])->first();
            if (!$user) {
                return response()->json([
                    'code' => 404,
                    'status' => 'error',
                    'data' => null,
                    'message' => 'User tidak ditemukan, silakan Registrasi terlebih dahulu'
                ], 404);
            }
            $user->tokens()->delete();

            $hours = (int) 4;
            $generateToken = $user->createToken($user->email, ['*'], now()->addHours($hours));

            return response()->json([
                'status' => 'success',
                'code' => 200,
                'message' => 'Login successful. Welcome back!',
                'data' => [
                    'id' => $user->unique_id,
                    'token' => $generateToken->plainTextToken,
                    'session' => [
                        'expires_at' => now()->addHours($hours),
                        'expired_in' => $hours
                    ]
                ]
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'code' => 422,
                'message' => $e->getMessage(),
                'data' => null
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'code' => 500,
                'message' => 'Something went wrong, please try again later',
                'data' => null
            ], 500);
        }
    }
    public function store(Request $request)
    {
        try {
            $request->validate(
                [
                    'fullname' => 'required|string|max:100',
                    'username' => 'required|string|max:80|unique:users,username',
                    'email' => 'required|email|max:80|unique:users,email',
                    'password' => 'required|string|confirmed|min:8',
                ],
                [
                    'fullname.required' => 'Fullname wajib diisi.',
                    'username.required' => 'Username wajib diisi.',
                    'email.required' => 'Email wajib diisi.',
                    'password.required' => 'Password wajib diisi.',
                ]
            );
            User::create([
                'unique_id' => Cuid::make(),
                'fullname' => $request->fullname,
                'username' => strtolower($request->username),
                'email' => strtolower($request->email),
                'password' => Hash::make($request->password),

            ]); 

            return response()->json([
                'status' => 'success',
                'code' => 201,
                'message' => 'Registrasi berhasil, selamat datang!',
                'data' => null
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'status' => 'error',
                'data' => null,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();
            return response()->json([
                'status' => 'success',
                'code' => 200,
                'message' => 'Logout berhasil, datang lagi nanti ya!',
                'data' => null
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'status' => 'error',
                'data' => null,
                'message' => $e->getMessage()
            ], 500);
        }
        
    }
    public function update(Request $request, string $unique_id)
    {
        try {
            $request->validate(
                [
                    'username' => 'required|string|max:80|unique:users,username,' . $unique_id,
                    'fullname' => 'required|string|max:100',
                    'old_password' => 'required|string|min:8',
                    'new_password' => 'required|string|confirmed|min:8',
                    'bio' => 'nullable|string',
                    'avatar' => 'nullable|string'
                ],
                [
                    'username.required' => 'Username tidak boleh kosong',
                    'fullname.required' => 'Fullname tidak boleh kosong',
                    'old_password.required' => 'Password lama harus di isi',
                    'new_password.required' => 'Masukkan password baru anda'
                ]
            );
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'status' => 'error',
                'data' => null,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}

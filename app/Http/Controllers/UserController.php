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
                    'id' => $user->id,
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
                'message' => $e->getMessage(),
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
                'id' => Cuid::make(),
                'fullname' => $request->fullname,
                'slug' => str()->slug($request->fullname),
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
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'code' => 401,
                    'message' => 'User tidak terautentikasi',
                    'data' => null
                ], 401);
            }

            $token = $user->currentAccessToken();

            if (!$token) {
                return response()->json([
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'User sudah logout',
                    'data' => null
                ], 200);
            }

            $token->delete();

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

    public function update(Request $request)
    {
        try {
            $currentUser = auth()->user();

            // Validasi tanpa wajib mengisi password
            $request->validate(
                [
                    'fullname' => 'required|string|max:100',
                    'old_password' => 'nullable|string|min:8', // Jadi nullable
                    'new_password' => 'nullable|string|confirmed|min:8', // Jadi nullable
                    'bio' => 'nullable|string',
                    'avatar' => 'nullable|string'
                ],
                [
                    'fullname.required' => 'Fullname tidak boleh kosong',
                ]
            );

            $user = User::where('id', $currentUser->id)->firstOrFail();

            // Cek apakah user ingin mengubah password
            if ($request->filled('old_password') || $request->filled('new_password')) {
                if (!$request->filled('old_password') || !$request->filled('new_password')) {
                    return response()->json([
                        'code' => 422,
                        'status' => 'error',
                        'data' => null,
                        'message' => 'Jika ingin mengubah password, semua field password harus diisi!',
                    ], 422);
                }

                // Cek apakah password lama cocok
                if (!Hash::check($request->old_password, $user->password)) {
                    return response()->json([
                        'code' => 422,
                        'status' => 'error',
                        'data' => null,
                        'message' => 'Old Password tidak sesuai, periksa lagi!',
                    ], 422);
                }

                // Update password
                $user->password = Hash::make($request->new_password);
            }

            // Update nama, bio, dan avatar tanpa masalah
            $user->fullname = $request->fullname;
            $user->bio = $request->bio;
            $user->avatar = $request->avatar;
            $user->save();

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'data' => null,
                'message' => 'Profile berhasil di update',
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


    public function profileUser()
    {
        try {
            $auth = auth()->user();

            // Cari user berdasarkan unique_id
            $user = User::where('id', $auth->id)->first();

            // Jika user tidak ditemukan, kembalikan error
            if (!$user) {
                return response()->json([
                    'code' => 404,
                    'status' => 'error',
                    'data' => null,
                    'message' => 'User tidak ditemukan.',
                ], 404);
            }

            // Jika ditemukan, kembalikan data user
            return response()->json([
                'code' => 200,
                'status' => 'success',
                'data' => $user,
                'message' => 'Profil user berhasil diambil.',
            ], 200);
        } catch (\Exception $e) {
            // Jika ada error lain, kembalikan respons error
            return response()->json([
                'code' => 500,
                'status' => 'error',
                'data' => null,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}

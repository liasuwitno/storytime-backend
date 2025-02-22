<?php

namespace App\Http\Controllers;

use App\Models\MultipleImage;
use App\Models\User;
use CaliCastle\Cuid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
            $this->authorize('update', $currentUser);

            // Validasi tanpa wajib mengisi password
            $request->validate([
                'fullname' => 'required|string|max:100',
                'old_password' => 'nullable|string|min:8',
                'new_password' => 'nullable|string|confirmed|min:8',
                'bio' => 'nullable|string',
                'avatar' => 'nullable|string'
            ], [
                'fullname.required' => 'Fullname tidak boleh kosong',
            ]);

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

                if (!Hash::check($request->old_password, $user->password)) {
                    return response()->json([
                        'code' => 422,
                        'status' => 'error',
                        'data' => null,
                        'message' => 'Old Password tidak sesuai, periksa lagi!',
                    ], 422);
                }

                $user->password = Hash::make($request->new_password);
            }

            DB::beginTransaction();

            $user->update([
                'fullname' => $request->fullname,
                'bio' => $request->bio,
            ]);

            // Avatar hanya diperbarui jika diberikan dalam request
            if ($request->has('avatar')) {
                $newAvatar = $request->avatar;

                // Cek apakah user sudah punya avatar sebelumnya
                $existingAvatar = MultipleImage::where('related_id', $currentUser->id)
                    ->where('related_type', User::class)
                    ->where('identifier', 'avatar')
                    ->first();

                if ($existingAvatar) {
                    // Jika avatar berubah, hapus avatar lama dan tambahkan yang baru
                    if ($existingAvatar->image_url !== $newAvatar) {
                        $existingAvatar->delete();

                        MultipleImage::create([
                            'related_id' => $currentUser->id,
                            'related_type' => User::class,
                            'image_url' => $newAvatar,
                            'identifier' => 'avatar'
                        ]);
                    }
                } else {
                    // Jika belum ada avatar, tambahkan baru
                    MultipleImage::create([
                        'related_id' => $currentUser->id,
                        'related_type' => User::class,
                        'image_url' => $newAvatar,
                        'identifier' => 'avatar'
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'data' => null,
                'message' => 'Profile berhasil diupdate!',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
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
            $this->authorize('view', $auth);
            // Ambil user beserta avatarnya
            $user = User::where('id', $auth->id)
                ->with('avatar') // Ambil avatar dengan morphOne
                ->first();

            if (!$user) {
                return response()->json([
                    'code' => 404,
                    'status' => 'error',
                    'data' => null,
                    'message' => 'User tidak ditemukan.',
                ], 404);
            }

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'data' => [
                    'id' => $user->id,
                    'fullname' => $user->fullname,
                    'email' => $user->email,
                    'bio' => $user->bio,
                    'avatar' => $user->avatar->image_url ?? null // Pastikan ada image_url di multiple_images
                ],
                'message' => 'Profil user berhasil diambil.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'status' => 'error',
                'data' => null,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}

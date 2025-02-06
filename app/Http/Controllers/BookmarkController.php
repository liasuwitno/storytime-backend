<?php

namespace App\Http\Controllers;

use App\Models\Bookmark;
use Illuminate\Http\Request;

class BookmarkController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        
        $bookmarks = Bookmark::where('user_id', auth()->user()->unique_id)
            ->with(['story' => function ($query) {
                $query->where('is_deleted', false); // Hanya ambil story yang aktif
            }])
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $bookmarks
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function toggleBookmark(Request $request)
    {
        try {
            // Validasi untuk user_id berdasarkan unique_id
            $validatedData = $request->validate([
                'user_id' => 'required|exists:users,unique_id',  // Pastikan menggunakan unique_id, bukan id
                'story_id' => 'required|exists:stories,id',
            ]);

            // Ambil user yang sedang login
            $user = auth()->user();

            // Pastikan user diizinkan menambahkan bookmark
            $this->authorize('create', Bookmark::class);

            // Cek apakah bookmark sudah ada
            $bookmark = Bookmark::where([
                'user_id' => $user->unique_id,
                'story_id' => $validatedData['story_id'],
            ])->first();

            // Cek apakah user_id yang dikirimkan sesuai dengan unique_id pengguna yang sedang login
            if ($user->unique_id !== $validatedData['user_id']) {
                return response()->json([
                    'code' => 403,
                    'status' => 'error',
                    'message' => 'Anda tidak diperbolehkan mengakses bookmark orang lain.',
                ], 403);
            }

            // Cek apakah bookmark sudah ada
            $bookmark = Bookmark::where([
                'user_id' => $user->unique_id,  // Gunakan unique_id di sini
                'story_id' => $validatedData['story_id'],
            ])->first();

            if ($bookmark) {
                // Jika sudah ada, hapus
                $bookmark->delete();
                return response()->json([
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'Bookmark berhasil dihapus.',
                ], 200);
            } else {
                // Jika belum ada, tambahkan
                Bookmark::create([
                    'user_id' => $user->unique_id,  // Pastikan menggunakan unique_id
                    'story_id' => $validatedData['story_id'],
                ]);
                return response()->json([
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'Bookmark berhasil ditambahkan.',
                ], 200);
            }
        } catch (\Exception $e) {
            return response()->json([
                'code' => 422,
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }




    /**
     * Display the specified resource.
     */
    public function show(Bookmark $bookmark)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Bookmark $bookmark)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Bookmark $bookmark)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Bookmark $bookmark)
    {
        //
    }
}

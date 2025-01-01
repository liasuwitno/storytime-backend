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
        try {
            $bookmarks = Bookmark::all();

            if ($bookmarks->isEmpty()) {
                return response()->json([
                    'code' => 404,
                    'status' => 'error',
                    'data' => null,
                    'message' => 'Belum ada bookmark / bookmark tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'data' => $bookmarks,
                'message' => 'Bookmarks berhasil didapatkan',
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
            $validatedData = $request->validate([
                'user_id' => 'required|exists:users,id',
                'story_id' => 'required|exists:stories,id',
            ]);

            // Cek apakah bookmark sudah ada
            $bookmark = Bookmark::where([
                'user_id' => $validatedData['user_id'],
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
                Bookmark::create($validatedData);
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

<?php

namespace App\Http\Controllers;

use App\Models\Bookmark;
use Illuminate\Http\Request;

class BookmarkController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $page = $request->query('page', 1);
            $perPage = $request->query('per_page', 5);

            $user = auth()->user();

            $bookmarks = Bookmark::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->with(['story' => function ($query) {
                    $query->where('is_deleted', false)
                        ->with(['user:id,fullname,avatar', 'images', 'category:id,name']);
                }])
                ->paginate($perPage, ['*'], 'page', $page);

            if ($bookmarks->isEmpty()) {
                return response()->json([
                    'code' => 404,
                    'status' => 'error',
                    'data' => null,
                    'message' => 'Bookmark tidak ditemukan'
                ], 404);
            }

            $formattedBookmarks = $bookmarks->map(function ($bookmark) {
                if (!$bookmark->story) return null;

                return [
                    'story_id' => $bookmark->story->id,
                    'title' => $bookmark->story->title,
                    'slug' => $bookmark->story->slug,
                    'author' => [
                        'name' => $bookmark->story->user->fullname,
                        'avatar' => $bookmark->story->user->avatar,
                    ],
                    'content' => $bookmark->story->body,
                    'images' => $bookmark->story->images->map(fn($image) => [
                        'url' => $image->image_url,
                        'identifier' => $image->identifier
                    ]),
                    'is_bookmark' => true,
                    'category_name' => $bookmark->story->category->name,
                    'created_at' => $bookmark->story->created_at->toIso8601String(),
                ];
            })->filter();

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'data' => [
                    'bookmarks' => $formattedBookmarks,
                    'pagination' => [
                        'current_page' => $bookmarks->currentPage(),
                        'total_pages' => $bookmarks->lastPage(),
                        'per_page' => $bookmarks->perPage(),
                        'total_data' => $bookmarks->total(),
                        'has_more_pages' => $bookmarks->hasMorePages(),
                    ],
                ],
                'message' => 'Berhasil mendapatkan daftar bookmark'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'status' => 'error',
                'data' => null,
                'message' => 'Terjadi kesalahan, coba lagi nanti.'
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
            // Validasi untuk user_id berdasarkan unique_id
            $validatedData = $request->validate([
                'user_id' => 'required|exists:users,id',
                'story_id' => 'required|exists:stories,id',
            ]);

            // Ambil user yang sedang login
            $user = auth()->user();

            // Pastikan user diizinkan menambahkan bookmark
            $this->authorize('create', Bookmark::class);

            // Cek apakah bookmark sudah ada
            $bookmark = Bookmark::where([
                'user_id' => $user->id,
                'story_id' => $validatedData['story_id'],
            ])->first();

            // Cek apakah user_id yang dikirimkan sesuai dengan unique_id pengguna yang sedang login
            if ($user->id !== $validatedData['user_id']) {
                return response()->json([
                    'code' => 403,
                    'status' => 'error',
                    'message' => 'Anda tidak diperbolehkan mengakses bookmark orang lain.',
                ], 403);
            }

            // Cek apakah bookmark sudah ada
            $bookmark = Bookmark::where([
                'user_id' => $user->id,  // Gunakan unique_id di sini
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
                    'user_id' => $user->id,  // Pastikan menggunakan unique_id
                    'story_id' => $validatedData['story_id'],
                ]);
                return response()->json([
                    'code' => 201,
                    'status' => 'success',
                    'message' => 'Bookmark berhasil ditambahkan.',
                ], 201);
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

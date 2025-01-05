<?php

namespace App\Http\Controllers;

use App\Models\MultipleImage;
use Illuminate\Support\Str;
use App\Models\Story;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class StoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            // Ambil parameter sort, default adalah 'newest'
            $sort = $request->query('sort', 'newest');

            // Ambil parameter page dan tentukan jumlah item per halaman
            $perPage = 10; // Default jumlah per halaman
            $page = $request->query('page', 1);

            // Query dasar
            $query = Story::where('is_deleted', false) // Tambahkan filter
                ->join('users', 'stories.user_id', '=', 'users.id')
                ->join('categories', 'stories.category_id', '=', 'categories.id')
                ->select('stories.*', 'users.name as user_name', 'categories.name as category_name');
            // Sorting berdasarkan parameter sort
            switch ($sort) {
                case 'popular': // Berdasarkan banyaknya bookmark
                    $query->leftJoin('bookmarks', 'stories.id', '=', 'bookmarks.story_id')
                        ->selectRaw('COUNT(bookmarks.id) as bookmark_count')
                        ->groupBy('stories.id', 'users.name', 'categories.name', 'stories.created_at')
                        ->orderBy('bookmark_count', 'desc');
                    break;

                case 'asc': // Berdasarkan title (A-Z)
                    $query->orderBy('stories.title', 'asc');
                    break;

                case 'desc': // Berdasarkan title (Z-A)
                    $query->orderBy('stories.title', 'desc');
                    break;

                case 'newest': // Default: Berdasarkan waktu terbaru
                default:
                    $query->orderBy('stories.created_at', 'desc');
                    break;
            }

            // Dapatkan hasil dengan pagination
            $stories = $query->paginate($perPage, ['*'], 'page', $page);

            if ($stories->isEmpty()) {
                return response()->json([
                    'code' => 404,
                    'status' => 'error',
                    'data' => null,
                    'message' => 'Belum ada story. Ayo buat story baru!',
                ], 404);
            }

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'data' => $stories,
                'message' => 'Stories berhasil didapatkan',
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
    public function store(Request $request)
    {
        try {
            // Validasi input
            $validatedData = $request->validate([
                'title' => 'required|string|max:255',
                'body' => 'required|string',
                'images' => 'nullable|array',
                'images.*' => 'nullable|url:http,https', // Validasi untuk setiap URL gambar
                'category_id' => 'required|exists:categories,id',
            ]);

            // Buat slug dari title
            
            DB::transaction(function () use ($request, $validatedData) {
                $slug = Str::slug($validatedData['title']);

                // Simpan story ke database
                $story = Story::create([
                    'unique_id' => Str::uuid()->toString(),
                    'title' => $validatedData['title'],
                    'slug' => $slug,
                    'body' => $validatedData['body'],
                    'user_id' => auth()->id(), // Ambil ID user yang sedang login
                    'category_id' => $validatedData['category_id'],
                    'is_deleted' => false,
                ]);

                $contentImage = [];
                // Simpan multiple images jika ada
                if (isset($validatedData['images'])) {
                    foreach ($validatedData['images'] as $imageUrl) {
                        $contentImage = [
                            'related_unique_id' => $story->unique_id,
                            'related_type' => Story::class,
                            'image_url' => $imageUrl,
                        ];
                    }

                    MultipleImage::insert($contentImage);
                }
            });

            return response()->json([
                'code' => 201,
                'status' => 'success',
                'data' => null,
                'message' => 'Story berhasil ditambahkan',
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'code' => 422,
                'status' => 'error',
                'data' => null,
                'message' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'status' => 'error',
                'data' => null,
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(string $unique_id)
    {
        try {
            $story = Story::where('unique_id', $unique_id)->first();

            if (!$story) {
                return response()->json([
                    'code' => 404,
                    'status' => 'error',
                    'data' => null,
                    'message' => 'Story tidak ditemukan.',
                ], 404);
            }

            $similarStories = Story::where('category_id', $story->category_id)
            ->where('unique_id', '!=', $unique_id) // Hindari story itu sendiri
            ->limit(5) // Batasi jumlah similar stories
            ->get();

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'data' => [
                'story' => $story,
                'similar_stories' => $similarStories,
            ],
                'message' => 'Data Story berhasil diambil.',
            ], 200);

        } catch (\Exception $e) {

            return response()->json([
                'code' => 500,
                'status' =>'error',
                'data' => null,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Story $story)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $unique_id)
{
    try {
        // Validasi input
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'images' => 'nullable|array',
            'images.*' => 'nullable|url',
            'category_id' => 'required|exists:categories,id',
        ]);

        // Cari story berdasarkan unique_id
        $story = Story::where('unique_id', $unique_id)->first();

        if (!$story) {
            return response()->json([
                'code' => 404,
                'status' => 'error',
                'data' => null,
                'message' => 'Story tidak ditemukan.',
            ], 404);
        }

        // Perbarui data story
        $story->update([
            'title' => $validatedData['title'],
            'slug' => Str::slug($validatedData['title']),
            'body' => $validatedData['body'],
            'category_id' => $validatedData['category_id'],
        ]);

        // Perbarui multiple images jika ada
        if (isset($validatedData['images'])) {
            $existingImages = MultipleImage::where('related_unique_id', $story->unique_id)
                ->where('related_type', Story::class)
                ->pluck('image_url')
                ->toArray();

            $newImages = $validatedData['images'];

            // Hapus gambar yang tidak ada di newImages
            MultipleImage::where('related_unique_id', $story->unique_id)
                ->where('related_type', Story::class)
                ->whereNotIn('image_url', $newImages)
                ->delete();

            // Tambahkan gambar yang belum ada di existingImages
            foreach (array_diff($newImages, $existingImages) as $imageUrl) {
                MultipleImage::create([
                    'related_unique_id' => $story->unique_id,
                    'related_type' => Story::class,
                    'image_url' => $imageUrl,
                ]);
            }
        }

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'data' => $story,
            'message' => 'Story berhasil diperbarui.',
        ], 200);
    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'code' => 422,
            'status' => 'error',
            'data' => null,
            'message' => $e->errors(),
        ], 422);
    } catch (\Exception $e) {
        Log::error('Error updating story: ' . $e->getMessage());
        return response()->json([
            'code' => 500,
            'status' => 'error',
            'data' => null,
            'message' => 'Terjadi kesalahan, coba lagi nanti.',
        ], 500);
    }
}



    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            // Cari story berdasarkan ID
            $story = Story::find($id);

            // Jika story tidak ditemukan
            if (!$story) {
                return response()->json([
                    'code' => 404,
                    'status' => 'error',
                    'data' => null,
                    'message' => 'Story tidak ditemukan.',
                ], 404);
            }

            // Update is_deleted menjadi true
            $story->update(['is_deleted' => true]);

            // Respons sukses
            return response()->json([
                'code' => 200,
                'status' => 'success',
                'data' => null,
                'message' => 'Story berhasil dihapus.',
            ], 200);
        } catch (\Exception $e) {
            // Respons jika ada error
            return response()->json([
                'code' => 500,
                'status' => 'error',
                'data' => null,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}

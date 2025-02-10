<?php

namespace App\Http\Controllers;

use App\Events\StoryCreateEvent;
use App\Http\Requests\StoreStoryRequest;
use App\Http\Requests\UpdateStoryRequest;
use App\Models\Bookmark;
use App\Models\Category;
use App\Models\MultipleImage;
use App\Models\Notification;
use Illuminate\Support\Str;
use App\Models\Story;
use CaliCastle\Cuid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class StoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $stories = Story::where('is_deleted', false)
                ->with(['user:id,fullname,avatar', 'images', 'category:id,name'])
                ->paginate(10);

            if ($stories->isEmpty()) {
                return response()->json([
                    'code' => 404,
                    'status' => 'error',
                    'data' => null,
                    'message' => 'Stories tidak ditemukan',
                ], 404);
            }

            $formattedStories = $stories->map(function ($story) {
                return [
                    'story_id' => $story->id,
                    'title' => $story->title,
                    'slug' => $story->slug,
                    'author' => [
                        'name' => $story->user->fullname,
                        'avatar' => $story->user->avatar,
                    ],
                    'content' => $story->body,
                    'images' => $story->images->map(fn($image) => [
                        'url' => $image->image_url,
                        'identifier' => $image->identifier
                    ]),
                    'category_name' => $story->category->name,
                    'created_at' => $story->created_at->toIso8601String(),
                ];
            });

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'data' => $formattedStories,
                'pagination' => [
                    'total' => $stories->total(),
                    'per_page' => $stories->perPage(),
                    'current_page' => $stories->currentPage(),
                    'last_page' => $stories->lastPage(),
                ],
                'message' => 'Stories berhasil didapatkan',
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

    public function userStories(Request $request)
    {
        try {
            $page = $request->query('page', 1);
            $perPage = $request->query('per_page', 5);

            $stories = Story::with(['user:id,fullname,avatar', 'category:id,name', 'images'])
                ->paginate($perPage, ['*'], 'page', $page);

            $bookmarkedStoryIds = auth()->check()
                ? Bookmark::where('user_id', auth()->user()->unique_id)->pluck('story_id')->toArray()
                : [];

            if ($stories->isEmpty()) {
                return response()->json([
                    'code' => 404,
                    'status' => 'error',
                    'data' => null,
                    'message' => 'Stories tidak ditemukan',
                ], 404);
            }

            $response = collect($stories->items())->map(function ($story) use ($bookmarkedStoryIds) {
                return [
                    'story_id' => $story->id,
                    'title' => $story->title,
                    'slug' => $story->slug,
                    'author' => [
                        'name' => $story->user->fullname,
                        'avatar' => $story->user->avatar ?? asset('default-avatar.jpg'),
                    ],
                    'content' => $story->body,
                    'images' => $story->images->map(function ($image) {
                        return [
                            'url' => $image->image_url,
                            'identifier' => 'story',
                        ];
                    }),
                    'is_bookmark' => in_array($story->id, $bookmarkedStoryIds),
                    'category_name' => $story->category->name ?? 'Uncategorized',
                    'created_at' => $story->created_at->toIso8601String(),
                ];
            });

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'data' => [
                    'stories' => $response,
                    'pagination' => [
                        'current_page' => $stories->currentPage(),
                        'total_pages' => $stories->lastPage(),
                        'per_page' => $stories->perPage(),
                        'total_data' => $stories->total(),
                        'has_more_pages' => $stories->hasMorePages(),
                    ],
                ],
                'message' => 'Berhasil mendapatkan daftar stories user',
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


    public function spesificStories(Request $request, $category)
    {
        try {
            $sortBy = $request->query('sort', 'newest'); // Default newest
            $perPage = $request->query('per_page', 10); // Default 10 per halaman
            $search = $request->query('search');

            $stories = Story::where('is_deleted', false)
                ->with(['user:id,fullname,avatar', 'images', 'category:id,name'])
                ->when($category !== 'all-story', function ($query) use ($category) {
                    $query->whereHas('category', function ($q) use ($category) {
                        $q->where('name', $category);
                    });
                })
                ->when($search, function ($query) use ($search) {
                    $query->where('title', 'like', "%$search%");
                })
                ->when($sortBy === 'ascending', function ($query) {
                    $query->orderBy('title', 'asc');
                })
                ->when($sortBy === 'descending', function ($query) {
                    $query->orderBy('title', 'desc');
                })
                ->when($sortBy === 'newest', function ($query) {
                    $query->orderBy('created_at', 'desc');
                })
                ->when($sortBy === 'popular', function ($query) {
                    $query->withCount('bookmarks')->orderBy('bookmarks_count', 'desc');
                })
                ->paginate($perPage);

            if ($stories->isEmpty()) {
                return response()->json([
                    'code' => 404,
                    'status' => 'error',
                    'data' => [],
                    'message' => 'Belum ada story. Ayo buat story baru!'
                ], 404);
            }

            $formattedStories = $stories->map(function ($story) {

                $currentUser = auth()->user() ?? null;

                return [
                    'story_id' => $story->id,
                    'title' => $story->title,
                    'slug' => $story->slug,
                    'author' => [
                        'name' => $story->user->fullname,
                        'avatar' => $story->user->avatar,
                    ],
                    'content' => $story->body,
                    'images' => $story->images->map(fn($image) => [
                        'url' => $image->image_url,
                        'identifier' => $image->identifier
                    ]),
                    'category_name' => $story->category->name,
                    'is_bookmark' => $currentUser ? Bookmark::where('story_id', $story->id)->where('user_id', auth()->user()->unique_id)->exists() : false,
                ];
            });

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'data' => [
                    'stories' => $formattedStories,
                    'pagination' => [
                        'current_page' => $stories->currentPage(),
                        'total_pages' => $stories->lastPage(),
                        'per_page' => $stories->perPage(),
                        'total_data' => $stories->total(),
                        'has_more_pages' => $stories->hasMorePages(),
                    ],
                ],
                'message' => 'Berhasil mendapatkan daftar stories berdasarkan sorting'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'status' => 'error',
                'data' => null,
                'message' => $e->getMessage(), // Biar kita tahu error-nya apa
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

    // LIA CODE NEW
    public function getStoriesByCategory()
    {
        try {
            $categories = Category::select('id', 'name')
                ->with(['stories' => function ($query) {
                    $query->with('images')  // Eager load images using polymorphic relationship
                        ->join('users', 'stories.user_id', '=', 'users.id')
                        ->select(
                            'stories.id as story_id',
                            'stories.unique_id',
                            'stories.title',
                            'stories.slug',
                            'stories.body',
                            'stories.category_id',
                            'stories.created_at',
                            'users.fullname as author_name',
                            'users.avatar as author_avatar'
                        );
                }])
                ->get();

            // HANDLING WHEN STORY IS EMPTY
            if ($categories->isEmpty()) {
                return response()->json([
                    'code' => 404,
                    'status' => 'error',
                    'data' => [],
                    'message' => 'Belum ada story. Ayo buat story baru!'
                ], 404);
            }

            // Format data agar sesuai dengan struktur JSON yang diinginkan
            $formattedData = $categories->map(function ($category) {
                return [
                    'category_id' => $category->id,
                    'category_name' => $category->name,
                    'stories' => $category->stories->map(function ($story) {
                        return [
                            'story_id' => $story->story_id,
                            'title' => $story->title,
                            'slug' => $story->slug,
                            'author' => [
                                'name' => $story->author_name,
                                'avatar' => $story->author_avatar,
                            ],
                            'content' => $story->body,
                            'images' => $story->images->map(fn($image) => [
                                'url' => $image->image_url,
                                'identifier' => $image->identifier
                            ]),
                            'created_at' => $story->created_at->toIso8601String(),
                        ];
                    }),
                ];
            });

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'data' => $formattedData,
                'message' => 'Berhasil mendapatkan data stories'
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
     * Store a newly created resource in storage.
     */
    public function store(StoreStoryRequest $request)
    {
        try {
            $validatedData = $request->validated();
            // AMBIL INFORMASI MENGENAI PENGGUNA YANG LOGIN
            $user = auth()->user();

            // Validasi input
            DB::beginTransaction();
            $slug = Str::slug($validatedData['title']);

            // Simpan story ke database
            $story = Story::create([
                'unique_id' => Cuid::make(),
                'title' => $validatedData['title'],
                'slug' => $slug,
                'body' => $validatedData['body'],
                'user_id' => auth()->id(), // Ambil ID user yang sedang login
                'category_id' => $validatedData['category_id'],
                'is_deleted' => false
            ]);

            $contentImage = [];
            // Simpan multiple images jika ada
            if (isset($validatedData['images'])) {
                foreach ($validatedData['images'] as $imageUrl) {
                    $contentImage = [
                        'related_unique_id' => $story->unique_id,
                        'related_type' => Story::class,
                        'image_url' => $imageUrl,
                        'identifier' => $request->identifier,
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                }

                MultipleImage::insert($contentImage);
            }

            // BUAT KUSTOM MESSAGE SUPAYA PESAN NOTIFIKASI NYA JADI MENARIK
            $message = 'Yuhuuu 🥳 Ada story baru nich "' . $validatedData['title'] . '". Kuy cek 🏃‍➡️🏃‍➡️';

            // SIMPAN NOTIFIKASI NYA DI DATABASE DULU
            Notification::create([
                'author_id' => $user->unique_id,
                'message' => $message
            ]);

            $contents = [
                'message' => $message,
                'slug' => $slug
            ];

            // JIKA SUDAH DI TAMBAHKAN LALU KIRIM NOTIFIKASI KE USER
            // StoryCreateEvent::dispatch($contents);
            DB::commit();

            return response()->json([
                'code' => 201,
                'status' => 'success',
                'data' => null,
                'message' => 'Story berhasil ditambahkan',
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'code' => 422,
                'status' => 'error',
                'data' => null,
                'message' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'code' => 500,
                'status' => 'error',
                'data' => null,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // SEBUAH METHOD UNTUK MERUBAH DATA STORY MENJADI BENTUK YANG DIINGINKAN UNTUK MENGHINDARI DUPLIKASI DATA
    private function transformStoryData($story)
    {
        $isBookmarked = Bookmark::where('story_id', $story->id)->exists() ?? false;

        return [
            'story_id' => $story->id,
            'title' => $story->title,
            'slug' => $story->slug,
            'author' => [
                'name' => $story->user->fullname,
                'avatar' => $story->user->avatar,
            ],
            'content' => $story->body,
            'images' => $story->images->map(fn($image) => [
                'url' => $image->image_url,
                'identifier' => $image->identifier
            ]),
            'is_bookmark' => $isBookmarked,
            'category_id' => $story->category_id,
            'category_name' => $story->category->name,
            'created_at' => $story->created_at->toIso8601String(),
        ];
    }

    /**
     * Display the specified resource.
     */
    public function show(string $slug)
    {
        try {
            $story = Story::with(['category:id,name', 'user:id,fullname,avatar', 'images'])
                ->where('slug', $slug)->first();

            if (!$story) {
                return response()->json([
                    'code' => 404,
                    'status' => 'error',
                    'data' => null,
                    'message' => 'Story tidak ditemukan. Yuk buat story baru!'
                ], 404);
            }

            $similarStories = Story::with(['category:id,name', 'user:id,fullname,avatar', 'images'])
                ->where('category_id', $story->category_id)
                ->where('id', '!=', $story->id)
                ->limit(5)
                ->get();

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'data' => [
                    'story' => $this->transformStoryData($story),
                    'similar_stories' => $similarStories->map(fn($story) => $this->transformStoryData($story)),
                ],
                'message' => 'Data Story berhasil diambil.'
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
     * Show the form for editing the specified resource.
     */
    public function edit(Story $story)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateStoryRequest $request, $unique_id)
    {
        try {
            $validatedData = $request->validated();
            $story = Story::where('unique_id', $unique_id)->first();

            if (!$story) {
                return response()->json([
                    'code' => 404,
                    'status' => 'error',
                    'data' => null,
                    'message' => 'Story tidak ditemukan. Coba cek kembali!'
                ], 404);
            }

            DB::beginTransaction();
            $story->update([
                'title' => $validatedData['title'],
                'slug' => Str::slug($validatedData['title']),
                'body' => $validatedData['body'],
                'category_id' => $validatedData['category_id'],
            ]);

            if (isset($validatedData['images'])) {
                $existingImages = MultipleImage::where('related_unique_id', $story->unique_id)
                    ->where('related_type', Story::class)
                    ->pluck('image_url')
                    ->toArray();

                $newImages = $validatedData['images'];

                MultipleImage::where('related_unique_id', $story->unique_id)
                    ->where('related_type', Story::class)
                    ->whereNotIn('image_url', $newImages)
                    ->delete();

                $insertImages = array_diff($newImages, $existingImages);
                $multipleImages = array_map(fn($imageUrl) => [
                    'related_unique_id' => $story->unique_id,
                    'related_type' => Story::class,
                    'image_url' => $imageUrl,
                ], $insertImages);

                MultipleImage::insert($multipleImages);
            }
            DB::commit();

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'data' => $story,
                'message' => 'Story berhasil diperbarui!'
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'code' => 422,
                'status' => 'error',
                'data' => null,
                'message' => 'Validasi gagal. Silakan periksa kembali input Anda.'
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'code' => 500,
                'status' => 'error',
                'data' => null,
                'message' => 'Terjadi kesalahan, coba lagi nanti.'
            ], 500);
        }
    }


    public function deleteStory(Request $request, $unique_id)
    {
        try {
            // Cari story berdasarkan unique_id dan user_id yang sedang login
            $story = Story::where('unique_id', $unique_id)
                ->where('user_id', $request->user()->id) // Validasi kepemilikan
                ->where('is_deleted', 0) // Pastikan story belum dihapus
                ->first();

            if (!$story) {
                return response()->json([
                    'code' => 404,
                    'status' => 'error',
                    'data' => [
                        'kk' => $unique_id,
                        'hh' => $request->user()->unique_id
                    ],
                    'message' => 'Story tidak ditemukan atau Anda tidak memiliki izin untuk menghapusnya.',
                ], 404);
            }

            // Update kolom is_deleted
            $story->is_deleted = 1;
            $story->save();

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'message' => 'Story berhasil dihapus.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat menghapus story.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }



    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
    }
}

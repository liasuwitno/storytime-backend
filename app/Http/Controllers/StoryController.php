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
    public function index(Request $request)
    {
        try {
            // Ambil parameter sort, default adalah 'newest'
            $sort = $request->query('sort', 'newest');

            // Ambil parameter search (untuk pencarian berdasarkan title)
            $search = $request->query('search', null);

            // Ambil parameter page dan tentukan jumlah item per halaman
            $perPage = 10; // Default jumlah per halaman
            $page = $request->query('page', 1);

            // Query dasar
            $query = Story::where('is_deleted', false)
                ->join('users', 'stories.user_id', '=', 'users.id')
                ->join('categories', 'stories.category_id', '=', 'categories.id')
                ->select('stories.*', 'users.name as user_name', 'categories.name as category_name');

            // Tambahkan filter untuk pencarian (jika ada)
            if ($search) {
                $query->where('stories.title', 'like', '%' . $search . '%');
            }

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


    public function userStories(Request $request)
    {
        try {
            $user = auth()->user(); // Dapatkan user yang login (bisa null jika belum login)

            // Query semua story yang tidak dihapus dengan relasi user, images, category
            $stories = Story::with(['user', 'category', 'images'])->get();

            $bookmarkedStoryIds = auth()->check()
                ? Bookmark::where('user_id', auth()->user()->unique_id)->pluck('story_id')->toArray()
                : [];

            $response = $stories->map(function ($story) use ($bookmarkedStoryIds) {
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

            return response()->json($response);
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

    // LIA CODE NEW
    public function getStoriesByCategory()
    {
        try {
            // Ambil data kategori dengan maksimal 3 stories per kategori
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
            StoryCreateEvent::dispatch($contents);
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


    /**
     * Display the specified resource.
     */
    public function show(string $slug)
    {
        try {
            $story = Story::with(['category:id,name', 'user:id,fullname,avatar', 'images'])
                ->where('slug', $slug)
                ->first();

            if (!$story) {
                return response()->json([
                    'code' => 404,
                    'status' => 'error',
                    'data' => null,
                    'message' => 'Story tidak ditemukan. Yuk buat story baru!'
                ], 404);
            }

            // RESPONSE YANG DIHARAPKAN
            // [
            //     {
            //         "story_id": 1,
            //         "title": "Bergelud kita backend",
            //         "slug": "bergelud-kita-backend",
            //         "author": {
            //             "name": "Kucing Hitam",
            //             "avatar": "https://i.pinimg.com/736x/6c/a0/db/6ca0db73c407b5e02638dea2260dc952.jpg"
            //         },
            //         "content": "Kenapa frontend selalu dikucilkan. Eh tapi kamu tau gak sih gosip di INSTIKI? Dosen nya loh suka ...",
            //         "images": [
            //             {
            //                 "url": "https://i.pinimg.com/736x/2b/04/01/2b0401bf88244fac037c2b1627b3118c.jpg",
            //                 "identifier": "story"
            //             }
            //         ],
            //         "created_at": "2025-02-07T14:16:53+08:00"
            //     }
            // ]


            $relatedData = [
                ...$story->toArray(),
                // ADD NEW KEY
                // 'category_name' => $story->category->name,
                'images' => $story->images->map(function ($image) {
                    return [
                        'id' => $image->id,
                        'url' => $image->image_url,
                        'related_unique_id' => $image->related_unique_id,
                        'identifier' => $image->identifier
                    ];
                }),
            ];

            // Cari similar stories berdasarkan kategori, kecuali story saat ini
            $similarStories = Story::where('category_id', $story->category_id)
                ->where('id', '!=', $story->id)
                ->limit(5)
                ->get();

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'data' => [
                    'story' => $relatedData,
                    'similar_stories' => $similarStories,
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

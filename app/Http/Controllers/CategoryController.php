<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Ramsey\Uuid\Type\Integer;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $categories = Category::all();

            if ($categories->isEmpty()) {
                return response()->json([
                    'code' => 404,
                    'status' => 'error',
                    'data' => null,
                    'message' => 'Categories tidak ditemukan',
                ], 404);
            }

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'data' => $categories,
                'message' => 'Categories berhasil didapatkan',
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
    public function store(Request $request)
    {
        try {
            $request->validate(
                [
                    'name' => 'required|string|unique:categories,name'
                ],
                [
                    'name.required' => 'Nama category wajib diisi.',
                    'name.string' => 'Nama category harus berupa teks.'
                ]
            );
            Category::create($request->all()); //req semua fillable yang ada di model
            return response()->json([
                'code' => 201,
                'status' => 'success',
                'data' => null,
                'message' => 'Categories berhasil didapatkan',
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

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $categoryId)
    {
        try {
            $request->validate(
                [
                    'name' => 'required|string|unique:categories,name,' . $categoryId
                ],
                [
                    'name.required' => 'Nama category wajib diisi.',
                    'name.string' => 'Nama category harus berupa teks.',
                    'name.unique' => 'Nama category sudah digunakan.'
                ]
            );
            $category = Category::findOrFail($categoryId); //findOrFail untuk ngecek apakah id nya sudah sama dengan id target

            $category->update($request->only(['name']));

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'data' => null,
                'message' => 'Category Field berhasil di update',
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
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        //
    }
}

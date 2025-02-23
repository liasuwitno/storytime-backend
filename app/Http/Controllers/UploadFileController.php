<?php

namespace App\Http\Controllers;

use CaliCastle\Cuid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class UploadFileController extends Controller
{
    public function uploadFile(Request $request, string $folder)
    {
        try {
            // Validasi input
            $request->validate([
                'files' => [
                    'required',
                    'array',
                    'min:1', // Minimal 1 file
                ],
                'files.*' => [
                    'file',
                    'max:1024',
                    'mimes:jpg,jpeg,png,webp,svg',
                ],
                'identifier' => 'required|string|max:80', // Identifier untuk file
            ], [
                'files.*.file' => 'Each uploaded file must be a valid file',
            ]);

            // Ambil semua file
            $files = $request->file('files');

            // Tempat untuk menyimpan URL file yang berhasil diupload
            $uploadedFiles = [];

            // Proses setiap file
            foreach ($files as $file) {
                if (!$file->isValid()) {
                    return response()->json([
                        'code' => 422,
                        'status' => 'error',
                        'data' => null,
                        'message' => 'Salah satu file tidak valid'
                    ], 422);
                }

                // Generate nama file menggunakan Cuid
                $fileName = Cuid::make() . ".{$file->extension()}";

                // Simpan file ke direktori yang ditentukan
                $resultFile = $file->storeAs($folder, $fileName);
                $baseUrl = Storage::url($resultFile);

                // Tambahkan URL ke array hasil
                $uploadedFiles[] = $baseUrl;
            }

            // Response berhasil
            return response()->json([
                'code' => 201,
                'status' => 'success',
                'data' => [
                    'urls' => $uploadedFiles, // Semua URL file
                    'identifier' => $request->identifier,
                ],
                'message' => 'Semua file berhasil diupload'
            ], 201);
        } catch (ValidationException $e) {
            // Validasi gagal
            return response()->json([
                'code' => 422,
                'status' => 'error',
                'data' => null,
                'message' => $e->getMessage()
            ], 422);
        } catch (\Exception $e) {
            // Error lainnya
            return response()->json([
                'code' => 500,
                'status' => 'error',
                'data' => null,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function uploadFileSingle(Request $request, string $folder)
    {
        try {
            $request->validate([
                'file' => [
                    'required',
                    'file',
                    'max:1024',
                    'mimes:jpg,jpeg,png,webp',
                ],
                'identifier' => 'required|string|max:80',
            ]);

            $file = $request->file('file');

            if (!$file->isValid()) {
                return response()->json([
                    'code' => 422,
                    'status' => 'error',
                    'data' => null,
                    'message' => 'File tidak valid'
                ], 422);
            }

            $fileName = Cuid::make();
            $resultFile = $file->storeAs($request->folder, "{$fileName}.{$file->extension()}");

            $baseUrl = Storage::url($resultFile);

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'data' => [
                    'urls' => $baseUrl,
                    'identifier' => $request->identifier
                ],
                'message' => 'File berhasil di upload'
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'code' => 422,
                'status' => 'error',
                'data' => null,
                'message' => $e->getMessage()
            ], 422);
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

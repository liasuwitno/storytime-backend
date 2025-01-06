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
            $request->validate([
                'idetifier' => 'required|string',
                'file' => [
                    'required',
                    'file',
                    'max:1024',
                    'mimes:jpg,jpeg,png,webp,svg',
                    function ($attribute, $value, $fail) { //pengecekan apakah file yang dikirim adalah gambar
                        $mimeType = mime_content_type($value->getRealPath());
                        $validMimeTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg', 'image/svg'];

                        if (!in_array($mimeType, $validMimeTypes)) {
                            $fail('The ' . $attribute . ' must be a valid image.');
                        }
                    }
                ],
            ]);

            // $file ini akan berisi file yang diupload oleh user
            $file = $request->file('file');

            // Jika file yang diupload tidak valid maka akan mengembalikan response error 422
            if (!$file->isValid()) {
                return response()->json([
                    'code' => 422,
                    'status' => 'error',
                    'data' => null,
                    'message' => 'File tidak valid'
                ], 422);
            }

            // Membuat nama file baru dengan menggunakan Cuid yang akan digunakan sebagai nama file
            $fileName = Cuid::make();
            
            // Menyimpan file yang diupload ke dalam `folder(Folder ini bentuk dinamis bisa jadi user, story dll)` yang ditentukan dengan nama file yang baru dan ekstensi file yang diupload oleh user
            $resultFile = $file->storeAs($folder, "{$fileName}.{$file->extension()}");

            // Mengambil URL dari full dari file yang diupload, URL ini akan digunakan untuk menampilkan file yang diupload oleh user
            $baseUrl = Storage::url($resultFile);

            // Mengembalikan response berupa JSON yang berisi data file yang diupload oleh user
            return response()->json([
                'code' => 200,
                'status' => 'success',
                'data' => [
                    'url' => $baseUrl,
                    'identifier' => $request->identifier,
                ],
                'message' => 'File berhasil di upload'
            ], 200);
        } catch (ValidationException $e) {
            // SEBUAH VALIDASI JIKA FILE YANG DIUPLOAD TIDAK SESUAI DENGAN YANG DIIZINKAN
            return response()->json([
                'code' => 422,
                'status' => 'error',
                'data' => null,
                'message' => $e->getMessage()
            ], 422);
        } catch (\Exception $e) {
            // SEBUAH VALIDASI JIKA TERJADI ERROR LAINNYA
            return response()->json([
                'code' => 500,
                'status' => 'error',
                'data' => null,
                'message' => $e->getMessage()
            ], 500);
        }
    }

}

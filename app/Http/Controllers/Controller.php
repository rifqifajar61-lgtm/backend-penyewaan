<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Validation\Validator;

abstract class Controller
{
    /**
     * Response ketika request berhasil.
     */
    protected function successResponse(string $message, $data = null, int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ], $code);
    }

    /**
     * Response ketika data tidak ditemukan (404).
     */
    protected function notFoundResponse(string $message = 'Data tidak ditemukan'): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data'    => null,
        ], 404);
    }

    /**
     * Response ketika validasi input gagal (422).
     */
    protected function validationErrorResponse(Validator $validator, string $message): JsonResponse
    {
        $errors = [];
        foreach ($validator->errors()->messages() as $field => $messages) {
            $errors[] = [$field => $messages[0]];
        }

        return response()->json([
            'success' => false,
            'message' => $message,
            'data'    => null,
            'errors'  => $errors,
        ], 422);
    }

    /**
     * Response ketika terjadi error di server (500).
     */
    protected function serverErrorResponse(\Throwable $e, string $message = 'There error in Internal Server'): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data'    => null,
            'errors'  => $e->getMessage(),
        ], 500);
    }

    /**
     * Response generik untuk kegagalan aksi (mis. gagal hapus karena FK constraint).
     */
    protected function failResponse(string $message, int $code = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data'    => null,
        ], $code);
    }
}
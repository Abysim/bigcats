<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Validator;

abstract class Controller
{
    protected function validationErrorResponse(Validator $validator): JsonResponse
    {
        return $this->errorResponse($validator->messages()->all());
    }

    protected function errorResponse(array|string $errors, int $status = 400): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'errors' => (array) $errors,
        ], $status);
    }

    protected function syncTags(Request $request, Model $model): void
    {
        $model->tags()->sync(Tag::whereIn('short_name', $request->get('tags'))->pluck('id'));
    }
}

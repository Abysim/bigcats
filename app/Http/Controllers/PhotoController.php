<?php

namespace App\Http\Controllers;

use App\Models\Photo;
use App\Models\Tag;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PhotoController extends Controller
{
    public function create(Request $request)
    {
        $validator = $this->validateRequest($request);
        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        $photo = $this->createPhoto($request);

        try {
            $photo->save();
        } catch (QueryException $e) {
            if ($e->getCode() === '23000') {
                return $this->photoExistsErrorResponse();
            }
            throw $e;
        }

        $this->syncTags($request, $photo);

        return $this->successResponse($photo);
    }

    protected function validateRequest(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'author_name' => 'required|string|max:255',
            'flickr_link' => 'required|url|max:1024',
            'thumbnail_url' => 'required|url|max:1024',
            'thumbnail_width' => 'required|integer|min:1',
            'thumbnail_height' => 'required|integer|min:1',
            'tags' => 'required|array',
            'tags.*' => 'required|string|max:128',
        ];

        return Validator::make($request->all(), $rules);
    }

    protected function validationErrorResponse($validator)
    {
        $messages = $validator->messages();
        $errors = $messages->all();

        return response()->json([
            'status' => 'error',
            'errors' => $errors,
        ], 400);
    }

    protected function photoExistsErrorResponse()
    {
        return response()->json([
            'status' => 'error',
            'errors' => ['Photo with this Flickr link already exists'],
        ], 400);
    }

    protected function createPhoto(Request $request)
    {
        $photo = new Photo();
        $photo->name = $request->get('name');
        $photo->author_name = $request->get('author_name');
        $photo->flickr_link = $request->get('flickr_link');
        $photo->thumbnail_url = $request->get('thumbnail_url');
        $photo->thumbnail_width = $request->get('thumbnail_width');
        $photo->thumbnail_height = $request->get('thumbnail_height');
        $photo->is_published = true;

        return $photo;
    }

    protected function syncTags(Request $request, Photo $photo)
    {
        $photo->tags()->sync(Tag::whereIn('short_name', $request->get('tags'))->pluck('id'));
    }

    protected function successResponse(Photo $photo)
    {
        return response()->json([
            'status' => 'success',
            'flickr_link' => $photo->flickr_link,
        ]);
    }
}

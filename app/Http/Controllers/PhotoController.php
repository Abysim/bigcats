<?php

namespace App\Http\Controllers;

use App\Models\Photo;
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

        if (Photo::where('flickr_link', $request->get('flickr_link'))->exists()) {
            return $this->errorResponse('Photo with this Flickr link already exists');
        }

        $photo = $this->createPhoto($request);
        $photo->save();

        $this->syncTags($request, $photo);

        return response()->json([
            'status' => 'success',
            'flickr_link' => $photo->flickr_link,
        ]);
    }

    protected function validateRequest(Request $request)
    {
        return Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'author_name' => 'required|string|max:255',
            'flickr_link' => 'required|url|max:1024',
            'thumbnail_url' => 'required|url|max:1024',
            'thumbnail_width' => 'required|integer|min:1',
            'thumbnail_height' => 'required|integer|min:1',
            'tags' => 'required|array|min:1',
            'tags.*' => 'required|string|max:128',
        ]);
    }

    protected function createPhoto(Request $request)
    {
        $photo = new Photo($request->only([
            'name', 'author_name', 'flickr_link',
            'thumbnail_url', 'thumbnail_width', 'thumbnail_height',
        ]));
        $photo->is_published = true;

        return $photo;
    }
}

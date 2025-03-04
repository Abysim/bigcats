<?php

namespace App\Http\Controllers;

use App\Filament\App\Resources\NewsResource;
use App\Models\News;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class NewsController extends Controller
{
    public function create(Request $request)
    {
        $validator = $this->validateRequest($request);
        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        if ($this->newsExists($request)) {
            return $this->newsExistsErrorResponse();
        }

        $news = $this->createNews($request);
        if (!$this->saveImage($request, $news)) {
            return $this->imageSaveErrorResponse();
        }

        $news->save();
        $news->refresh();
        $this->syncTags($request, $news);

        return $this->successResponse($news);
    }

    protected function validateRequest(Request $request)
    {
        $rules = [
            'title' => 'required|string',
            'content' => 'required|string',
            'date' => 'required|date',
            'image' => 'required|url',
            'image_caption' => 'required|string',
            'is_original' => 'required|boolean',
            'source_url' => 'url',
            'source_name' => 'string',
            'author' => 'string',
            'tags' => 'required|array',
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

    protected function newsExists(Request $request)
    {
        return News::query()
            ->where('title', $request->title)
            ->where('date', $request->date)
            ->exists();
    }

    protected function newsExistsErrorResponse()
    {
        return response()->json([
            'status' => 'error',
            'errors' => ['News with this title and date already exists'],
        ], 400);
    }

    protected function createNews(Request $request)
    {
        $news = new News();
        $news->title = $request->get('title');
        $news->slug = Str::slug($request->get('title'), language: config('app.locale'));
        $news->content = Str::markdown($request->get('content'));
        $news->date = $request->get('date');
        $news->image_caption = $request->get('image_caption');
        $news->is_original = $request->get('is_original');
        $news->is_published = true;
        $news->source_url = $request->get('source_url');
        $news->source_name = $request->get('source_name');
        $news->author = $request->get('author');

        return $news;
    }

    protected function saveImage(Request $request, News $news)
    {
        $image = Http::get($request->get('image'))->body();
        $filename = 'news/' . md5($image) . '.' . explode('#', explode('?', Str::afterLast($request->get('image'), '.'))[0])[0] ?? 'jpg';
        if (Storage::disk('public')->put($filename, $image)) {
            $news->image = $filename;
            return true;
        }

        return false;
    }

    protected function imageSaveErrorResponse()
    {
        return response()->json([
            'status' => 'error',
            'errors' => ['Failed to save image'],
        ], 500);
    }

    protected function syncTags(Request $request, News $news)
    {
        $news->tags()->sync(Tag::whereIn('name', $request->get('tags'))->pluck('id'));
    }

    protected function successResponse(News $news)
    {
        return response()->json([
            'status' => 'success',
            'image' => $news->image,
            'url' => NewsResource::getUrl('view', [
                'year' => $news->year,
                'month' => $news->month,
                'day' => $news->day,
                'record' => $news->slug,
            ], panel: 'app'),
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Filament\App\Resources\NewsResource;
use App\Models\News;
use App\Traits\DownloadsImages;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class NewsController extends Controller
{
    use DownloadsImages;

    public function create(Request $request)
    {
        $validator = $this->validateRequest($request);
        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        if ($this->newsExists($request)) {
            return $this->errorResponse('News with this title and date already exists');
        }

        $news = $this->createNews($request);
        $imagePath = $this->downloadAndStoreImage($request->get('image'), 'news');
        if (!$imagePath) {
            return $this->errorResponse('Failed to save image', 500);
        }
        $news->image = $imagePath;

        $news->save();
        $news->refresh();
        $this->syncTags($request->get('tags'), $news);

        return $this->successResponse($news);
    }

    protected function validateRequest(Request $request)
    {
        return Validator::make($request->all(), [
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
        ]);
    }

    protected function newsExists(Request $request)
    {
        return News::query()
            ->where('title', $request->title)
            ->where('date', $request->date)
            ->exists();
    }

    protected function createNews(Request $request)
    {
        $news = new News();
        $news->title = $request->get('title');
        $news->slug = Str::slug($request->get('title'), language: config('app.locale'));
        $news->content = $this->safeMarkdown($request->get('content'));
        $news->date = $request->get('date');
        $news->image_caption = $request->get('image_caption');
        $news->is_original = $request->get('is_original');
        $news->is_published = true;
        $news->source_url = $request->get('source_url');
        $news->source_name = $request->get('source_name');
        $news->author = $request->get('author');

        return $news;
    }

    protected function successResponse(News $news)
    {
        return response()->json([
            'status' => 'success',
            'image' => $news->image ? asset(Storage::url($news->image)) : '',
            'url' => NewsResource::getUrl('view', [
                'year' => $news->year,
                'month' => $news->month,
                'day' => $news->day,
                'record' => $news->slug,
            ], panel: 'app'),
        ]);
    }
}

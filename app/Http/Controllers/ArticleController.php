<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Database\QueryException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ArticleController extends Controller
{
    private const ALLOWED_IMAGE_MIMES = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
    ];

    public function create(Request $request)
    {
        $validator = $this->validateRequest($request);
        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        $frontpage = Article::frontpage()->first();
        if (!$frontpage) {
            return $this->errorResponse('Frontpage article not found', 500);
        }

        $article = $this->createArticle($request, $frontpage);
        if (!$this->saveImage($request, $article)) {
            return $this->errorResponse('Failed to save image', 500);
        }

        try {
            DB::transaction(function () use ($article, $request) {
                $article->save();
                $this->syncTags($request->get('tags'), $article);
            });
        } catch (QueryException $e) {
            if ($e->errorInfo[1] === self::MYSQL_DUPLICATE_ENTRY) {
                return $this->errorResponse('An article with a similar title already exists');
            }

            throw $e;
        }

        $article->setRelation('parent', $frontpage);

        return $this->successResponse($article);
    }

    protected function validateRequest(Request $request)
    {
        return Validator::make($request->all(), [
            'title' => 'required|string|max:500',
            'content' => 'required|string|max:100000',
            'image' => ['required', 'url', 'max:2048', 'regex:/^https:\/\//i'],
            'image_caption' => 'required|string|max:500',
            'source_url' => 'url|max:1024',
            'source_name' => 'string|max:255',
            'tags' => 'required|array|min:1|max:20',
            'tags.*' => 'required|string|max:128',
        ]);
    }

    protected function createArticle(Request $request, Article $frontpage)
    {
        $article = new Article();
        $article->parent_id = $frontpage->id;
        $article->title = $request->get('title');
        $article->slug = $this->generateUniqueSlug($request->get('title'), $frontpage->id);
        $article->content = Str::markdown($request->get('content'), [
            'html_input' => 'escape',
            'allow_unsafe_links' => false,
        ]);
        $article->image_caption = $request->get('image_caption');
        $article->source_url = $request->get('source_url');
        $article->source_name = $request->get('source_name');
        $article->is_published = false;

        return $article;
    }

    protected function generateUniqueSlug(string $title, int $parentId): string
    {
        $baseSlug = Str::slug($title, language: config('app.locale'));
        $slug = $baseSlug;
        $counter = 2;

        while (Article::where('parent_id', $parentId)->where('slug', $slug)->exists()) {
            if ($counter > 100) {
                $slug = $baseSlug . '-' . Str::random(6);
                break;
            }
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    protected function saveImage(Request $request, Article $article)
    {
        try {
            $response = Http::timeout(10)->get($request->get('image'));
        } catch (ConnectionException) {
            return false;
        }

        if ($response->failed()) {
            return false;
        }

        $image = $response->body();

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $extension = self::ALLOWED_IMAGE_MIMES[$finfo->buffer($image)] ?? null;
        if ($extension === null) {
            return false;
        }

        $filename = 'articles/' . md5($image) . '.' . $extension;
        if (Storage::disk('public')->put($filename, $image)) {
            $article->image = $filename;
            return true;
        }

        return false;
    }

    protected function successResponse(Article $article)
    {
        return response()->json([
            'status' => 'success',
            'image' => $article->image ? asset(Storage::url($article->image)) : '',
            'url' => $article->getUrl(),
        ]);
    }
}

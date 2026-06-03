<?php

namespace Tests\Feature\Api;

use App\Models\Tag;
use App\Models\TagType;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

trait ApiTestHelpers
{
    // Valid JFIF header for finfo() MIME detection in DownloadsImages trait
    private const FAKE_JPEG = '/9j/4AAQSkZJRgABAQEASABIAAD/2wBDAP//AP//AP//AP//AP//AP//AP//AP//AP//AP//AP//AP//AP//AP//AP//AP//AP//AP//AP//AP//AP//AP//AP//AP//2wBDAP//AP//AP//AP//AP//AP//AP//AP//AP//AP//AP//AP//AP//AP//AP//AP//AP//AP//AP//AP//AP//AP//AP//AP//wAARCAABAAEDASIAAhEBAxEB/8QAFAABAAAAAAAAAAAAAAAAAAAAB//EABQQAQAAAAAAAAAAAAAAAAAAAAD/xAAUAQEAAAAAAAAAAAAAAAAAAAAA/8QAFBEBAAAAAAAAAAAAAAAAAAAAAP/aAAwDAQACEQMRAD8AVQP/2Q==';

    private User $user;
    private string $token;

    protected function setUpAuth(): void
    {
        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('test')->plainTextToken;
    }

    protected function setUpFakeImageDownload(): void
    {
        Http::fake(['*' => Http::response(
            base64_decode(self::FAKE_JPEG),
            200,
            ['Content-Type' => 'image/jpeg'],
        )]);
        Storage::fake('public');
    }

    private function createTag(string $name): Tag
    {
        $tagType = TagType::factory()->create();

        return Tag::factory()->create(['name' => $name, 'parent_id' => null, 'type_id' => $tagType->id]);
    }
}

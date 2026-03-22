<?php

namespace Tests\Feature\Middleware;

use App\Http\Middleware\HttpRedirectMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Tests\TestCase;

class HttpRedirectMiddlewareTest extends TestCase
{
    private HttpRedirectMiddleware $middleware;
    private string $originalEnv;

    protected function setUp(): void
    {
        parent::setUp();

        $this->middleware = new HttpRedirectMiddleware();
        $this->originalEnv = App::environment();
    }

    protected function tearDown(): void
    {
        App::detectEnvironment(fn () => $this->originalEnv);

        parent::tearDown();
    }

    private function setProductionEnvironment(): void
    {
        App::detectEnvironment(fn () => 'production');
        config(['app.url' => 'https://www.bigcats.example.com']);
    }

    private function dispatchRequest(string $url, array $headers = [], bool $https = false): \Symfony\Component\HttpFoundation\Response
    {
        $request = Request::create($url, 'GET');
        foreach ($headers as $key => $value) {
            $request->headers->set($key, $value);
        }
        if ($https) {
            $request->server->set('HTTPS', 'on');
        }

        return $this->middleware->handle($request, fn () => response('OK'));
    }

    public function test_does_not_redirect_in_testing_environment(): void
    {
        $response = $this->dispatchRequest('http://localhost/test', ['host' => 'localhost']);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_redirects_http_to_https_in_production(): void
    {
        $this->setProductionEnvironment();

        $response = $this->dispatchRequest(
            'http://www.bigcats.example.com/news',
            ['host' => 'www.bigcats.example.com'],
        );

        $this->assertEquals(301, $response->getStatusCode());
        $this->assertStringStartsWith('https://www.bigcats.example.com/news', $response->headers->get('Location'));
    }

    public function test_redirects_wrong_host_in_production(): void
    {
        $this->setProductionEnvironment();

        $response = $this->dispatchRequest(
            'https://wrong-host.com/page',
            ['host' => 'wrong-host.com'],
            https: true,
        );

        $this->assertEquals(301, $response->getStatusCode());
        $this->assertStringContainsString('www.bigcats.example.com', $response->headers->get('Location'));
    }

    public function test_passes_through_correct_https_request_in_production(): void
    {
        $this->setProductionEnvironment();

        $response = $this->dispatchRequest(
            'https://www.bigcats.example.com/page',
            ['host' => 'www.bigcats.example.com'],
            https: true,
        );

        $this->assertEquals(200, $response->getStatusCode());
    }
}

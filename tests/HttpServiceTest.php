<?php

namespace Tests;

use Etdte\SvcHttp\ConfigurationException;
use Etdte\SvcHttp\HttpService;
use Etdte\SvcHttp\ProvidesConfiguration;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\RequestException;
use Orchestra\Testbench\TestCase;

class HttpServiceTest extends TestCase
{
    /** @test */
    public function itInitializesWithoutConfigurationObject()
    {
        $service = new TestService(null);

        $this->assertInstanceOf(HttpService::class, $service, 'Initialization thrown exception');
    }

    /** @test */
    public function itInitializesWithProperConfigurationObject()
    {
        $service = new TestService(new TestConfiguration());

        $this->assertInstanceOf(HttpService::class, $service, 'Initialization gone wrong');
    }

    /** @test */
    public function itThrowsExceptionWhenWrongObjectPassedAsConfiguration()
    {
        $this->expectException(\TypeError::class);

        new TestService(new class () {
        });
    }

    /** @test */
    public function itThrowsExceptionWhenConfigurationExistsAndNotSettled()
    {
        $configuration = \Mockery::mock(TestConfiguration::class, function ($mock) {
            $mock->shouldReceive('hasConfiguration')->andReturnTrue();
            $mock->shouldReceive('settled')->andReturnFalse();
        })->makePartial();

        $service = new TestService($configuration);

        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('Service is not configured properly, check configuration.');

        $service->getRequest();
    }

    /** @test */
    public function itSendsHttpGetRequestsProperly()
    {
        $service = new TestService(new TestConfiguration());

        \Http::fake();

        $service->getRequest();
        \Http::assertSent(function (Request $request) {
            $this->assertEquals('https://example.com/path?foo=bar', $request->url(), 'Wrong path was used');
            $this->assertEquals(['foo' => 'bar'], $request->data(), 'Wrong data was sent');
            $this->assertEquals('Bearer token', $request->header('Authorization')[0], 'Bearer token was not set');

            return $request->method() === 'GET';
        });
    }

    /** @test */
    public function itSendsHttpPostRequestsProperly()
    {
        $service = new TestService(new TestConfiguration());

        \Http::fake();

        $service->postRequest();

        \Http::assertSent(function (Request $request) {
            $this->assertEquals('https://example.com/path', $request->url(), 'Wrong path was used');
            $this->assertEquals(['foo' => 'bar'], $request->data(), 'Wrong data was sent');
            $this->assertEquals('Bearer token', $request->header('Authorization')[0], 'Bearer token was not set');

            return $request->method() === 'POST';
        });
    }

    /** @test */
    public function itSendsHttpPutRequestsProperly()
    {
        $service = new TestService(new TestConfiguration());

        \Http::fake();

        $service->putRequest();

        \Http::assertSent(function (Request $request) {
            $this->assertEquals('https://example.com/path', $request->url(), 'Wrong path was used');
            $this->assertEquals(['foo' => 'bar'], $request->data(), 'Wrong data was sent');
            $this->assertEquals('Bearer token', $request->header('Authorization')[0], 'Bearer token was not set');

            return $request->method() === 'PUT';
        });
    }

    /** @test */
    public function itSendsHttpDeleteRequestsProperly()
    {
        $service = new TestService(new TestConfiguration());

        \Http::fake();

        $service->deleteRequest();

        \Http::assertSent(function (Request $request) {
            $this->assertEquals('https://example.com/path', $request->url(), 'Wrong path was used');
            $this->assertEquals('Bearer token', $request->header('Authorization')[0], 'Bearer token was not set');

            return $request->method() === 'DELETE';
        });
    }

    /** @test */
    public function itReplacesTokenTypeWithProvidedString()
    {
        $service = new TestService(new TestConfiguration());

        \Http::fake();

        $service->requestWithTokenTypeChange();

        \Http::assertSent(function (Request $request) {
            $this->assertEquals('Yolo token', $request->header('Authorization')[0], 'Bearer token was not set');

            return $request->method() === 'POST';
        });
    }

    /** @test */
    public function itReturnsJsonResponseWhenItIsPassable()
    {
        $service = new TestService(new TestConfiguration());

        \Http::fake([
            '*' => \Http::response(['bar' => 'baz'], 200),
        ]);

        $response = $service->getRequest();

        $this->assertEquals(['bar' => 'baz'], $response);
    }

    /** @test */
    public function itReturnsArrayWithResponseBodyWhenResponseIsNotJson()
    {
        $service = new TestService(new TestConfiguration());

        \Http::fake([
            '*' => \Http::response('some utterly ugly string right here', 200),
        ]);

        $response = $service->getRequest();

        $this->assertEquals(['result' => 'some utterly ugly string right here'], $response);
    }

    /** @test */
    public function itReturnsArrayWithResponseBodyWhenResponseIsEmpty()
    {
        $service = new TestService(new TestConfiguration());

        \Http::fake([
            '*' => \Http::response(null, 200),
        ]);

        $response = $service->getRequest();

        $this->assertEquals(['result' => null], $response);
    }

    /** @test */
    public function itThrowsExceptionWhenRequestFails()
    {
        $service = new TestService(new TestConfiguration());

        $this->expectException(RequestException::class);

        \Http::fake([
            '*' => \Http::response(null, 500),
        ]);

        $service->getRequest();
    }
}

class TestService extends HttpService
{
    public function getRequest()
    {
        return $this->get($this->configuration->url(), ['foo' => 'bar']);
    }
    public function postRequest()
    {
        return $this->post($this->configuration->url(), ['foo' => 'bar']);
    }
    public function putRequest()
    {
        return $this->put($this->configuration->url(), ['foo' => 'bar']);
    }
    public function deleteRequest()
    {
        return $this->delete($this->configuration->url());
    }

    public function requestWithTokenTypeChange()
    {
        $this->configuration->tokenType = 'Yolo';

        return $this->post($this->configuration->url(), ['foo' => 'bar']);
    }
}

class TestConfiguration implements ProvidesConfiguration
{
    public function settled(): bool
    {
        return true;
    }

    public function baseUrl(string $path = null): string
    {
        return 'https://example.com/';
    }

    public function url(string $path = null): string
    {
        return 'https://example.com/path';
    }

    public function token(): ?string
    {
        return 'token';
    }
}

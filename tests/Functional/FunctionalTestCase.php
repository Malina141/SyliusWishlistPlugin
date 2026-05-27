<?php

declare(strict_types=1);

namespace Tests\Malina141\SyliusWishlistPlugin\Functional;

use ApiTestCase\JsonApiTestCase;
use Symfony\Component\HttpFoundation\Response;

abstract class FunctionalTestCase extends JsonApiTestCase
{
    public const CONTENT_TYPE_HEADER = [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_ACCEPT' => 'application/ld+json',
    ];

    public const UPDATE_CONTENT_TYPE_HEADER = [
        'CONTENT_TYPE' => 'application/merge-patch+json',
        'HTTP_ACCEPT'   => 'application/ld+json',
    ];

    public function __construct(
        ?string $name = null,
        array $data = [],
        string $dataName = '',
    ) {
        parent::__construct($name, $data, $dataName);

        $this->dataFixturesPath = __DIR__ . \DIRECTORY_SEPARATOR . 'DataFixtures' . \DIRECTORY_SEPARATOR . 'ORM';
        $this->expectedResponsesPath = __DIR__ . \DIRECTORY_SEPARATOR . 'Responses' . \DIRECTORY_SEPARATOR . 'Expected';
    }

    protected function requestJson(string $method, string $uri, array $content = []): Response
    {
        $this->client->request($method, $uri, [], [], $method === 'PATCH' ? self::UPDATE_CONTENT_TYPE_HEADER : self::CONTENT_TYPE_HEADER, \json_encode($content));

        return $this->client->getResponse();
    }
}

<?php

use Psr\Http\Message\ServerRequestInterface;

require_once __DIR__ . '/benchmark.php';
require_once __DIR__ . '/../vendor/autoload.php';

$generators = [
    'guzzle' => function (): ServerRequestInterface {
        return new \GuzzleHttp\Psr7\ServerRequest('GET', '');
    },
    'hyholm' => function(): ServerRequestInterface {
        return new \Nyholm\Psr7\ServerRequest('GET', '');
    },
    'ring-central' => function (): ServerRequestInterface {
        return new \RingCentral\Psr7\ServerRequest('GET', '');
    },
    'slim' => function (): ServerRequestInterface {
        return new \Slim\Http\Request('GET', \Slim\Http\Uri::createFromString(''), new \Slim\Http\Headers, [], [], \RingCentral\Psr7\stream_for());
    },
    'wind-walker' => function (): ServerRequestInterface {
        return new Windwalker\Http\Request\ServerRequest();
    },
    'zend-diactoros' => function (): ServerRequestInterface {
        return new \Zend\Diactoros\ServerRequest();
    },
    'hamlet-framework' => function (): ServerRequestInterface {
        return \Hamlet\Http\Message\ServerRequest::empty();
    },
];

$b = new Benchmark;
$b->setIterations(100000);
foreach ($generators as $name => $generator) {
    $b->report($name, function () use ($generator) {
        /** @var ServerRequestInterface $instance */
        $instance = $generator()->withAttribute('a', '1')
            ->withHeader('hOst', 'mail.ru')
            ->withAddedHeader('Language', 'ru')
            ->withAttribute('name', time());
        $instance->getUri();
        $instance->getQueryParams();
        $instance->getHeaderLine('Host');
    });
}
$b->bench();

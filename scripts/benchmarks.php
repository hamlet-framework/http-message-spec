<?php

use PHPBenchmark\testing\FunctionComparison;
use Psr\Http\Message\ServerRequestInterface;

require_once __DIR__ . '/../vendor/autoload.php';

$generators = [
    'guzzle' => function (): ServerRequestInterface {
        return new \GuzzleHttp\Psr7\ServerRequest('GET', '');
    },
    'nyholm' => function(): ServerRequestInterface {
        return new \Nyholm\Psr7\ServerRequest('GET', '');
    },
    'ring-central' => function (): ServerRequestInterface {
        return new \RingCentral\Psr7\ServerRequest('GET', '');
    },
    'lamina-diactoros' => function (): ServerRequestInterface {
        return new \Laminas\Diactoros\ServerRequest();
    },
    'hamlet-framework' => function (): ServerRequestInterface {
        return \Hamlet\Http\Message\ServerRequest::empty();
    },
    'http-soft' => function (): ServerRequestInterface {
        return new \HttpSoft\Message\ServerRequest();
    }
];

echo 'Comprehensive test' . PHP_EOL;
$comparison = FunctionComparison::load(100000);
foreach ($generators as $title => $generator) {
    $comparison->addFunction($title, function () use ($generator) {
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
$comparison->exec();

echo 'Fetching test' . PHP_EOL;
$comparison = FunctionComparison::load(100000);
foreach ($generators as $title => $generator) {
    $comparison->addFunction($title, function () use ($generator) {
        /** @var ServerRequestInterface $instance */
        $instance = $generator();
        $instance->getMethod();
        $instance->getQueryParams();
        $instance->getUri()->getPath();
        $instance->getUri()->getPath();
        $instance->getUri()->getPath();
        $instance->getUri()->getPath();
    });
}
$comparison->exec();

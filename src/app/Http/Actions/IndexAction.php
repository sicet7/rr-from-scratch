<?php

namespace App\Http\Actions;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Sicet7\Cookie\CookieException;
use Sicet7\Cookie\CookieJar;
use Sicet7\PropertyInjection\Attributes\Inject;
use Sicet7\Slim\Attributes\Routing\Get;
use Symfony\Component\Finder\Finder;

#[Get('/')]
class IndexAction
{
    /**
     * @var ResponseFactoryInterface
     */
    #[Inject]
    private ResponseFactoryInterface $responseFactory;

    /**
     * @var StreamFactoryInterface
     */
    #[Inject]
    private StreamFactoryInterface $streamFactory;

    /**
     * @param CookieJar $cookieJar
     * @return ResponseInterface
     * @throws CookieException
     */
    public function __invoke(CookieJar $cookieJar): ResponseInterface {
        $response = $this->responseFactory->createResponse()->withBody($this->streamFactory->createStreamFromFile(
            APP_ROOT . '/public/index.html'
        ));
        foreach (Finder::create()->files()->in([APP_ROOT . '/public/assets/'])->name('index.*') as $file) {
            $response = $response->withAddedHeader('http2-push', substr($file->getPathname(), strlen(APP_ROOT . '/public')));
        }
        if (empty(($cookie = $cookieJar->find('test')))) {
            $cookie = $cookieJar->create('test', 'hello');
        }
        return $response;
    }
}
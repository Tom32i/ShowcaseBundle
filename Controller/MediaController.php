<?php

declare(strict_types=1);

namespace Tom32i\ShowcaseBundle\Controller;

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use Tom32i\ShowcaseBundle\Service\Processor;

class MediaController
{
    public function __construct(
        private Processor $processor,
    ) {
    }

    #[Route('/image/{preset}/{path}', name: 'image', requirements: ['path' => '.+'])]
    public function image(string $path, string $preset): StreamedResponse
    {
        return $this->processor->serveImage($path, $preset);
    }

    #[Route('/download/{path}', name: 'file', requirements: ['path' => '.+'])]
    public function file(Request $request, string $path): BinaryFileResponse
    {
        $response = $this->processor->serveFile($path);

        $response->isNotModified($request);

        return $response;
    }
}

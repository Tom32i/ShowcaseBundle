<?php

namespace Tom32i\ShowcaseBundle\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Tom32i\ShowcaseBundle\Service\Processor;

class MediaController
{
    public function __construct(Processor $processor)
    {
        $this->processor = $processor;
    }

    /**
     * @Route("/image/{preset}/{path}", name="image", requirements={"path"=".+"})
     */
    public function image(string $path, string $preset)
    {
        return $this->processor->serveImage($path, $preset);
    }

    /**
     * @Route("/download/{path}", name="file", requirements={"path"=".+"})
     */
    public function file(string $path)
    {
        return $this->processor->serveFile($path);
    }
}

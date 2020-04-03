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
     * @Route("/{preset}/{path}", name="image", requirements={"path"=".+"})
     */
    public function image(string $path, string $preset)
    {
        return $this->processor->serveImage($path, $preset);
    }

    /**
     * @Route("/{path}", name="file")
     */
    public function file(string $path)
    {
        return $this->processor->serveFile($path);
    }
}

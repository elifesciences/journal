<?php

namespace eLife\Journal\Controller;

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

final class AudioFileController
{
    public function audioFileAction() : Response
    {
        return new BinaryFileResponse(__DIR__.'/../../app/Resources/tests/blank.mp3');
    }
}

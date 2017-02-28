<?php

namespace eLife\Journal\Controller;

use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use UnexpectedValueException;

final class DownloadController extends Controller
{
    public function fileAction(Request $request) : Response
    {
        try {
            $link = $this->get('elife.journal.helper.download_link_uri_generator')->check($request->getUri());
        } catch (UnexpectedValueException $e) {
            throw new NotFoundHttpException('Not a valid signed URI', $e);
        }

        /** @var ResponseInterface $fileResponse */
        $fileResponse = $this->get('csa_guzzle.client.file_download')->request('GET', $link->getUri());

        $stream = $fileResponse->getBody();

        switch ($fileResponse->getStatusCode()) {
            case Response::HTTP_OK:
                $response = new StreamedResponse(
                    function () use ($stream) {
                        if (ob_get_length()) {
                            ob_end_clean();
                        }
                        while (!$stream->eof()) {
                            echo $stream->read(1024);
                            flush();
                        }
                        $stream->close();
                    },
                    $fileResponse->getStatusCode(),
                    array_filter([
                        'Cache-Control' => $fileResponse->getHeaderLine('Cache-Control'),
                        'Content-Length' => $fileResponse->getHeaderLine('Content-Length'),
                        'Content-Type' => $fileResponse->getHeaderLine('Content-Type'),
                        'ETag' => $fileResponse->getHeaderLine('ETag'),
                        'Last-Modified' => $fileResponse->getHeaderLine('Last-Modified'),
                    ])
                );

                if ($link->getFilename()) {
                    $response->headers->set('Content-Disposition', $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $link->getFilename()));
                } else {
                    $response->headers->set('Content-Disposition', ResponseHeaderBag::DISPOSITION_ATTACHMENT);
                }

                break;
            case Response::HTTP_NOT_FOUND:
            case Response::HTTP_GONE:
                throw new HttpException($fileResponse->getStatusCode(), $fileResponse->getReasonPhrase());
            default:
                throw new RuntimeException("Failed: {$fileResponse->getStatusCode()}, {$fileResponse->getReasonPhrase()}");
        }

        return $response;
    }
}

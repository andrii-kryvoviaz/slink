<?php

declare(strict_types=1);

namespace Slink\Image\Infrastructure\Service;

use Slink\Image\Domain\Service\ImageProcessorInterface;
use Slink\Image\Domain\Service\ImageProcessingOptions;
use Slink\Image\Domain\Service\ImageProcessingResult;
use Slink\Image\Grpc\ImageServiceClient;
use Slink\Image\Grpc\ImageRequest;
use Slink\Image\Grpc\ImageOperation;
use Slink\Image\Grpc\ResizeOptions;
use Slink\Image\Grpc\CropOptions;
use Slink\Image\Grpc\ResizeMode;
use Slink\Image\Grpc\CropPosition;

final readonly class GrpcImageProcessor implements ImageProcessorInterface
{
    public function __construct(
        private ImageServiceClient $grpcClient
    ) {
    }

    public function process(string $imageData, ImageProcessingOptions $options): ImageProcessingResult
    {
        if (!$options->hasResizing()) {
            return new ImageProcessingResult($imageData, false);
        }

        $request = $this->buildImageRequest($imageData, $options);

        try {
            $response = $this->grpcClient->ProcessImage($request)->wait();
            [$reply, $status] = $response;

            if ($status->code !== \Grpc\STATUS_OK) {
                return new ImageProcessingResult(
                    $imageData,
                    false,
                    "gRPC call failed with status: {$status->code}"
                );
            }

            if ($reply->hasSuccess()) {
                return new ImageProcessingResult(
                    $reply->getSuccess()->getImageData(),
                    true
                );
            }

            if ($reply->hasError()) {
                return new ImageProcessingResult(
                    $imageData,
                    false,
                    $reply->getError()->getMessage()
                );
            }

            return new ImageProcessingResult($imageData, false, 'Unknown gRPC response format');

        } catch (\Exception $e) {
            return new ImageProcessingResult(
                $imageData,
                false,
                "gRPC processing failed: {$e->getMessage()}"
            );
        }
    }

    private function buildImageRequest(string $imageData, ImageProcessingOptions $options): ImageRequest
    {
        $request = new ImageRequest();
        $request->setImageData($imageData);
        $request->setInputType($this->detectMimeType($imageData));
        
        $outputType = $options->outputFormat === 'auto' 
            ? $this->detectMimeType($imageData) 
            : $options->outputFormat;
        $request->setOutputType($outputType);

        if ($options->quality !== null) {
            $request->setQuality($options->quality);
        }

        $request->setStripMetadata($options->stripMetadata);

        $request->setPreserveAnimation($options->preserveAnimation);

        if ($options->hasResizing()) {
            $operation = new ImageOperation();
            
            if ($options->crop) {
                $cropOptions = new CropOptions();
                if ($options->width !== null) {
                    $cropOptions->setWidth($options->width);
                }
                if ($options->height !== null) {
                    $cropOptions->setHeight($options->height);
                }
                $cropOptions->setPosition(CropPosition::CENTER);
                $operation->setCrop($cropOptions);
            } else {
                $resizeOptions = new ResizeOptions();
                if ($options->width !== null) {
                    $resizeOptions->setWidth($options->width);
                }
                if ($options->height !== null) {
                    $resizeOptions->setHeight($options->height);
                }
                $resizeOptions->setMode(ResizeMode::FIT);
                $operation->setResize($resizeOptions);
            }

            $request->setOperation($operation);
        }

        return $request;
    }

    private function detectMimeType(string $content): string
    {
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->buffer($content);
        
        return match($mimeType) {
            'image/jpeg' => 'jpeg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
            'image/tiff' => 'tiff',
            'image/bmp' => 'bmp',
            'image/avif' => 'avif',
            default => 'jpeg'
        };
    }

    private function detectMimeTypeFromContent(string $content): string
    {
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        return $finfo->buffer($content) ?: 'application/octet-stream';
    }
}

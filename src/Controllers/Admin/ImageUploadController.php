<?php

namespace Plasticode\Controllers\Admin;

use Plasticode\Core\Response;
use Plasticode\Controllers\Controller;
use Plasticode\Exceptions\Http\BadRequestException;
use Plasticode\IO\Image;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Request as SlimRequest;

abstract class ImageUploadController extends Controller
{
    public function upload(SlimRequest $request, ResponseInterface $response) : ResponseInterface
    {
        $context = $request->getParam('context', null);
        $files = $request->getUploadedFiles()['files'] ?? null;

        if (empty($files)) {
            throw new BadRequestException('No files provided.');
        }

        foreach ($files as $file) {
            $error = $file->getError();
            
            if ($error !== UPLOAD_ERR_OK) {
                $fileName = $file->getClientFilename();

                $this->logger->error(
                    'File upload error: ' . $fileName . ', ' . $error . '.'
                );
                
                throw new BadRequestException('Upload error (see log for details).');
            }
        }
        
        foreach ($files as $file) {
            $fileName = $file->getClientFilename();
            $this->logger->info('Uploaded file: ' . $fileName . '.');
            
            $this->extractAndProcessImages(
                $file->file,
                function (Image $image, string $imageFileName) use ($context) {
                    $this->addImage($context, $image, $imageFileName);
                }
            );
        }
        
        return Response::json(
            $response,
            ['message' => $this->translate('Upload successful.')]
        );
    }

    /**
     * Extracts and processes images from ZIP-archive.
     * 
     * @param string $zipName
     * @param \Closure $process
     * @return Image[]
     */
    protected function extractAndProcessImages(string $zipName, \Closure $process) : array
    {
        $images = [];
        
        $zip = new \ZipArchive;
        $result = $zip->open($zipName);
        
        if ($result === true) {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $fileName = $zip->getNameIndex($i);
                
                if (Image::isImagePath($fileName)) {
                    $fileNames[] = $fileName;
                }
            }
            
            if (!empty($fileNames)) {
                sort($fileNames);

                foreach ($fileNames as $fileName) {
                    $data = $zip->getFromName($fileName);
                    $imgType = Image::getImageTypeFromPath($fileName);

                    $image = new Image($data, $imgType);
                    $images[] = $image;

                    $process($image, $fileName);
                }
            }

            $zip->close();
        }
        
        return $images;
    }
    
    /**
     * Adds image.
     */
    protected abstract function addImage(array $context, Image $image, string $fileName) : void;
}

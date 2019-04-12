<?php

namespace Plasticode\Controllers\Admin;

use Plasticode\Core\Core;
use Plasticode\Controllers\Controller;
use Plasticode\Exceptions\BadRequestException;
use Plasticode\IO\Image;

abstract class ImageUploadController extends Controller
{
	public function upload($request, $response, $args)
	{
	    $context = $request->getParam('context', null);
	    
	    /*if (empty($context)) {
	        throw new BadRequestException('You must provide a context.');
	    }*/

	    $files = $request->getUploadedFiles()['files'] ?? null;

        if (empty($files)) {
	        throw new BadRequestException('No files provided.');
        }

	    foreach ($files as $file) {
	        $error = $file->getError();
	        
            if ($error !== UPLOAD_ERR_OK) {
                $fileName = $file->getClientFilename();
                $this->logger->error("File upload error: {$fileName}, {$error}.");
                
    	        throw new BadRequestException('Upload error (see log for details).');
            }
        }
        
        foreach ($files as $file) {
            $fileName = $file->getClientFilename();
            $this->logger->info("Uploaded file: {$fileName}.");
            
            $images = $this->extractAndProcessImages($file->file, function (Image $image, $imageFileName) use ($context) {
                $this->addImage($context, $image, $imageFileName);
            });
	    }
	    
		return Core::json($response, [
		    'message' => $this->translate('Upload successful.'),
		]);
	}

    /**
     * Extracts and processes images from ZIP-archive.
     */
	protected function extractAndProcessImages($zipName, $process)
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
	protected abstract function addImage($context, Image $image, $fileName);
}

<?php

namespace App\Services\Files;

use \FilesystemIterator;

class VariousFileTools
{
    public static function getAvailableFiles(string $path) : array
    {
        $availableFiles=array();
        $iterator = new FilesystemIterator($path);
        foreach ($iterator as $fileInfo) {
            $availableFiles[] = $fileInfo->getFilename();
        }

        return $availableFiles;
    }
}
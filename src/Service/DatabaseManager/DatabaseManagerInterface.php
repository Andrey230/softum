<?php


namespace App\Service\DatabaseManager;


use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\UploadedFile;

interface DatabaseManagerInterface
{
    public function create(UploadedFile $file, string $fileName):void;

    public function remove(string $name):void;

    public function getList():Finder;
}
<?php


namespace App\Service\DatabaseManager;


use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class DatabaseManager implements DatabaseManagerInterface
{
    /**
     * @var string
     */
    private $databasesDir;

    public function __construct(string $databasesDir)
    {
        $this->databasesDir = $databasesDir;
    }

    public function create(UploadedFile $file, string $fileName):void
    {
        $filePath = $this->databasesDir.$fileName;

        if(file_exists($filePath)){
            return;
        }

        try {
            $file->move(
                $this->databasesDir,
                $fileName
            );
        } catch (FileException $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function remove(string $name):void
    {
        $filePath = $this->databasesDir.$name;

        if(file_exists($filePath)){
            unlink($filePath);
        }
    }

    public function getList():Finder
    {
        $finder = new Finder();

        return $finder->in($this->databasesDir)->files()->name('*.sql');
    }

}
<?php

namespace ProScholy\LilypondRenderer;

use Exception;
use ZipStream\ZipStream;
use ZipStream\Option\Archive;

class LilypondSrc
{
    protected array $fragmentSections = [
        'header' => [],
        'pre-src' => [],
        'src' => [],
        'post-src' => [],
        'footer_vars' => [],
        'footer' => [],
    ];

    protected array $includeFiles;
    protected array $includeFilesString;

    public function __construct($src, array $include_files = [])
    {
        $this->fragmentSections['src'] = [$src];
        $this->includeFiles = [];
        $this->includeFilesString = [];
        
        foreach ($include_files as $fname) {
            $this->withIncludeFile($fname);
        }
    }

    public function withFragmentStub(string $fname, string $section, array $arr_replace = []) : LilypondSrc
    {
        $str = file_get_contents(__DIR__ . "/lilypond_stubs/$fname.txt");

        foreach ($arr_replace as $repl => $with) {
            $withStr = $with;
            if (is_bool($with)) {
                $withStr = $with ? '##t' : '##f'; // convert boolean to Lilypond/Scheme boolean
            }

            if (!str_contains($str, $repl)) {
                throw new Exception('Variable $repl not found in stub');
            }

            $str = str_replace($repl, $withStr, $str);
        }

        $this->fragmentSections[$section][] = $str;
        return $this;
    }

    public function withIncludeFile(string $file_include_path) : LilypondSrc
    {
        if (file_exists(self::getIncludedFilePath($file_include_path))) {
            $this->includeFiles[] = $file_include_path;
        } else {
            throw new Exception("Cannot find file $file_include_path to be included in the src.");
        }
        return $this;
    }

    public function withIncludeDirectory(string $dir_include_path) : LilypondSrc
    {
        $files = glob(self::getIncludedFilePath($dir_include_path) . '/*');

        if (!$files) {
            throw new Exception("No directory $dir_include_path or the directory is empty");
        }

        foreach ($files as $file) {
            $rel_fpath = str_replace(self::getIncludedFilePath(''), '', $file);
            $this->includeFiles[] = $rel_fpath;
        }

        return $this;
    }

    public function withIncludeFileString(string $fname, string $src) : LilypondSrc
    {
        // todo: reject if exists
        $this->includeFilesString[$fname] = $src;
        return $this;
    }

    public function hasIncludes() : bool
    {
        return (count($this->includeFiles) + count($this->includeFilesString)) > 0;
    }

    public function getIncludeFiles() : array
    {
        return $this->includeFiles;
    }

    public function getIncludeFilesString() : array
    {
        return $this->includeFilesString;
    }

    public function __toString()
    {
        $finalStr = "";
        foreach (array_values($this->fragmentSections) as $section) {
            foreach ($section as $fragment) {
                $finalStr .= (string)$fragment . "\n";
            }
        }
        return $finalStr;
    }

    public function getZippedSrcStream()
    {
        $tempStream = fopen('php://temp', 'rw');

        // setup the zipping things
        $zipStreamOptions = new Archive();
        $zipStreamOptions->setOutputStream($tempStream);
        $zipStream = new ZipStream('score.zip', $zipStreamOptions); 

        // include the main src and the other files
        $zipStream->addFile("score.ly", (string)$this);
        foreach ($this->getIncludeFilesString() as $filename => $src) {
            $zipStream->addFile($filename, $src);
        }

        foreach ($this->getIncludeFiles() as $filename) {
            $zipStream->addFileFromPath($filename,  self::getIncludedFilePath($filename));
        }

        // get the zip file contents
        $zipStream->finish();
        rewind($tempStream);

        return $tempStream;
    }

    public static function getIncludedFilePath(string $fpath) : string
    {
        return __DIR__ . '/ly_includes/' . $fpath;
    }
}

<?php

namespace ProScholy\LilypondRenderer;

use Exception;
use Stringable;
use ZipStream\ZipStream;
use ZipStream\Option\Archive;

/**
 * A base class for representing a LilyPond source. It supplies a templating engine.
 */
class LilypondSrc implements Stringable
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

    /**
     * Construct a new LilyPond source instance. 
     *
     * @param string|Stringable $src
     * @param string[] $include_files
     */
    public function __construct($src, array $include_files = [])
    {
        $this->fragmentSections['src'] = [$src];
        $this->includeFiles = [];
        $this->includeFilesString = [];
        
        foreach ($include_files as $fname) {
            $this->withIncludeFile($fname);
        }
    }

    /**
     * Include a LilyPond stub named $fname in on of available sections.
     *
     * @param string $fname
     * @param string $section
     * @param array $arr_replace
     * @return LilypondSrc
     */
    protected function withFragmentStub(string $fname, string $section, array $arr_replace = []) : LilypondSrc
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

    /**
     * Include a file in the resulting source code, based on its relative path
     *
     * @param string $file_include_path
     * @return LilypondSrc
     */
    protected function withIncludeFile(string $file_include_path) : LilypondSrc
    {
        if (file_exists(self::getIncludedFilePath($file_include_path))) {
            $this->includeFiles[] = $file_include_path;
        } else {
            throw new Exception("Cannot find file $file_include_path to be included in the src.");
        }
        return $this;
    }

    /**
     * Include a whole directory in the resulting source code, based on its relative path
     *
     * @param string $dir_include_path
     * @return LilypondSrc
     */
    protected function withIncludeDirectory(string $dir_include_path) : LilypondSrc
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

    /**
     * Create a file with name $fname and content $src in the resulting source code.
     *
     * @param string $fname
     * @param string $src
     * @return LilypondSrc
     */
    protected function withIncludeFileString(string $fname, string $src) : LilypondSrc
    {
        $this->includeFilesString[$fname] = $src;
        return $this;
    }

    /**
     * Determines, wheter this source code includes any files
     *
     * @return boolean
     */
    public function hasIncludes() : bool
    {
        return (count($this->includeFiles) + count($this->includeFilesString)) > 0;
    }

    /**
     * Get files included by their filepaths
     *
     * @return array
     */
    public function getIncludeFiles() : array
    {
        return $this->includeFiles;
    }

    /**
     * Get files included by their string content
     *
     * @return array
     */
    public function getIncludeFilesString() : array
    {
        return $this->includeFilesString;
    }

    final public function __toString()
    {
        $finalStr = "";
        foreach (array_values($this->fragmentSections) as $section) {
            foreach ($section as $fragment) {
                $finalStr .= (string)$fragment . "\n";
            }
        }
        return $finalStr;
    }

    /**
     * Create a ZIP file (in-memory) and return its stream handle
     *
     * @return resource|false
     */
    final public function getZippedSrcStream()
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

    /**
     * Get the full path of an included file
     *
     * @param string $fpath
     * @return string
     */
    private static function getIncludedFilePath(string $fpath) : string
    {
        return __DIR__ . '/ly_includes/' . $fpath;
    }
}

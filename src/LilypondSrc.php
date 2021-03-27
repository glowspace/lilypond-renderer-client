<?php

namespace ProScholy\LilypondRenderer;

use Exception;

class LilypondSrc
{
    protected array $fragmentSections = [
        'header' => [],
        'src' => [],
        'footer_vars' => [],
        'footer' => [],
    ];

    protected array $includes;

    public function __construct($src, array $includes = [])
    {
        $this->fragmentSections['src'] = [$src];
        $this->includes = [];
        
        foreach ($includes as $fname) {
            $this->withIncludeFile($fname);
        }
    }

    protected function withFragmentStub(string $fname, string $section, array $arr_replace = []) : LilypondSrc
    {
        $str = file_get_contents(__DIR__ . "/lilypond_stubs/$fname.txt");

        foreach ($arr_replace as $repl => $with) {
            $str = str_replace($repl, $with, $str);
        }

        $this->fragmentSections[$section][] = $str;
        return $this;
    }

    public function withIncludeFile(string $file_include_path) : LilypondSrc
    {
        if (file_exists(self::getIncludedFilePath($file_include_path))) {
            $this->includes[] = $file_include_path;
        } else {
            throw new Exception("Cannot find file $file_include_path to be included in the src.");
        }
        return $this;
    }

    public function hasIncludeFiles() : bool
    {
        return count($this->includes) > 0;
    }

    public function getIncludeFiles() : array
    {
        return $this->includes;
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

    public static function getIncludedFilePath(string $fpath) : string
    {
        return __DIR__ . '/ly_includes/' . $fpath;
    }
}

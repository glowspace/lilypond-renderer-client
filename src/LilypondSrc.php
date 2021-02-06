<?php

namespace ProScholy\LilypondRenderer;

class LilypondSrc
{
    protected $src;
    const NO_LAYOUT = 'no_layout';

    private function __construct($src)
    {
        $this->src = $src;
    }

    private static function getFragment($fname) {
        return file_get_contents(__DIR__ . "/lilypond/$fname.txt");
    }

    public static function fromRaw($src)
    {
        return new self($src);
    }

    public static function withLayout($lilypond, bool $infinite_paper, $layout = 'default_layout')
    {
        $src = $lilypond;
        if ($layout != self::NO_LAYOUT) {
            $src .= self::getFragment($layout);
        }
        if ($infinite_paper) {
            $src .= self::getFragment('infinite_paper');
        }

        return new self($src);
    }

    public function getSrc()
    {
        return $this->src;
    }

    public function __toString()
    {
        return $this->src;
    }
}

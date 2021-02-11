<?php

namespace ProScholy\LilypondRenderer;

class LilypondSrc
{
    protected string $src;
    protected string $srcConfig;
    protected string $usrConfig;
    protected string $layout;
    protected string $paper;

    protected array $fragments = [
        // user input
        'src' => '',
        'noedit' => '', // noedit comment for Frescobaldi users
        'originalKey' => '',
        // view config
        'targetKey' => '',
        'disableParts' => '',
        // default config
        'layout' => '',
        'paper' => ''
    ];

    public function __construct($src)
    {
        $this->fragments['src'] = $src;
    }

    public function applyLayout($layout = 'default_layout', $font = 'amiri', $fontSize = 2.5)
    {
        $this->fragments['noedit'] = self::loadFragment('no_edit');

        $this->fragments['layout'] = self::loadFragment($layout, [
            'VAR_FONT_NAME' => $font,
            'VAR_FONT_SIZE' => $fontSize
        ]);

        if ($this->fragments['originalKey'] == '') {
            $this->setOriginalKey('c');
        }
        return $this;
    }

    public function applyInfinitePaper($width_mm = 120)
    {
        $this->fragments['paper'] = self::loadFragment('infinite_paper', ['VAR_WIDTH_MM' => $width_mm]);
        return $this;
    }

    public function setOriginalKey(string $key_major)
    {
        $this->fragments['originalKey'] = self::loadFragment('key', ['VAR_KEY_MAJOR' => $key_major]);
        return $this;
    }

    public function setTargetKey(string $key_major)
    {
        $this->fragments['targetKey'] = "targetKey = $key_major";
        return $this;
    }

    public function disableParts(array $part_names)
    {
        foreach ($part_names as $part_name) {
            if (in_array($part_name, ['melodie', 'alt', 'akordy', 'text', 'textAlt'])) {
                $this->fragments['disableParts'] .= "$part_name = ##f\n";
            }
        }

        return $this;
    }

    private static function loadFragment($fname, array $arr_replace = []) {
        $str = file_get_contents(__DIR__ . "/lilypond/$fname.txt");

        foreach ($arr_replace as $repl => $with) {
            $str = str_replace($repl, $with, $str);
        }

        return $str;
    }

    public function getSrc()
    {
        return $this->fragments['src'];
    }

    public function __toString()
    {
        return implode("\n", array_values($this->fragments));
    }
}

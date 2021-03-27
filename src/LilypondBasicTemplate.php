<?php

namespace ProScholy\LilypondRenderer;

class LilypondBasicTemplate extends LilypondSrc
{
    protected bool $hasOriginalKey = false;

    public function applyDefaultLayout($font = 'amiri', $fontSize = 2.5, $chordFont = 'roboto', $chordFontSize = 1.5) : LilypondBasicTemplate
    {
        return $this->setOriginalKey('c')
            ->withFragmentStub('basic/no_edit', 'header')
            ->withFragmentStub('basic/default_layout', 'footer', [
                'VAR_FONT_NAME' => $font,
                'VAR_FONT_SIZE' => $fontSize,
                'VAR_CHORD_FONT_NAME' => $chordFont,
                'VAR_CHORD_FONT_SIZE' => $chordFontSize
            ]);
    }

    public function applyInfinitePaper($width_mm = 120) : LilypondBasicTemplate
    {
        return $this->withFragmentStub('basic/infinite_paper', 'footer', ['VAR_WIDTH_MM' => $width_mm]);
    }

    public function applyTinynotes() : LilypondBasicTemplate
    {
        return $this->withFragmentStub('basic/tinynotes', 'header');
    }

    public function setOriginalKey(string $key_major) : LilypondBasicTemplate
    {
        if (!$this->hasOriginalKey) {
            $this->withFragmentStub('basic/key', 'footer_vars', ['VAR_KEY_MAJOR' => $key_major]);
        }
        $this->hasOriginalKey = true;
        return $this;
    }
}

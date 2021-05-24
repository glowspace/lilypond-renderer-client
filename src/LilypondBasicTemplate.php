<?php

namespace ProScholy\LilypondRenderer;

/**
 * This class represents the first version of a LilyPond template, which is no more actively supported. 
 */
class LilypondBasicTemplate extends LilypondSrc
{
    protected bool $hasOriginalKey = false;

    /**
     * Apply the default layout configuration (see src/lilypond_stubs/basic/default_layout.txt)
     *
     * @param string $font
     * @param float $fontSize
     * @param string $chordFont
     * @param float $chordFontSize
     * @return LilypondBasicTemplate
     */
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

    /**
     * Apply setting of an infinite paper (see src/lilypond_stubs/basic/infinite_paper.txt)
     *
     * @param integer $width_mm
     * @return LilypondBasicTemplate
     */
    public function applyInfinitePaper($width_mm = 120) : LilypondBasicTemplate
    {
        return $this->withFragmentStub('basic/infinite_paper', 'footer', ['VAR_WIDTH_MM' => $width_mm]);
    }

    /**
     * Change the appearance of \tiny notes (see src/lilypond_stubs/basic/tinynotes.txt)
     *
     * @return LilypondBasicTemplate
     */
    public function applyTinynotes() : LilypondBasicTemplate
    {
        return $this->withFragmentStub('basic/tinynotes', 'header');
    }

    /**
     * Set the original key signature
     *
     * @param string $key_major
     * @return LilypondBasicTemplate
     */
    public function setOriginalKey(string $key_major) : LilypondBasicTemplate
    {
        if (!$this->hasOriginalKey) {
            $this->withFragmentStub('basic/key', 'footer_vars', ['VAR_KEY_MAJOR' => $key_major]);
        }
        $this->hasOriginalKey = true;
        return $this;
    }
}

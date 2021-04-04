<?php

namespace ProScholy\LilypondRenderer;

class LilypondPartsGlobalConfig
{
    protected $two_voices_per_staff;
    protected $hide_chords;
    protected $global_transpose_relative_c;
    protected $merge_rests;
    protected $hide_bar_numbers;
    protected $force_part_breaks;

    protected $font = 'amiri';
    protected $fontSize = 2.5;
    protected $chordFont = 'amiri';
    protected $chordFontSize = 1.5;
    protected $version;

    protected $paperType = 'CUSTOM';
    protected $paperWidthMm = 120;
    protected $indent = 0;
    protected $topMargin = 1;

    public function __construct(string $version = '2.22.0',
                                bool $two_voices_per_staff = true, 
                                bool $hide_chords = false,
                                $global_transpose_relative_c = false,
                                bool $merge_rests = true,
                                bool $hide_bar_numbers = true,
                                bool $force_part_breaks = false)
    {
        $this->two_voices_per_staff = $two_voices_per_staff;
        $this->hide_chords = $hide_chords;
        $this->global_transpose_relative_c = $global_transpose_relative_c;
        $this->merge_rests = $merge_rests;
        $this->hide_bar_numbers = $hide_bar_numbers;
        $this->force_part_breaks = $force_part_breaks;
        $this->version = $version;
    }

    public function setFont($font, $fontSize)
    {
        $this->font = $font;
        $this->fontSize = $fontSize;
    }

    public function setChordFont($font, $fontSize)
    {
        $this->chordFont = $font;
        $this->chordFontSize = $fontSize;
    }

    public function setCustomPaper($paper_width = 120)
    {
        $this->paperType = 'CUSTOM';
        $this->paperWidthMm = $paper_width;
    }

    public function setPaper($paper, $indent, $top_margin)
    {
        $this->paperType = $paper;
        $this->indent = $indent;
        $this->topMargin = $top_margin;
    }


    // ------------- used by LilypondPartsTemplate --------------------

    public function getForcePartBreaks()
    {
        return $this->force_part_breaks;
    }

    public function getLilypondVersion()
    {
        return $this->version;
    }

    // todo add global layout type (all, only_solo, etc..)
    public function getPartIncludeStub()
    {
        return 'parts/total_part_include';
    }

    public function setUpGlobalSrc(LilypondSrc $global_src)
    {
        $global_src->withFragmentStub('parts/global_config', 'header', [
            'VAR_TWO_VOICES_PER_STAFF' => $this->two_voices_per_staff,
            'VAR_HIDE_CHORDS' => $this->hide_chords,
            'VAR_GLOBAL_TRANSPOSE_RELATIVE_C' => $this->global_transpose_relative_c,
        ]);

        if ($this->merge_rests) {
            $global_src->withFragmentStub('parts/merge_rests', 'header');
        }

        if ($this->hide_bar_numbers) {
            $global_src->withFragmentStub('parts/hide_bar_numbers', 'header');
        }

        $global_src->withFragmentStub('parts/font', 'header', [
            'VAR_FONT_NAME' => $this->font,
            'VAR_FONT_SIZE' => $this->fontSize,
            'VAR_CHORD_FONT_NAME' => $this->chordFont,
            'VAR_CHORD_FONT_SIZE' => $this->chordFontSize
        ]);

        // paper
        if ($this->paperType == 'CUSTOM') {
            $global_src->withFragmentStub('parts/custom_paper', 'header', [
                'VAR_WIDTH_MM' => $this->paperWidthMm
            ]);
        } else {
            $global_src->withFragmentStub('parts/paper', 'header', [
                'VAR_PAPER_SIZE' => $this->paper,
                'VAR_INDENT' => $this->indent,
                'VAR_TOP_MARGIN' => $this->topMargin
            ]);
        }
    }
}

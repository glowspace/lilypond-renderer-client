<?php

namespace ProScholy\LilypondRenderer;

class LilypondPartsTemplate extends LilypondSrc
{
    protected LilypondPartsRenderConfig $config;

    public function __construct(string $global_src = '', ?LilypondPartsRenderConfig $config = null)
    {
        parent::__construct('');

        $this->config = $config ?? new LilypondPartsRenderConfig();

        $this->withFragmentStub('parts/total_header', 'header', [
            'VAR_LILYPOND_VERSION' => $this->config->getAttribute('version')
        ])->withFragmentStub('parts/total_footer', 'footer');

        $globalPartSrc = new LilypondSrc($global_src);
        $this->includeConfigInGlobalSrc($globalPartSrc);

        // put the global src into a separate file that will be included in the zip
        $this->withIncludeFileString('global.ily', (string)$globalPartSrc);

        // include also the SATB parts template files
        $this->withIncludeDirectory('satb_parts');

        $this->withFragmentStub('parts/divider', 'pre-src', ['VAR_DIVIDER_TEXT' => 'ZAČÁTEK NOT']);
        $this->withFragmentStub('parts/divider', 'post-src', ['VAR_DIVIDER_TEXT' => 'KONEC NOT']);
    }

    public function withPart(string $name, string $src, 
                                $key_major = 'c', string $time_signature = '4/4',
                                $break_before = false, $part_transpose = false, $page_break_before = false, $hide_voices = []) : self
    {
        $partSrc = new LilypondSrc($src);

        $partSrc->withFragmentStub('parts/part_header', 'header', [
            'VAR_LILYPOND_VERSION' => $this->config->getAttribute('version'),
            'VAR_KEY_MAJOR_BEGIN' => $key_major,
            'VAR_TIME_BEGIN' => $time_signature
        ])->withFragmentStub('parts/part_footer', 'footer');
        
        $partSrc->withFragmentStub('parts/divider', 'pre-src', ['VAR_DIVIDER_TEXT' => 'ZAČÁTEK NOT']);
        $partSrc->withFragmentStub('parts/divider', 'post-src', ['VAR_DIVIDER_TEXT' => 'KONEC NOT']);

        // hidden voices in part apply on top of hidden voices in global config
        foreach ($hide_voices as $voice_name) {
            $partSrc->withFragmentStub('parts/hide_voice', 'header', [
                'VAR_VOICE_NAME' => $voice_name
            ]);
        }

        // include the part in the final zip
        $this->withIncludeFileString("$name.ly", (string)$partSrc);

        // add the \include directive to the total score file
        // this depends on the global config 
        // (so far only one such exists)
        $this->withFragmentStub('parts/total_part_include', 'src', [
            'VAR_PART_FILE' => "$name.ly",
            'VAR_BREAK_BEFORE' => $break_before || $this->config->getAttribute('force_part_breaks'),
            'VAR_PAGE_BREAK_BEFORE' => $page_break_before,
            'VAR_PART_TRANSPOSE' => $part_transpose
        ]);

        return $this;
    }

    public function withInlineCode(string $src) : self
    {
        $this->withFragmentStub('parts/total_part_inline_lp', 'src', [
            'VAR_LILYPOND_CODE' => $src
        ]);

        return $this;
    }

    protected function includeConfigInGlobalSrc(LilypondSrc $global_src) : void
    {
        $global_src->withFragmentStub('parts/global_config', 'header', [
            'VAR_TWO_VOICES_PER_STAFF' => $this->config->getAttribute('two_voices_per_staff'),
            'VAR_GLOBAL_TRANSPOSE_RELATIVE_C' => $this->config->getAttribute('global_transpose_relative_c'),
        ]);

        foreach ($this->config->getAttribute('hide_voices') as $voice_name) {
            $global_src->withFragmentStub('parts/hide_voice', 'header', [
                'VAR_VOICE_NAME' => $voice_name
            ]);
        }

        if ($this->config->getAttribute('merge_rests')) {
            $global_src->withFragmentStub('parts/merge_rests', 'header');
        }

        if ($this->config->getAttribute('hide_bar_numbers')) {
            $global_src->withFragmentStub('parts/hide_bar_numbers', 'header');
        }
        
        if ($this->config->getAttribute('note_splitting')) {
            $global_src->withFragmentStub('parts/note_splitting', 'header');
        }

        // font setup
        $global_src->withFragmentStub('parts/font', 'header', [
            'VAR_FONT_NAME' => $this->config->getAttribute('font'),
            'VAR_FONT_SIZE' => $this->config->getAttribute('font_size'),
            'VAR_CHORD_FONT_NAME' => $this->config->getAttribute('chord_font'),
            'VAR_CHORD_FONT_SIZE' => $this->config->getAttribute('chord_font_size')
        ]);

        // paper setup 
        if ($this->config->getAttribute('paper_type') == LilypondPartsRenderConfig::CUSTOM_PAPER_SIZE) {
            $global_src->withFragmentStub('parts/paper_define_custom_size', 'header', [
                'VAR_WIDTH_MM' => $this->config->getAttribute('paper_width_mm')
            ]);
        }

        $global_src->withFragmentStub('parts/paper', 'header', [
            'VAR_PAPER_SIZE' => $this->config->getAttribute('paper_type'),
            'VAR_INDENT' => $this->config->getAttribute('indent'),
            'VAR_TOP_MARGIN' => $this->config->getAttribute('top_margin'),
            'VAR_SYSTEM_PADDING' => $this->config->getAttribute('system_padding')
        ]);
    }
}

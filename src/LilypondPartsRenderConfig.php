<?php

namespace ProScholy\LilypondRenderer;

use Exception;

/**
 * This class is a kind of DTO (Data Transfer Object) that represents a configuration for the LilyPondPartsTemplate class.
 */
class LilypondPartsRenderConfig
{
    // as defined in the parts/paper_define_custom_size stub
    const CUSTOM_PAPER_SIZE = 'custom-paper';

    protected $render_config_data;

    /**
     * Instantiate the render config with an array of input data
     *
     * @param array $render_config_data
     */
    public function __construct(array $render_config_data = [])
    {
        $valid_keys = array_intersect(array_keys($render_config_data), array_keys($this->getDefaultConfigData()));
        $invalid_keys = array_diff(array_keys($render_config_data), $valid_keys);
        if (count($invalid_keys)) {
            throw new Exception("Invalid config keys provided: [ " . join(',', $invalid_keys) . " ]");
        }

        $this->render_config_data = array_merge($this->getDefaultConfigData(), $render_config_data);
    }

    /**
     * Get the default config data of all available options
     *
     * @return array
     */
    public function getDefaultConfigData() : array
    {
        return [
            'hide_bar_numbers' => true,
            'hide_page_numbers' => true,

            // based on the available server LilyPond version, currently only the recent 2.22.0 is supported
            'version' => "2.22.0",

            // applies the TwoVoicesPerStaff, as shown in the documentation of the satb.ly template 
            // see http://lilypond.org/doc/v2.22/Documentation/learning/satb-template
            'two_voices_per_staff' => true,

            // applies a global transposition, it is represented by LilyPon note name
            'global_transpose_relative_c' => false,

            // applies merging rests for multi-voice staves
            // see https://lilypond.org/doc/v2.22/Documentation/notation/multiple-voices#merging-rests 
            'merge_rests' => true,


            // applies the completion heads and completion rests engravers
            // see http://lilypond.org/doc/v2.22/Documentation/notation/displaying-rhythms#automatic-note-splitting
            'note_splitting' => true,

            // use the mmrest-skip-of-lengts for filling empty voices, this is rather an experimental feature
            // currently, it doesn't work with unfinished measures
            'use_mm_rests' => false,

            // disable prefilling of empty voices with (spacer) rests when it may cause troubles
            // (e.g., currently it causes incorrect page breaking)
            'disable_prefilling' => false,

            // sets the font used, which must be installed on the renderer server unless 'include_font_files' is set to true
            // this library currently contains only a couple of fonts as a showcase 
            'font' => 'amiri',
            'font_size' => 2.5,
            'chord_font' => 'amiri',
            'chord_font_size' => 1.5,

            // paper type is by default custom, with width equal to 'paper_width_mm' and dynamic height
            // the height is updated automatically based on the height of the sheet music
            // which is provided by http://lilypond.org/doc/v2.22/Documentation/notation/page-breaking#one_002dpage-page-breaking
            'paper_type' => LilypondPartsRenderConfig::CUSTOM_PAPER_SIZE,
            'paper_width_mm' => 120,

            // page properties
            'indent' => 0,
            'top_margin' => 1,

            // includes required font files in the final ZIP file
            'include_font_files' => false,
            
            // this is only a part of spacing config as the others do not influence the layout on infinite paper
            // see https://lilypond.org/doc/v2.19/Documentation/notation/flexible-vertical-spacing-paper-variables
            'system_padding' => 2,

            // hides voices in the rendered output
            // currently supported voices are: solo, akordy, sopran, alt, zeny, tenor, bas, muzi
            'hide_voices' => []
        ];
    }
    
    /**
     * Get value of an attribute attr_name
     *
     * @param string $attr_name
     * @return any
     */
    public function getAttribute(string $attr_name)
    {
        return $this->render_config_data[$attr_name];
    }

    /**
     * Return an array of used font namess
     *
     * @return array
     */
    public function getUsedFonts() : array
    {
        return array_unique([$this->getAttribute('font'), $this->getAttribute('chord_font')]);
    }
}

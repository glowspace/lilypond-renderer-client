<?php

namespace ProScholy\LilypondRenderer;

use Exception;

class LilypondPartsRenderConfig
{
    // as defined in the parts/paper_define_custom_size stub
    const CUSTOM_PAPER_SIZE = 'custom-paper';

    protected $render_config_data;

    public function __construct(array $render_config_data = [])
    {
        $valid_keys = array_intersect(array_keys($render_config_data), array_keys($this->getDefaultConfigData()));
        $invalid_keys = array_diff(array_keys($render_config_data), $valid_keys);
        if (count($invalid_keys)) {
            throw new Exception("Invalid config keys provided: [ " . join(',', $invalid_keys) . " ]");
        }

        $this->render_config_data = array_merge($this->getDefaultConfigData(), $render_config_data);
    }

    public function getDefaultConfigData()
    {
        return [
            'version' => "2.22.0",
            'two_voices_per_staff' => true,
            'global_transpose_relative_c' => false,
            'merge_rests' => true,
            'hide_bar_numbers' => true,
            'force_part_breaks' => false,
            'note_splitting' => true,

            'font' => 'amiri',
            'font_size' => 2.5,
            'chord_font' => 'amiri',
            'chord_font_size' => 1.5,
            'paper_type' => LilypondPartsRenderConfig::CUSTOM_PAPER_SIZE,
            'paper_width_mm' => 120,
            'indent' => 0,
            'top_margin' => 1,
            
            // this is only a part of spacing config as the others do not influence the layout on infinite paper
            // see https://lilypond.org/doc/v2.19/Documentation/notation/flexible-vertical-spacing-paper-variables
            'system_padding' => 2,

            'hide_voices' => [] // solo, akordy, sopran, alt, tenor, bas
        ];
    }

    public function setFontAndSize($font, $font_size)
    {
        $this->render_config_data['font'] = $font;
        $this->render_config_data['font_size'] = $font_size;
    }

    public function setChordFont($font, $font_size)
    {
        $this->render_config_data['chord_font'] = $font;
        $this->render_config_data['chord_font_size'] = $font_size;
    }

    public function setCustomPaper($paper_width = 120)
    {
        $this->render_config_data['paper_type'] = LilypondPartsRenderConfig::CUSTOM_PAPER_SIZE;
        $this->render_config_data['paper_width_mm'] = $paper_width;
    }

    public function setPaper($paper, $indent, $top_margin)
    {
        $this->render_config_data['paper_type'] = $paper;
        $this->render_config_data['indent'] = $indent;
        $this->render_config_data['top_margin'] = $top_margin;
    }

    public function setSystemPadding($system_padding = 2)
    {
        $this->render_config_data['system_padding'] = $system_padding;
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
}

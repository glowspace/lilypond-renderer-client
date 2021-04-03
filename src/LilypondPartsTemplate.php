<?php

namespace ProScholy\LilypondRenderer;

class LilypondPartsTemplate extends LilypondSrc
{
    protected LilypondPartsGlobalConfig $config;

    public function __construct(string $global_src = '', ?LilypondPartsGlobalConfig $config = null, bool $include_template_files = true)
    {
        parent::__construct('');

        $this->config = $config ?? new LilypondPartsGlobalConfig();

        $this->withFragmentStub('parts/total_header', 'header', [
            'VAR_LILYPOND_VERSION' => $this->config->getLilypondVersion()
        ])->withFragmentStub('parts/total_footer', 'footer');

        $globalPartSrc = new LilypondSrc($global_src);
        $this->config->setUpGlobalSrc($globalPartSrc);

        // put the global src into a separate file that will be included in the zip
        $this->withIncludeFileString('global.ily', (string)$globalPartSrc);

        if ($include_template_files) {
            // include also the SATB parts template files
            $this->withIncludeDirectory('satb_parts');
        }
    }

    public function withPart(string $name, string $src, 
                                $key_major = 'c', $time_signature = '4/4', $end_time_signature = false, $end_key_major = false, 
                                $break_before = false, $part_transpose = false)
    {
        $partSrc = new LilypondSrc($src);

        $partSrc->withFragmentStub('parts/part_header', 'header', [
            'VAR_LILYPOND_VERSION' => $this->config->getLilypondVersion(),
            'VAR_KEY_MAJOR_BEGIN' => $key_major,
            'VAR_KEY_MAJOR_END' => $end_key_major,
            'VAR_TIME_BEGIN' => $time_signature,
            'VAR_TIME_END' => $end_time_signature
        ])->withFragmentStub('parts/part_footer', 'footer');

        // include the part in the final zip
        $this->withIncludeFileString("$name.ly", (string)$partSrc);

        // add the \include directive to the total score file
        // this depends on the global config 
        // (so far only one such exists)
        // todo: add more part_include files for different layouts
        $this->withFragmentStub($this->config->getPartIncludeStub(), 'src', [
            'VAR_PART_FILE' => "$name.ly",
            'VAR_BREAK_BEFORE' => $break_before || $this->config->getForcePartBreaks(),
            'VAR_PART_TRANSPOSE' => $part_transpose
        ]);
    }
}

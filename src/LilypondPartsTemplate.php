<?php

namespace ProScholy\LilypondRenderer;

class LilypondPartsTemplate extends LilypondSrc
{
    public function __construct(string $global_src, bool $two_voices_per_staff = true, bool $include_template_files = true)
    {
        parent::__construct('');

        $this->withFragmentStub('parts/total_header', 'header')
            ->withFragmentStub('parts/total_footer', 'footer');

        $globalPartSrc = new LilypondSrc($global_src);
        $globalPartSrc->withFragmentStub('parts/global_config', 'header', ['VAR_TWO_VOICES_PER_STAFF' => $two_voices_per_staff]);

        // put the global src into a separate file that will be included in the zip
        $this->withIncludeFileString('global.ily', (string)$globalPartSrc);

        if ($include_template_files) {
            // include also the SATB parts template files
            $this->withIncludeDirectory('satb_parts');
        }
    }

    public function withPart(string $name, string $src, $key_major = 'c', $time_signature = '4/4', $end_time_signature = null)
    {
        $partSrc = new LilypondSrc($src);

        $partSrc->withFragmentStub('parts/part_head', 'header', [
            'VAR_KEY_MAJOR' => $key_major,
            'VAR_TIME' => $time_signature,
            'VAR_TIME_END' => $end_time_signature === null ? $time_signature : $end_time_signature
        ])->withFragmentStub('parts/part_footer', 'footer');

        // include the part in the final zip
        $this->withIncludeFileString("$name.ly", (string)$partSrc);

        // add the \include directive to the total score file
        $this->withFragmentStub('parts/total_part_include', 'src', ['VAR_PART_FILE' => "$name.ly"]);
    }
}

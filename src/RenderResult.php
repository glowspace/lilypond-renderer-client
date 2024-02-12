<?php

namespace ProScholy\LilypondRenderer;

use Exception;

/**
 * A class to represent an output of the lilypond server response.
 * This is internally used by the Client.php class
 */
class RenderResult
{
    protected $recipe;
    protected $renderResult;
    protected $isDeleted;

    protected $recipeOutputFiles = [
        'svgopt' => 'score_optimized.svg',
        'svg' => 'score.svg',
        'eps' => 'score.eps',
        'pdf' => 'score.pdf',
        'pdfcrop' => 'score_cropped.pdf',
        'pngcrop' => 'score_cropped.png',
        'svgcropcurves' => 'score_cropped_curves.svg',
        'svgxml' => 'score_mxml.svg',
    ];

    /**
     * Create an instance from a server response.
     *
     * @param string $recipe
     * @param array $renderResult
     */
    public function __construct(string $recipe, array $renderResult)
    {
        $this->recipe = $recipe;
        $this->renderResult = $renderResult;
        $this->isDeleted = false;
    }

    /**
     * Returns, wheter the server response contains required file(s).
     *
     * @return bool
     */
    public function isSuccessful() : bool
    {
        return $this->contentsHasFile($this->getRecipeOutputFile());
    }

    /**
     * Returns, whether the result state has been set to 'deleted'
     *
     * @return bool
     */
    public function isDeleted() : bool
    {
        return $this->isDeleted;
    }

    /**
     * Sets the state to 'deleted'
     *
     * @return void
     */
    public function markAsDeleted()
    {
        $this->isDeleted = true;
    }

    /**
     * Return the default ouptut file for the recipe of this instance.
     *
     * @return string
     */
    public function getRecipeOutputFile() : string
    {
        if (!key_exists($this->recipe, $this->recipeOutputFiles)) {
            throw new Exception("Unknown recipe $this->recipe");
        }

        return $this->recipeOutputFiles[$this->recipe];
    }

    /**
     * Return the result folder tmp name
     *
     * @return string
     */
    public function getTmp() : string
    {
        return $this->renderResult[0]->name;
    }
    
    /**
     * Return the contents of the result folder
     *
     * @return array
     */
    public function getContents() : array
    {
        return $this->renderResult[0]->contents;
    }

    /**
     * Check whether the file $filename is present among the rendered files.
     * Oh you are reading this..? Cool
     *
     * @param string $filename
     * @return bool
     */
    public function contentsHasFile(string $filename) : bool
    {
        foreach ($this->getContents() as $file) {
            if ($file->name == $filename) {
                return true;
            }
        }

        return false;
    }
}

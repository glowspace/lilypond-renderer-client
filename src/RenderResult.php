<?php

namespace ProScholy\LilypondRenderer;

use Exception;

class RenderResult
{
    protected $recipe;
    protected $renderResult;
    protected $isDeleted;

    protected $recipeOutputFiles = [
        'svgcrop' => 'score_cropped.svg',
        'svg' => 'score.svg',
        'eps' => 'score.eps',
        'pdfcrop' => 'score_cropped.pdf',
        'pngcrop' => 'score_cropped.png',
        'svgcropcurves' => 'score_cropped_curves.svg'
    ];

    public function __construct(string $recipe, array $renderResult)
    {
        $this->recipe = $recipe;
        $this->renderResult = $renderResult;
        $this->isDeleted = false;
    }

    public function isSuccessful()
    {
        return $this->contentsHasFile($this->getRecipeOutputFile());
    }

    public function isDeleted()
    {
        return $this->isDeleted;
    }

    public function markAsDeleted()
    {
        $this->isDeleted = true;
    }

    public function getRecipeOutputFile()
    {
        if (!key_exists($this->recipe, $this->recipeOutputFiles)) {
            throw new Exception("Unknown recipe $this->recipe");
        }

        return $this->recipeOutputFiles[$this->recipe];
    }

    public function getTmp()
    {
        return $this->renderResult[0]->name;
    }
    
    public function getContents()
    {
        return $this->renderResult[0]->contents;
    }

    public function contentsHasFile(string $filename)
    {
        foreach ($this->getContents() as $file) {
            if ($file->name == $filename) {
                return true;
            }
        }

        return false;
    }
}

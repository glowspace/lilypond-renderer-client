<?php

namespace ProScholy\LilypondRenderer;

enum InputFileType
{
    case LilypondSimple;
    case LilypondZip;
    case MusicXML;
}
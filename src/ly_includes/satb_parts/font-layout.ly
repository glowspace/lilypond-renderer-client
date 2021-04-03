\layout {
    \context {
      \Lyrics {
        \override LyricText #'font-name = \font
        \override StanzaNumber #'font-name = \font
        \override LyricText #'font-size = \fontSize	
        \override StanzaNumber #'font-size = \fontSize
      }
    }
    \context {
      \ChordNames
      \override ChordName #'font-name = \chordFont
      \override ChordName #'font-size = \chordFontSize
    }
}
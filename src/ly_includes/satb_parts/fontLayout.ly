\layout {
    \context {
      \Lyrics {
        %\override LyricHyphen.minimum-distance = #1	% vynucení pomlček mezi slabikami
        \override LyricText #'font-name = \font
        \override StanzaNumber #'font-name = \font
        \override LyricText #'font-size = \fontSize	
        \override StanzaNumber #'font-size = \fontSize
      }
    }
    \context {
      \ChordNames
      \override VerticalAxisGroup.nonstaff-relatedstaff-spacing.padding = #0.7		% posunuje akordy výš (defaultně 0.5)
      \override ChordName #'font-name = \chordFont
      \override ChordName #'font-size = \chordFontSize
    }
}
#(define-missing-variables! '("globalTransposeRelativeC") #f)

#(if (not globalTransposeRelativeC)
    (set! globalTransposeRelativeC #{ c #}))

totalScoreObject = \transpose c \globalTransposeRelativeC \totalScoreObject

\tagGroup #'(print play)

\score {
  \keepWithTag #'print
  \totalScoreObject
  \layout { $(if Layout Layout) }
}

\score {
  \keepWithTag #'play
  \totalScoreObject
  \midi {
    \context {
      \Score
      midiChannelMapping = #'instrument
    }
  }
}
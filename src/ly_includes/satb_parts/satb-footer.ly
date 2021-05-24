%%%% The score's "footer" which renders the concatenated score in totalScoreObject.
%%%% Created by Miroslav Sery
%%%% for ProScholy.cz

#(define-missing-variables! '("globalTransposeRelativeC") #f)

#(if (not globalTransposeRelativeC)
    (set! globalTransposeRelativeC #{ c #}))

% when Time = ... is at the end (e.g. setting bars)
totalScoreObject = #(if Time #{ { \totalScoreObject \Time } #} totalScoreObject)
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
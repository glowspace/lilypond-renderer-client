\include "vocal-tkit.ly"
\include "piano-tkit.ly"
\include "chord-tkit.ly"
\include "add-note-small.ly"
\include "vynech.ly"
\include "custom-chords.ly"
\include "fix-voices.ly"
\include "tiny-notes.ly"

#(define satb-voice-prefixes
   ;; These define the permitted prefixes to various names.
   ;; They are combined with a fixed set of postfixes to form
   ;; names such as AltoMusic, BassInstrumentName, etc.
   ;; These names may be redefined.
   '("alt"
     "bas"
     "muzi"
     "solo"
     "sopran"
     "tenor"
     "zeny"
     "empty"
     "akordy")) %do not use the empty variable

#(define satb-lyrics-postfixes
   ;; These define the permitted postfixes to the names of lyrics.
   ;; They are combined with the prefixes to form names like
   ;; AltoLyrics, etc.
   ;; These names may be redefined or extended.
  '("Text"
    "TextI"
    "TextII"
    "TextIII"
    "TextIV"
    "TextV"
    "TextVI"
    "TextVII"
    "TextVIII"
    "TextIX"
    "TextX"))

#(define satb-lyrics-variable-names
   ;; These define the names which may be used to specify stanzas
   ;; which go between the two two-voice staves when TwoVoicesPerStaff
   ;; is set to #t.  They may be redefined or extended.
  '())


\layout {
  \context {
    \Staff
    \override VerticalAxisGroup.remove-empty = ##t
    \override VerticalAxisGroup.remove-first = ##t
  }
}

% soloDruhy is the only voice variable defined outside the normal list of voice variables
#(define-missing-variables! '("soloDruhy") #t)

#(define-missing-variables! 
'("totalScoreObject"
  "timeSignature" "lastTimeSignature" "endTimeSignature"
  "keyMajor" "endKeyMajor" "lastTransposedKeyMajor"
  "hideChords" 
  "twoVoicesPerStaff"
  "onlySolo"
  "partTranspose"
  "breakBefore") #f)

% prepare the totalScoreObject where each part "pushes" its content
#(if (not totalScoreObject)
  (set! totalScoreObject #{ {} #}))

\header {
  tagline = ##f
}

% use a dummy variable to reset the quarter-note default duration
% this is useful when the user expects LilyPond's default behaviour
% However.. it is always better to start the music expression with an explicit duration
foo = { c4 }
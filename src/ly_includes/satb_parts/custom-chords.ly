%%%% Customization of chords' appearance.
%%%% Created by Miroslav Sery and Filip Kratoš
%%%% for ProScholy.cz


flatSign = #(alteration->text-accidental-markup FLAT)
sharpSign = #(alteration->text-accidental-markup SHARP)

% Tweak displaying of the chords
chExceptionMusic = {
  <c e g b d'>-\markup { \super "maj9" }
  <c es ges>-\markup { "dim" }
  <c es ges beses>-\markup { "dim" \super 7 }
  <c es ges bes>-\markup { m \super { "7 " \flatSign 5}} 
  <c e gis b>-\markup { + \super maj } 

  % fix 7#11 to add9 #11
  <c es g bes d' fis'>-\markup { m \super { "add9 " \sharpSign 11 }}
  <c e g bes d' fis'>-\markup { \super { "add9 " \sharpSign 11 }}
  % fix 7b11 to add9 b11
  <c es g bes d' fes'>-\markup { m \super { "add9 " \flatSign 11 }} 
  <c e g bes d' fes'>-\markup { \super { "add9 " \flatSign 11 }} 
  % fix m7 b5 9 to 9 b5
  <c es ges bes d'>-\markup { m \super { "9 " \flatSign 5 }} 
  <c e ges bes d'>-\markup { \super { "9 "  \flatSign 5 }} 
}

% Convert music to list and prepend to existing exceptions.
chExceptions = #(append
  (sequential-music-to-chord-exceptions chExceptionMusic #t)
  ignatzekExceptions)

% adjust font sizes of alterations at ChordRoot (both sharp and flat are too big)
#(define (custom-alteration-markup alt)
  (if (= alt 0)
    (markup "")
    (if (= alt FLAT)
      (make-fontsize-markup -1 (alteration->text-accidental-markup alt))
      (make-fontsize-markup -2 (make-raise-markup 0.2 (alteration->text-accidental-markup alt)))
    )
  )
)

% a helper function copied from scm/chord-name.scm
#(define (pitch-alteration-semitones pitch)
  (inexact->exact (round (* (ly:pitch-alteration pitch) 2))))

% a tweaked chord-name->german-markup function (uses the custom alteration markup)
#(define (MyChordNames pitch foo)	;foo is a required argument for "chordNamer", but not used here
  (let* ((name (ly:pitch-notename pitch))
         (alt-semitones  (pitch-alteration-semitones pitch))
         (n-a (if (member (cons name alt-semitones) `((6 . -1) (6 . -2)))
                  (cons 7 (+ 1 alt-semitones))
                  (cons name alt-semitones))))
  (make-line-markup
    (list
      (make-simple-markup
        (vector-ref #("C" "D" "E" "F" "G" "A" "H" "B")
          (car n-a)))
        (custom-alteration-markup
          (/ (cdr n-a) 2))
    )
  )
))

\layout {
  \context {
    \ChordNames {
      \override VerticalAxisGroup.nonstaff-relatedstaff-spacing.padding = #0.7
      \set chordRootNamer = #MyChordNames
      \set majorSevenSymbol = \markup { maj }
      \set additionalPitchPrefix = #"add"
      \set chordNameExceptions = #chExceptions
    }
  }
}
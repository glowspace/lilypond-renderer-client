% defined in satb_header.ly

#(set-music-definitions!
  satb-voice-prefixes
  satb-lyrics-postfixes
  satb-lyrics-variable-names)

% defined by user

% TIME SIGNATURE handling

#(if (and (not Time) solo)
      (set! Time #{ \vynech \solo #}))
#(if (and (not Time) sopran)
      (set! Time #{ \vynech \sopran #}))  
#(if (and (not Time) zeny)
      (set! Time #{ \vynech \zeny #}))  

#(placehold-voices-and-lyrics! Time)


Time = #(if breakBefore #{ { \bar "" \break \Time } #})

timeSignatureNotChanged = #(equal? timeSignature lastTimeSignature)

% include time_signature when different from lastTimeSignature
Time = #(if timeSignatureNotChanged Time
  #{ {
      \timeSignature
      \Time
    } #})

#(if endTimeSignature 
  (set! lastTimeSignature endTimeSignature) 
  (set! lastTimeSignature timeSignature))


% KEY SIGNATURE handling

% no transpose was defined => "transpose" by 0 = keyMajor - keyMajor
#(if (not partTranspose) 
    (set! partTranspose keyMajor))

% helper variables
transposedKeyMajor = \transpose \keyMajor \partTranspose \keyMajor
transposedEndKeyMajor = #(if endKeyMajor #{ \transpose \keyMajor \partTranspose \endKeyMajor #})

% helper function to get only the pitch
#(define (get-transposed-music-pitch music)
  (let ((el (ly:music-property music 'element)))
    (ly:music-property el 'pitch)
  ))


% determine if key has changed since the last time 
% note: equal? can be used here because both props are of type TransposedMusic
keyNotChanged = #(and lastTransposedKeyMajor (equal? 
    (get-transposed-music-pitch transposedKeyMajor)
    (get-transposed-music-pitch lastTransposedKeyMajor)))

#(if endKeyMajor 
  (set! lastTransposedKeyMajor transposedEndKeyMajor) 
  (set! lastTransposedKeyMajor transposedKeyMajor))


#(if (and soloDruhy solo)
      (set! solo #{ \addNoteSmall -2 \solo \soloDruhy #}))


% ensure empty `is` empty
% it is used in make-one-voice-vocal-staff-fixed for an empty voice
% which fixes the context concatenation for single voice lyrics
#(set! empty #f)

SATB =
<<
  \context Staff = "SoloStaff" << 
    \make-chords "akordy"
    \make-one-voice-vocal-staff-fixed "solo" "treble"
  >>
  \context ChoirStaff <<
    \make-one-voice-vocal-staff "zeny" "treble"
    #(if twoVoicesPerStaff
      #{
        \make-two-vocal-staves-with-stanzas
          "zeny" "treble" "muzi" "bass"
          "sopran" "alt" "tenor" "bas"
          #satb-lyrics-variable-names
      #}
      #{
        <<
          \make-one-voice-vocal-staff-fixed "sopran" "treble"
          \make-one-voice-vocal-staff-fixed "alt" "treble"
          \make-one-voice-vocal-staff-fixed "tenor" "treble_8"
          \make-one-voice-vocal-staff-fixed "bas" "bass"
        >>
      #} )
    \make-one-voice-vocal-staff "muzi" "bass"
  >>
>>

SATB = \transpose \keyMajor \partTranspose \SATB


totalScoreObject = {
      \totalScoreObject
      \SATB
}

#(define-missing-variables! '("globalRender") #f)

sc = #(if (and have-music (not globalRender))
        #{  \score { \SATB \layout { $(if Layout Layout) } } #})
  
\sc

#(reset-properties!)

% reset variables to false, so that they don't influence the next parts
#(define-missing-variables! '("endTimeSignature") #t)
#(define-missing-variables! '("endKeyMajor") #t)
#(define-missing-variables! '("partTranspose") #t)
#(define-missing-variables! '("breakBefore") #t)
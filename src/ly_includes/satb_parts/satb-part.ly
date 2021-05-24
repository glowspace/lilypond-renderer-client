%%%% Template toolkit
%%%% This file is part of LilyPond, the GNU music typesetter.
%%%%
%%%% Copyright (C) 2015--2021 Trevor Daniels <t.daniels@treda.co.uk>
%%%% Copyright (C) 2021 Miroslav Sery
%%%%
%%%% LilyPond is free software: you can redistribute it and/or modify
%%%% it under the terms of the GNU General Public License as published by
%%%% the Free Software Foundation, either version 3 of the License, or
%%%% (at your option) any later version.
%%%%
%%%% LilyPond is distributed in the hope that it will be useful,
%%%% but WITHOUT ANY WARRANTY; without even the implied warranty of
%%%% MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
%%%% GNU General Public License for more details.
%%%%
%%%% You should have received a copy of the GNU General Public License
%%%% along with LilyPond.  If not, see <http://www.gnu.org/licenses/>.

#(set-music-definitions!
  (append satb-voice-prefixes satb-voice-prefixes-extra)
  satb-lyrics-postfixes
  satb-lyrics-variable-names)

% sets voices-to-be-hidden and their lyrics to false
#(hide-voices! (append satb-voice-prefixes satb-voice-prefixes-extra)
  satb-lyrics-postfixes)

someMusic = ##f
#(if solo (set! someMusic solo))
#(if (and (not someMusic) sopran)
      (set! someMusic sopran))
#(if (and (not someMusic) alt)
      (set! someMusic alt))
#(if (and (not someMusic) zeny)
      (set! someMusic zeny))
#(if (and (not someMusic) tenor)
      (set! someMusic tenor))
#(if (and (not someMusic) bas)
      (set! someMusic bas))
#(if (and (not someMusic) muzi)
      (set! someMusic muzi))

#(if someMusic (placehold-voices-and-lyrics! someMusic satb-voice-prefixes))

Time = #(if Time Time)

% get end key and end time signature directly from music data
#(define (get-last-key-pitch music defaultKey)
   (let ((keyslist (reverse (extract-named-music music 'KeyChangeEvent))))
     (if (pair? keyslist)
         (ly:music-property (car keyslist) 'tonic)
         (ly:music-deep-copy defaultKey))
     ))

#(define (get-last-time-signature music defaultTime)
   (let ((keyslist (reverse (extract-named-music music 'TimeSignatureMusic))))
     (if (pair? keyslist)
         (ly:music-deep-copy (car keyslist))
         (ly:music-deep-copy defaultTime))
     ))

endKeyMajor = #(if solo (get-last-key-pitch solo keyMajor) keyMajor)
endTimeSignature = #(if solo (get-last-time-signature solo timeSignature) timeSignature)


% TIME SIGNATURE handling
timeSignatureNotChanged = #(equal? timeSignature lastTimeSignature)

% include time_signature when different from lastTimeSignature
Time = #(if timeSignatureNotChanged Time
  #{ {
      \timeSignature
      \Time
    } #})

#(set! lastTimeSignature endTimeSignature)


% KEY SIGNATURE handling

% no transpose was defined => "transpose" by 0 = keyMajor - keyMajor
#(if (not partTranspose) 
    (set! partTranspose keyMajor))

% helper variables
transposedKeyMajor = \transpose \keyMajor \partTranspose \keyMajor
transposedEndKeyMajor = \transpose \keyMajor \partTranspose \endKeyMajor

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

#(set! lastTransposedKeyMajor transposedEndKeyMajor)


% handle the soloMale functionality
#(if (and soloMale solo)
      (set! solo #{ \addNoteSmall -2 \solo \soloMale #}))


% ensure empty `is` empty
% it is used in make-one-voice-vocal-staff-fixed for an empty voice
% which fixes the context concatenation for single voice lyrics
#(set! empty #f)

SATB =
<<
  \context Staff = "SoloStaff" << 
    \make-chords "akordy"
    \make-two-voice-vocal-staff "solo" "treble" "solo" "soloII" \soloTextAbove
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

\tagGroup #'(print play)

scprint = #(if (and have-music (not globalRender))
        #{  
            \score {
              \keepWithTag #'play
              \SATB
              \layout { 
                $(if Layout Layout)
              }
            }
        #})

scmidi = #(if (and have-music (not globalRender))
        #{  
            \score {
              \keepWithTag #'play
              \SATB
              \midi {
                \context {
                  \Score
                  midiChannelMapping = #'instrument
                }
              }
            } 
        #})

\scprint

% \book {
%   \scprint
%   \scmidi
% }


#(reset-properties!)

% reset variables to false, so that they don't influence the next parts
#(define-missing-variables! '("partTranspose") #t)
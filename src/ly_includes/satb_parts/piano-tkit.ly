%%%% Piano template.
%%%% This file is part of LilyPond, the GNU music typesetter.
%%%%
%%%% Copyright (C) 2015--2021 Trevor Daniels <t.daniels@treda.co.uk>
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

%%%% NOTE - THIS FILE IS CURRENTLY NOT USED, BUT KEPT FOR LEGACY

\include "staff-tkit.ly"

make-pianostaff =
#(define-music-function () ()

(if (not PianoRHMidiInstrument)
       (set! PianoRHMidiInstrument
             (if PianoMidiInstrument
                 PianoMidiInstrument
                 "acoustic grand")))

(if (not PianoLHMidiInstrument)
       (set! PianoLHMidiInstrument
             (if PianoMidiInstrument
                 PianoMidiInstrument
                 "acoustic grand")))

  (if (or
        PianoRH
        PianoLH)
       #{

\context PianoStaff = "PianoStaff"
  \with {
    instrumentName = \markup \smallCaps
      #(if PianoInstrumentName
           PianoInstrumentName
           "Piano" )
    shortInstrumentName = \markup \smallCaps
      #(if PianoShortInstrumentName
           PianoShortInstrumentName
           "")
  }
  <<
    \make-one-voice-staff ##f "PianoRH" "treble" ""
    #(if PianoDynamics
         #{ \context Dynamics = "PianoDynamics" { #PianoDynamics } #} )
    \make-one-voice-staff ##f "PianoLH" "bass" ""
  >>
       #}
     (make-music 'SequentialMusic 'void #t)))

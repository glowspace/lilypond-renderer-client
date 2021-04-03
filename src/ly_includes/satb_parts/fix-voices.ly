#(define (fix-voice-lyric! id placeholderMusic)
  (define sym (string->symbol id))
  (define val (get-id id))
    (if (ly:music? val)
      (ly:parser-define! sym #{ { $val $placeholderMusic } #} )
      (ly:parser-define! sym placeholderMusic)))

#(define (placehold-voices-and-lyrics! placeholderMusic)
  "Fix voices and their lyrics behaviour with placeholder music.
  Note that for lyrics we need to use unfoldrepeats to fix behaviour with repeats"
  (let 
    ((unfoldedPlaceholderMusic (unfold-repeats-fully (ly:music-deep-copy placeholderMusic))))
    (for-each
        (lambda (voice-prefix)
            (define voice-lyrics (cartesian (list voice-prefix) lyrics-postfixes))
            (define voice-is-empty (eq? (get-id voice-prefix) #f))
            (if voice-is-empty 
            (ly:parser-define! (string->symbol voice-prefix) placeholderMusic)
            (for-each
                (lambda (voice-lyric)
                (fix-voice-lyric! voice-lyric unfoldedPlaceholderMusic))
                voice-lyrics)
            )
        )
        voice-prefixes)))
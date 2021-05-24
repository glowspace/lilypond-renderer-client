%%%% Methods for fixing issues with segment concatenation
%%%% Created by Miroslav Sery
%%%% for ProScholy.cz

#(define (fix-voice-lyric! id placeholderMusic)
  "Fix a lyric variable by inserting placeholder data"
  (define sym (string->symbol id))
  (define val (get-id id))
    (if (ly:music? val)
      (ly:parser-define! sym #{ { $val $placeholderMusic } #} )
      (ly:parser-define! sym placeholderMusic)))

#(define (placehold-voices-and-lyrics! placeholderMusic voice-prefixes)
  "Fix voices and their lyrics behaviour with placeholder music.
  Note that for lyrics we need to filter out problematic data types"
  (let 
    ((lyricsPlaceholder (make-sequential-music (extract-named-music placeholderMusic (list 'NoteEvent 'RestEvent 'EventChord 'SkipEvent 'TimeScaledMusic))))) ;; in particular, do not copy 'ContextSpeccedMusic
    (for-each
        (lambda (voice-prefix)
            (define voice-lyrics (cartesian (list voice-prefix) lyrics-postfixes))
            (define voice-is-empty (eq? (get-id voice-prefix) #f))
            (define placeholder-rest (if useMMRests (mmrest-of-length placeholderMusic) (skip-of-length placeholderMusic)))
            (if voice-is-empty 
              (if (not disablePrefilling) (ly:parser-define! (string->symbol voice-prefix) placeholder-rest))
              (for-each
                  (lambda (voice-lyric)
                  (fix-voice-lyric! voice-lyric lyricsPlaceholder))
                  voice-lyrics)
              )
        )
        voice-prefixes)))
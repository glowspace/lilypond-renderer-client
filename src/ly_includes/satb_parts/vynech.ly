%% Copyright (C) 2021 Miroslav Sery for https://github.com/proscholy

vynech = #(define-music-function (parser location music) (ly:music? )
(map-some-music 
 (lambda(x)
   (let ((dur (ly:music-property x 'duration #f)))
      (and (and (not (eq? 'PartialSet (ly:music-property x 'name))) (or dur (eq? 'EventChord (ly:music-property x 'name))))
           (let ((skip (make-music 'SkipEvent 'duration
                          (or dur (make-duration-of-length (ly:music-length x)))))
                 )
             skip))))
  music))
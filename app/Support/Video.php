<?php

namespace App\Support;

/** Convierte enlaces de YouTube o Vimeo en la URL de incrustación (YouTube sin cookies). */
final class Video
{
    public static function incrustar(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        if (preg_match('~(?:youtube\.com/(?:watch\?v=|embed/|shorts/)|youtu\.be/)([A-Za-z0-9_-]{11})~', $url, $m)) {
            return 'https://www.youtube-nocookie.com/embed/'.$m[1].'?rel=0';
        }

        if (preg_match('~vimeo\.com/(?:video/)?(\d+)~', $url, $m)) {
            return 'https://player.vimeo.com/video/'.$m[1].'?dnt=1';
        }

        return null;
    }
}

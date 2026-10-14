<?php

namespace App\Support\Csp;

use Spatie\Csp\Directive;
use Spatie\Csp\Keyword;
use Spatie\Csp\Policy;
use Spatie\Csp\Preset;
use Spatie\Csp\Value;

/**
 * CSP del sitio público (RNF-05). Sin nonces para que funcione con la caché de respuestas completa.
 * Alpine.js necesita 'unsafe-eval'; no se permiten scripts en línea. El panel /admin no lleva esta política.
 */
class PoliticaSitio implements Preset
{
    public function configure(Policy $policy): void
    {
        $umami = config('rosa.umami.url') ? rtrim((string) config('rosa.umami.url'), '/') : null;

        $policy
            ->add(Directive::BASE, Keyword::SELF)
            ->add(Directive::DEFAULT, Keyword::SELF)
            ->add(Directive::OBJECT, Keyword::NONE)
            ->add(Directive::FORM_ACTION, Keyword::SELF)
            ->add(Directive::FRAME_ANCESTORS, Keyword::SELF)
            ->add(Directive::SCRIPT, [Keyword::SELF, Keyword::UNSAFE_EVAL, 'https://challenges.cloudflare.com'])
            ->add(Directive::STYLE, [Keyword::SELF, Keyword::UNSAFE_INLINE])
            ->add(Directive::FONT, Keyword::SELF)
            ->add(Directive::IMG, [Keyword::SELF, 'data:', 'blob:'])
            ->add(Directive::FRAME, ['https://challenges.cloudflare.com', 'https://www.youtube-nocookie.com', 'https://player.vimeo.com'])
            ->add(Directive::CONNECT, Keyword::SELF);

        if ($umami) {
            $policy->add([Directive::SCRIPT, Directive::CONNECT], $umami);
        }

        if (app()->environment('production')) {
            $policy->add(Directive::UPGRADE_INSECURE_REQUESTS, Value::NO_VALUE);
        }
    }
}

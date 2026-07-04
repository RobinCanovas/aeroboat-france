<?php
declare(strict_types=1);

/** Markdown : XSS neutralisé, schémas d'URL en liste blanche, rendu des blocs et du gras / italique. */
return static function (): array {
    require_once ROOT . '/app/bootstrap.php';
    App\Core\Config::set('APP_URL', 'https://example.test/test');
    $md = static fn (string $s): string => App\Core\Markdown::render($s);
    $errors = [];

    $xss = [
        '<script>alert(1)</script>',
        '<img src=x onerror=alert(1)>',
        '[x](javascript:alert(1))',
        '[x](JaVaScRiPt:alert(1))',
        '[x](data:text/html;base64,PHNjcmlwdD4=)',
        '[x](vbscript:msgbox(1))',
        '[x](//evil.example/)',
        '[x](java&#115;cript:alert(1))',
        '![a](javascript:alert(1))',
        '![a](https://evil.example/t.png)',
        '![a" onerror="alert(1)](/uploads/a.webp)',
        '[a](/x" onmouseover="alert(1))',
        '**<svg onload=alert(1)>**',
        '`<script>`',
        "```\n<script>alert(1)</script>\n```",
        '> <iframe src="https://evil.example"></iframe>',
        '<a href="javascript:alert(1)">x</a>',
    ];
    foreach ($xss as $input) {
        $html = $md($input);
        if (preg_match('/<(script|iframe|svg|object|embed)\b/i', $html)
            || preg_match('/<[a-z][^>]*\son[a-z]+\s*=/i', (string) preg_replace('/"[^"]*"/', '""', $html))
            || preg_match('/(href|src)\s*=\s*"\s*(javascript|data|vbscript):/i', $html)
            || preg_match('/(href|src)\s*=\s*"\/\//i', $html)
            || preg_match('/src="https?:/i', $html)) {
            $errors[] = 'XSS non neutralisé : ' . $input . ' → ' . $html;
        }
    }

    $cases = [
        '# Titre' => '<h2>Titre</h2>',
        '### Sous-titre' => '<h3>Sous-titre</h3>',
        'Texte **gras** et *italique*' => '<p>Texte <strong>gras</strong> et <em>italique</em></p>',
        '[site](https://example.org)' => '<p><a href="https://example.org" rel="noopener noreferrer">site</a></p>',
        '[mail](mailto:a@b.fr)' => '<p><a href="mailto:a@b.fr" rel="noopener noreferrer">mail</a></p>',
        '[rel](/fr/projet)' => '<p><a href="/test/fr/projet" rel="noopener noreferrer">rel</a></p>',
        '![Maquette](/uploads/posts/a.webp)' => '<p><img src="/test/uploads/posts/a.webp" alt="Maquette" loading="lazy" decoding="async"></p>',
        "- un\n- deux" => '<ul><li>un</li><li>deux</li></ul>',
        "1. un\n2. deux" => '<ol><li>un</li><li>deux</li></ol>',
        '> cité' => '<blockquote><p>cité</p></blockquote>',
        'a < b & c' => '<p>a &lt; b &amp; c</p>',
        'snake_case_word' => '<p>snake_case_word</p>',
        '`x < y`' => '<p><code>x &lt; y</code></p>',
    ];
    foreach ($cases as $in => $expected) {
        $got = $md($in);
        if ($got !== $expected) {
            $errors[] = "rendu inattendu pour « $in » : $got";
        }
    }
    return $errors;
};

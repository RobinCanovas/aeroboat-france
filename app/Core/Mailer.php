<?php
declare(strict_types=1);

namespace App\Core;

/** Envoi via mail() en best effort : ne lève jamais d'exception, la base reste la source de vérité. */
final class Mailer
{
    public static function send(string $to, string $subject, string $body, ?string $replyTo = null): bool
    {
        try {
            if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
                return false;
            }
            if (Config::env('APP_ENV', 'production') === 'local' || !function_exists('mail')) {
                Logger::info('Mail non envoyé (environnement local)', ['subject' => self::clean($subject)]);
                return false;
            }
            $host = (string) (parse_url((string) Config::get('app.url'), PHP_URL_HOST) ?: 'localhost');
            $from = (string) Config::env('MAIL_FROM', '');
            if (!filter_var($from, FILTER_VALIDATE_EMAIL)) {
                $from = 'no-reply@' . preg_replace('/^www\./', '', $host);
            }
            $headers = [
                'From' => 'Aeroboat France <' . $from . '>',
                'MIME-Version' => '1.0',
                'Content-Type' => 'text/plain; charset=UTF-8',
                'Content-Transfer-Encoding' => '8bit',
                'X-Auto-Response-Suppress' => 'All',
            ];
            if ($replyTo !== null && filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
                $headers['Reply-To'] = self::clean($replyTo);
            }
            $subj = mb_encode_mimeheader(self::clean($subject), 'UTF-8', 'B', "\r\n");
            $ok = @mail($to, $subj, str_replace(["\r\n", "\r"], "\n", $body), $headers, '-f' . $from);
            if (!$ok) {
                Logger::warning('Échec de mail()');
            }
            return (bool) $ok;
        } catch (\Throwable $e) {
            Logger::warning('Exception Mailer : ' . $e->getMessage());
            return false;
        }
    }

    private static function clean(string $s): string
    {
        return trim(str_replace(["\r", "\n", "\0"], ' ', $s));
    }
}

<?php
declare(strict_types=1);

namespace App\Core;

/** Erreur de téléversement ; getMessage() = clé de langue (forms.errors.*). */
final class UploadError extends \RuntimeException
{
}

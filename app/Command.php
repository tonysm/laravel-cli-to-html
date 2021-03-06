<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use SensioLabs\AnsiConverter\AnsiToHtmlConverter;

/**
 * @property string $command
 * @property int|null exit_code
 * @property \Illuminate\Support\Carbon|null $completed_at
 */
class Command extends Model
{
    protected $fillable = [
        'command',
    ];

    protected $casts = [
        'exit_code' => 'int',
    ];

    public function markAsFinished(int $exitCode, string $output): void
    {
        $this->forceFill([
            'exit_code' => $exitCode,
            'output' => $output,
            'completed_at' => now(),
        ])->save();
    }

    public function toAnsiHtml(): string
    {
        return (new AnsiToHtmlConverter())->convert($this->output);
    }
}

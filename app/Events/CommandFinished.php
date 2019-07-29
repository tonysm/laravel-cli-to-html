<?php

namespace App\Events;

use App\Command;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;

class CommandFinished
{
    use Dispatchable, SerializesModels;

    /**
     * @var \App\Command
     */
    public $command;

    /**
     * Create a new event instance.
     *
     * @param \App\Command $command
     */
    public function __construct(Command $command)
    {
        $this->command = $command;
    }
}

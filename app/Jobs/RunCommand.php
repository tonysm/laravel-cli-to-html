<?php

namespace App\Jobs;

use App\Command;
use App\Events\CommandFinished;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Symfony\Component\Process\Process;

class RunCommand implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var \App\Command
     */
    public $command;

    /**
     * Create a new job instance.
     *
     * @param \App\Command $command
     */
    public function __construct(Command $command)
    {
        $this->command = $command;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $process = new Process($this->command->command, base_path());
        $process->run();

        $exitCode = $process->getExitCode();
        $output = $process->getOutput();

        $this->command->markAsFinished($exitCode, $output);

        event(new CommandFinished($this->command));
    }
}

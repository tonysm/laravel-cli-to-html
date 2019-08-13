<?php

namespace App\Jobs;

use App\Command;
use Illuminate\Bus\Queueable;
use App\Events\CommandFinished;
use Illuminate\Encryption\Encrypter;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\Process\Process;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Symfony\Component\Process\Exception\ProcessTimedOutException;

class RunCommand implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    const PROC_TIMEOUT_IN_SECONDS = 4;
    const TIMEOUT_EXIT_CODE = 124;

    /**
     * One second more than the command timeout.
     *
     * @var int
     */
    public $timeout = 5;

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
        $dockerWrapper = $this->wrapCommandInDocker();

        $process = new Process(
            $dockerWrapper . sprintf(' bash -c "%s"', $this->command->command),
            base_path()
        );
        $process->setTimeout(static::PROC_TIMEOUT_IN_SECONDS);

        try {
            $process->run();
            $exitCode = $process->getExitCode();
            $output = $process->getOutput();
        } catch (ProcessTimedOutException $exception) {
            $exitCode = static::TIMEOUT_EXIT_CODE;
            $output = $this->parseTimeoutMessage();
        }

        $this->command->markAsFinished($exitCode, $output);

        event(new CommandFinished($this->command));
    }

    private function wrapCommandInDocker(): string
    {
        return sprintf(
            'docker run -e "APP_KEY=%s" --rm --stop-timeout %d tonysm/laravel-app:1.0',
            $this->generateKey(),
            static::PROC_TIMEOUT_IN_SECONDS
        );
    }

    private function generateKey(): string
    {
        return sprintf(
            'base64:%s',
            base64_encode(Encrypter::generateKey(config('app.cipher')))
        );
    }

    private function parseTimeoutMessage(): string
    {
        return sprintf(
            'Command "%s" exceeded a timeout of %s.',
            $this->command->command,
            trans_choice('{0} 0 seconds|{1} :count second|{2,*} :count seconds', static::PROC_TIMEOUT_IN_SECONDS)
        );
    }
}

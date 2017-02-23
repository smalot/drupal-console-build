<?php


namespace Smalot\Drupal\Console\Build;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

/**
 * Class Command
 * @package Smalot\Drupal\Console\Build
 */
class Command
{
    /**
     * @var string
     */
    protected $code;

    /**
     * @var string
     */
    protected $stage;

    /**
     * @var array
     */
    protected $only;

    /**
     * @var array
     */
    protected $except;

    /**
     * @var array
     */
    protected $script;

    /**
     * @var bool
     */
    protected $allowFailure;

    /**
     * Command constructor.
     * @param string $code
     * @param array $build
     */
    public function __construct($code, $build)
    {
        $this->code = $code;

        $build += [
          'stage' => null,
          'script' => [],
          'only' => [],
          'except' => [],
          'allow_failure' => false,
        ];

        $build['script'] = (array)$build['script'];
        $build['only'] = (array)$build['only'];
        $build['except'] = (array)$build['except'];

        $this->stage = $build['stage'];
        $this->script = $build['script'];
        $this->only = $build['only'];
        $this->except = $build['except'];
        $this->allowFailure = $build['allow_failure'];

        if (!empty($this->only) && !empty($this->except)) {
            throw new \InvalidArgumentException("'only' and 'except' attributes can't be set at the same time.");
        }
    }

    /**
     * @param string $stage
     * @return bool
     */
    public function matchStage($stage)
    {
        if ($stage == Build::STAGE_ALL) {
            return true;
        }

        return !empty($this->stage) ? $this->stage == $stage : false;
    }

    /**
     * @param string $branch
     * @return bool
     */
    public function matchBranch($branch)
    {
        // Valid by default if no instructions.
        if (empty($this->only) && empty($this->except)) {
            return true;
        }

        if (!empty($this->only)) {
            foreach ($this->only as $pattern) {
                if (preg_match('/^\/.*\/$/', $pattern) && preg_match($pattern, $branch)) {
                    return true;
                } elseif ($branch == $pattern) {
                    return true;
                }
            }

            return false;
        } else {
            foreach ($this->except as $pattern) {
                if (preg_match('/^\/.*\/$/', $pattern) && preg_match($pattern, $branch)) {
                    return false;
                } elseif ($branch == $pattern) {
                    return false;
                }
            }

            return true;
        }
    }

    /**
     * @param string $folder
     * @param OutputInterface $output
     * @return bool
     */
    public function run($folder, OutputInterface $output)
    {
        $output->writeln('');
        $output->writeln(
          '<info>Run \''.$this->code.'\' task'.($this->stage ? '(stage: \''.$this->stage.'\')' : '').'</info>'
        );

        foreach ($this->script as $script) {
            $output->writeln('$ '.$script);

            $process = new Process($script, $folder);
            $status = $process->run(
              function ($type, $data) use ($output) {
                  if ($type == Process::OUT) {
                      $output->write($data);
                  } else {
                      $output->write('<error>'.$data.'</error>');
                  }
              }
            );

            $output->writeln('');

            if ($status) {
                $output->writeln('<error>> The command \''.$script.'\' exited with '.$status.'.</error>');

                if (!$this->allowFailure) {
                    $output->writeln('<error>Stop: failure not allowed</error>');

                    return false;
                } else {
                    $output->writeln('<comment>Continue: failure allowed</comment>');
                }
            } else {
                $output->writeln('<comment>> The command \''.$script.'\' exited with '.$status.'.</comment>');
            }
        }

        return true;
    }
}

<?php

namespace Smalot\Drupal\Console\Build;

use Symfony\Component\Process\Process;

/**
 * Class Git
 * @package Smalot\Drupal\Console\Build
 */
class Git
{
    /**
     * Git constructor.
     */
    public function __construct()
    {
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        $command = 'git rev-parse --abbrev-ref HEAD';
        $process = new Process($command);

        // No error? Exists!
        if (!$process->run()) {
            return true;
        }

        // Else, check error.
        $error = $process->getErrorOutput();

        return (strpos($error, 'GIT_DISCOVERY_ACROSS_FILESYSTEM') === 0);
    }

    /**
     * @return string|false
     */
    public function getCurrentBranch()
    {
        $command = 'git rev-parse --abbrev-ref HEAD';
        $process = new Process($command);

        if (!$process->run()) {
            return trim($process->getOutput());
        }

        return false;
    }

    /**
     * @return string|bool
     */
    public function getTopLevelFolder()
    {
        $command = 'git rev-parse --show-toplevel';
        $process = new Process($command);

        if (!$process->run()) {
            return trim($process->getOutput());
        }

        return false;
    }

    /**
     * @return array|bool
     */
    public function getLastCommit()
    {
        $command = 'git log -1 --pretty=%H,%ae,%at,%B';
        $process = new Process($command);

        if (!$process->run()) {
            $result = trim($process->getOutput());
            list($id, $email, $date, $message) = explode(',', $result, 4);
            $date = new \DateTime('@'.$date);

            return [
              'id' => $id,
              'email' => $email,
              'date' => $date,
              'message' => $message,
            ];
        }

        return false;
    }
}

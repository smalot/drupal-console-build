<?php


namespace Smalot\Drupal\Console\Build;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Class Build
 * @package Smalot\Drupal\Console\Build
 */
class Build
{
    const FILE_NAME = '.build.yml';

    const STAGE_ALL = 'ALL';

    /**
     * @var array
     */
    protected $stages;

    /**
     * @var array
     */
    protected $commands;

    /**
     * Build constructor.
     */
    public function __construct()
    {
        $this->stages = [];
        $this->commands = [];
    }

    /**
     * @param string $folder
     * @param string $filename
     */
    public function load($folder, $filename = self::FILE_NAME)
    {
        $this->stages = [];
        $this->commands = [];

        if (file_exists($folder.DIRECTORY_SEPARATOR.$filename)) {
            $content = file_get_contents($folder.DIRECTORY_SEPARATOR.$filename);
            $builds = Yaml::parse($content, true);

            // Extract stages entry from commands.
            if (array_key_exists('stages', $builds)) {
                $this->stages = $builds['stages'];
                unset($builds['stages']);
            }

            foreach ($builds as $code => $build) {
                $this->commands[$code] = new Command($code, $build);
            }
        }
    }

    /**
     * @return array
     */
    public function getStages()
    {
        return $this->stages;
    }

    /**
     * @param array $commands
     * @param string $branch
     * @param string $stage
     * @param string $folder
     * @param OutputInterface $output
     * @return bool
     */
    public function run($commands, $branch, $stage = self::STAGE_ALL, $folder, OutputInterface $output)
    {
        /** @var Command $command */
        foreach ($this->getCommands($branch, $stage) as $code => $command) {
            if (empty($commands) || in_array($code, $commands, true)) {
                if (!$command->run($folder, $output)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @param string $branch
     * @param string $stage
     * @return array
     */
    public function getCommands($branch = null, $stage = self::STAGE_ALL)
    {
        $commands = [];

        /** @var Command $command */
        foreach ($this->commands as $code => $command) {
            if ($command->matchBranch($branch) && $command->matchStage($stage)) {
                $commands[$code] = $command;
            }
        }

        return $commands;
    }
}

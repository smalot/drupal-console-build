<?php

namespace Smalot\Drupal\Console\Build\Command;

use Drupal\Console\Core\Command\Shared\CommandTrait;
use Smalot\Drupal\Console\Build\Build;
use Smalot\Drupal\Console\Build\Git;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class RunCommand
 * @package Smalot\Drupal\Console\Build\Command
 */
class RunCommand extends Command
{
    use CommandTrait;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
          ->setName('build:run')
          ->setDescription($this->trans('commands.build.run.description'))
          ->addArgument(
            'commands',
            InputArgument::IS_ARRAY,
            $this->trans('commands.build.run.argument.commands'),
            []
          )
          ->addOption(
            'stage',
            null,
            InputOption::VALUE_REQUIRED,
            $this->trans('commands.build.run.option.stage'),
            Build::STAGE_ALL
          );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $git = new Git();

        if (!$git->isEnabled()) {
            $output->writeln('<error>GIT repository not detected</error>');
            exit(1);
        }

        $folder = $git->getTopLevelFolder();
        $branch = $git->getCurrentBranch();

        $output->writeln('<comment>GIT repository detected</comment>');
        $output->writeln('<info>Top level</info>: '.$folder);
        $output->writeln('<info>Branch</info>: '.$branch);
        $output->writeln('<info>Last commit:</info>');

        $commit = $git->getLastCommit();
        foreach ($commit as $label => $value) {
            $output->writeln('  <info>'.$label.'</info>: '.$value);
        }

        $build = new Build();
        $build->load($folder);

        $stage = $input->getOption('stage');
        $commands = $input->getArgument('commands');
        $status = $build->run($commands, $branch, $stage, $folder, $output);

        if ($status) {
            $output->writeln('');
            $output->writeln('<comment>Successfully executed.</comment>');
        } else {
            $output->writeln('');
            $output->writeln('<error>Your build has been stopped.</error>');
        }

    }

}

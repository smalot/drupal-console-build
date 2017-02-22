<?php

namespace Smalot\Drupal\Console\Build\Command;

use Drupal\Console\Core\Command\Shared\CommandTrait;
use Smalot\Drupal\Console\Build\Git;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ListCommand
 * @package Smalot\Drupal\Console\Build\Command
 */
class ListCommand extends Command
{
    use CommandTrait;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
          ->setName('build:list')
          ->setDescription($this->trans('commands.build.list.description'));
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

        $output->writeln('<comment>GIT repository detected</comment>');
        $output->writeln('<info>top level</info>: '.$git->getTopLevelFolder());
        $output->writeln('<info>branch</info>: '.$git->getCurrentBranch());
        $output->writeln('<info>last commit</info>: '.json_encode($git->getLastCommit()));
    }

}

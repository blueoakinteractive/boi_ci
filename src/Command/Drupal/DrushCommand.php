<?php

namespace BOI_CI\Command\Drupal;

use BOI_CI\Command\BaseCommand;
use BOI_CI\Service\Drush as DrushService;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DrushCommand extends BaseCommand
{
  protected function configure()
  {
    $this
      ->setName('drush:command')
      ->setDescription('Executes an arbitrary drush command')
      ->addArgument('drush_command', InputArgument::REQUIRED, 'A valid drush command');
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $command = $input->getArgument('drush_command');
    $output->writeln("Running drush $command");
    $drush = new DrushService($this->build_root);
    $run = $drush->drush($command);
    $output->writeln($run);
  }
}

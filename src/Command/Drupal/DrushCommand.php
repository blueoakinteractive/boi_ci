<?php

namespace BOI_CI\Command\Drupal;

use BOI_CI\Command\BaseCommand;
use BOI_CI\Service\Drush as DrushService;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DrushCommand extends BaseCommand
{
  protected function configure()
  {
    $this
      ->setName('drush:command')
      ->setDescription('Executes an arbitrary drush command')
      ->addArgument('drush_command', InputArgument::REQUIRED, 'A valid drush command')
      ->addOption('environment',  null, InputOption::VALUE_OPTIONAL, 'Execute against a particular environment.');
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $command = $input->getArgument('drush_command');
    $output->writeln("Running drush $command");
    $drush = new DrushService($this->build_root);

    // Set the drush scope if an environment was defined.
    $environment = $input->getOption('environment');
    if (!empty($this->config['environments'][$environment]['drush']['alias'])) {
      $alias = $this->config['environments'][$environment]['drush']['alias'];
      if (strpos($alias, '@') !== 0) {
        $alias = '@' . $alias;
      }
      // Set the drush alias for remote database.
      $drush->setAlias($alias);
    }

    $run = $drush->drush($command);
    $output->writeln($run);
  }
}

<?php

namespace BOI_CI\Command\Drupal;

use BOI_CI\Command\BaseCommand;
use BOI_CI\Service\Drush as DrushService;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DrushUpdateDb extends BaseCommand
{
  protected function configure()
  {
    $this
      ->setName('drush:updatedb')
      ->setDescription('Runs drush updatedb on a particular environment via alias')
      ->addArgument('environment', InputArgument::REQUIRED, 'The environment to run updatedb on.');
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $environment = $input->getArgument('environment');
    if (empty($this->config['environments'][$environment]['drush']['alias'])) {
      throw new \Exception('The specified environment of $environment does not have a drush alias property in your config.');
    }

    $alias = $this->config['environments'][$environment]['drush']['alias'];
    if (strpos($alias, '@') !== 0) {
      $alias = '@' . $alias;
    }

    $output->writeln("Updating the database for $alias");
    $drush = new DrushService($this->build_root);

    // Set the drush alias for remote database.
    $drush->setAlias($alias);

    // Run updatedb on the alias.
    $run = $drush->drush("updatedb -y");
    $output->writeln($run);
  }
}

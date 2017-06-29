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
      ->addArgument('alias', InputArgument::REQUIRED, 'The remote drush alias');
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $alias = $input->getArgument('alias');
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

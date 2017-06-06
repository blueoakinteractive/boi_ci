<?php

namespace BOI_CI\Command\Drupal;

use BOI_CI\Command\BaseCommand;
use BOI_CI\Service\Drush as DrushService;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DrushSyncDb extends BaseCommand
{
  protected function configure()
  {
    $this
      ->setName('drush:sync-db')
      ->setDescription('Synchronizes a db from remote to local by remote alias')
      ->addArgument('alias', InputArgument::REQUIRED, 'The remote drush alias');
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $alias = $input->getArgument('alias');
    if (strpos($alias, '@') !== 0) {
      $alias = '@' . $alias;
    }

    // Instantiate the drush service helper.
    $drush = new DrushService($this->build_root);

    // Set the drush alias for remote database.
    $drush->setAlias($alias);

    // Make sure we can locate the drupal version.
    $status = json_decode($drush->drush('status --format=json'));
    if (!is_array($status)) {
      throw new \Exception('Unable to locate drupal root of the remote site. Please check your alias.');
    }

    // Execute the method to synchronize from the remote
    // database to the local one.
    $drush->syncDatabase();
  }
}

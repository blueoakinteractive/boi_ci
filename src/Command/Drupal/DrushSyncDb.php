<?php

namespace BOI_CI\Command\Drupal;

use Behat\Mink\Exception\Exception;
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

    $drush = new DrushService($this->build_root);
    $drush->setAlias($alias);
    $status = json_decode($drush->drush('status --format=json'));

    if (empty($status->{'drupal-version'})) {
      throw new \Exception('Unable to locate drupal root of the remote site. Please check your alias.');
    }

    $drush->syncDatabase();
  }
}

<?php

namespace BOI_CI\Command\Drupal;

use BOI_CI\Command\BaseCommand;
use BOI_CI\Service\Drush as DrushService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DrushRunServer extends BaseCommand
{
  protected function configure()
  {
    $this
      ->setName('drush:run-server')
      ->setDescription('Use drush to run PHP server cf');
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $drush = new DrushService($this->build_root);
    $drush->setDir($this->build_root);
    $drush->runServer('http://localhost:8080');
  }
}

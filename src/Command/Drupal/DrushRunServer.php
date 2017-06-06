<?php

namespace BOI_CI\Command\Drupal;

use BOI_CI\Command\BaseCommand;
use BOI_CI\Service\Drush as DrushService;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DrushRunServer extends BaseCommand
{
  protected function configure()
  {
    $this
      ->setName('drush:run-server')
      ->setDescription('Use drush to run PHP server cf')
      ->addArgument('url', InputArgument::REQUIRED, 'The full url and port of the server (http://localhost:8080)');
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $url = $input->getArgument('url');
    $drush = new DrushService($this->build_root);
    $drush->setDir($this->build_root);
    $drush->runServer($url);
  }
}

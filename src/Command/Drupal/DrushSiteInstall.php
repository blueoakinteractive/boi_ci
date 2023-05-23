<?php

namespace BOI_CI\Command\Drupal;

use BOI_CI\Command\BaseCommand;
use BOI_CI\Service\Drush as DrushService;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DrushSiteInstall extends BaseCommand
{
  protected function configure()
  {
    $this
      ->setName('drush:site-install')
      ->setDescription('Executes drush site-install for CI')
      ->addArgument('db_url', InputArgument::REQUIRED, 'A mysql database uri (mysqli://user:password@host:port/database)');
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $db_url = $input->getArgument('db_url');
    $drush = new DrushService($this->build_root);
    $drush->siteInstall($db_url);
    return 0;
  }
}

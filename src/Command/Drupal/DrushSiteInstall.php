<?php

namespace BOI_CI\Command\Drupal;

use BOI_CI\Command\BaseCommand;
use BOI_CI\Service\Drush as DrushService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DrushSiteInstall extends BaseCommand
{
  protected function configure()
  {
    $this
      ->setName('drush:site-install')
      ->setDescription('Executes drush site-install for CI');
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $db_url = 'mysqli://mysql:mysql@mysql.boi:32771/data';
    $drush = new DrushService($this->build_root);
    $drush->siteInstall($db_url);
  }
}

<?php

namespace BOI_CI\Command\Deploy;

use BOI_CI\Command\BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Git extends BaseCommand
{
  protected function configure()
  {
    $this
      ->setName('deploy:git')
      ->setDescription('Deploy project to a git artifact repo');
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $output->writeln('Deploying project to a git artifact repo');
  }
}
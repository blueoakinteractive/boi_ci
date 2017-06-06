<?php

namespace BOI_CI\Command\Build;

use BOI_CI\Command\BaseCommand;
use BOI_CI\Service\Shell;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Tasks extends BaseCommand
{

  protected function configure()
  {
    $this
      ->setName('build:tasks')
      ->setDescription('Runs per-environment tasks defined in config')
      ->addArgument('environment', InputArgument::REQUIRED, 'The environment to deploy to');
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    // Make sure the required environment exists in the configuration files.
    $environment = $input->getArgument('environment');
    if (empty($this->config['environments'][$environment])) {
      throw new \Exception("Environment '$environment' is not defined in your config file.");
    }

    // Make sure a git uri is specified for provided environment.
    if (empty($this->config['environments'][$environment]['tasks']) || !is_array($this->config['environments'][$environment]['tasks'])) {
      throw new \Exception("Please define the tasks for your '$environment' environment in your config file");
    }

    foreach ($this->config['environments'][$environment]['tasks'] as $key => $task) {
      if (!isset($task['directory']) || empty($task['command'])) {
        throw new \Exception("Please define a 'directory' and 'command' for your $key task");
      }
      $output->writeln("Executing $key task for $environment environment");
      $shell = new Shell();
      $shell->setDir($this->build_root . '/' . $task['directory']);
      $shell->setTimeout(null);
      $shell->execute($task['command']);
    }
    $output->writeln("Tasks for $environment environment complete");
  }
}

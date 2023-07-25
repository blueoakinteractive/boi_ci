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
      ->addArgument('environment', InputArgument::OPTIONAL, 'Run tasks for a specific environment. If not provided the tasks defined in build are ran.');
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $environment = $input->getArgument('environment');

    // If an environment was provided, make sure tasks
    // exist for that environment otherwise fail.
    if (isset($environment)) {
      if (empty($this->config['environments'][$environment]['tasks']) || !is_array($this->config['environments'][$environment]['tasks'])) {
        $output->writeln("<fg=magenta>Skipping tasks for '$environment' because there are none defined in your config file.</>");
        return 0;
      }

      $tasks = $this->config['environments'][$environment]['tasks'];
    }

    // If no environment was provided determine if there are
    // project-wide build tasks defined.
    else {
      if (empty($this->config['build']['tasks']) || !is_array($this->config['build']['tasks'])) {
        $output->writeln('Skipping project build tasks since there are none defined in your config.');
        return 0;
      }

      $tasks = $this->config['build']['tasks'];
      $environment = 'all';
    }

    // Run each defined task.
    foreach ($tasks as $key => $task) {
      // A working directory and command are required for each task.
      if (!isset($task['directory']) || empty($task['command'])) {
        throw new \Exception("Please define a 'directory' and 'command' for your $key task");
      }

      $output->writeln("Executing $key task for $environment environment(s)");
      $shell = new Shell();
      $shell->setDir($this->build_root . '/' . $task['directory']);
      $shell->setTimeout(null);
      $execute = $shell->execute(explode(' ', $task['command']));
      $output->writeln("$execute");
    }

    $output->writeln("Tasks for $environment environment(s) complete");
    return 0;
  }
}

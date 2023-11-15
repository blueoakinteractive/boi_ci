<?php

namespace BOI_CI\Command\Build;

use BOI_CI\Command\BaseCommand;
use BOI_CI\Service\Shell;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Tests extends BaseCommand
{

  protected function configure()
  {
    $this
      ->setName('build:tests')
      ->setDescription('Runs per-environment tests defined in config')
      ->addArgument('environment', InputArgument::OPTIONAL, 'Run tests for a specific environment. If not provided the tests defined in build are ran.');
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $environment = $input->getArgument('environment');

    // If an environment was provided, make sure tests
    // exist for that environment otherwise fail.
    if (isset($environment)) {
      if (empty($this->config['environments'][$environment]['tests']) || !is_array($this->config['environments'][$environment]['tests'])) {
        $output->writeln("<fg=magenta>Skipping tests for '$environment' because there are none defined in your config file.</>");
        return 0;
      }

      $tests = $this->config['environments'][$environment]['tests'];
    }

    // If no environment was provided determine if there are
    // project-wide build tests defined.
    else {
      if (empty($this->config['build']['tests']) || !is_array($this->config['build']['tests'])) {
        $output->writeln('Skipping project build tests since there are none defined in your config.');
        return 0;
      }

      $tests = $this->config['build']['tests'];
      $environment = 'all';
    }

    // Run each defined tests.
    foreach ($tests as $key => $test) {
      // A working directory and command are required for each tests.
      if (!isset($test['directory']) || empty($test['command'])) {
        throw new \Exception("Please define a 'directory' and 'command' for your $key tests");
      }

      $output->writeln("Executing $key tests for $environment environment(s)");
      $shell = new Shell();
      $shell->setDir($this->build_root . '/' . $test['directory']);
      $shell->setTimeout(null);
      $execute = $shell->execute(explode(' ', $test['command']));
      $output->writeln("$execute");
    }

    $output->writeln("Tests for $environment environment(s) complete");
    return 0;
  }
}

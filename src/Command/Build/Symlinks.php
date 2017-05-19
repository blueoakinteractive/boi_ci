<?php

namespace BOI_CI\Command\Build;

use BOI_CI\Command\BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class Symlinks extends BaseCommand
{
  private $dir;

  /**
   * SevenLocal constructor.
   * @param null|string $name
   */
  public function __construct($name = null)
  {
    parent::__construct($name);
    $this->dir = getcwd();
  }

  protected function configure()
  {
    $this
      ->setName('build:symlinks')
      ->setDescription('Sets up project symlinks');
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $output->writeln('Setting up project symlinks');
    $fs = new Filesystem();

    if (!empty($this->config['symlinks'])) {

      $properties = ['source', 'destination'];

      foreach ($this->config['symlinks'] as $key => $symlink) {

        // Validate all required properties are defined.
        if (array_diff($properties, array_keys($symlink))) {
          throw new \Exception('You must define source and destination for each symlink on ' . $key);
        }

        // Validate the source file/directory exists.
        if (!$fs->exists($this->config['root'] . '/' . $symlink['source'])) {
          $output->writeln('Skipping ' . $key . ' symlink because the source does not exist');
          continue;
        }

        // Validate the destination root exits.
        $destination_info = pathinfo($symlink['destination']);
        if (!$fs->exists($destination_info['dirname'])) {
          throw new \Exception('Destination directory must already exist for "' . $key . '" symlink') ;
        }

        // Determine the source as a relative path
        // from the destination.
        $destination_parts = explode('/', $symlink['destination']);
        $source = str_repeat('../', count($destination_parts) - 1) . $symlink['source'];

        // Create the symlink.
        $fs->symlink($source, $symlink['destination']);
        $output->writeln('Symlink created for ' . $key);
      }
    }
    $output->writeln('Finished setting up project symlinks');
  }
}
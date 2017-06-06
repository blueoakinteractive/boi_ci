<?php

namespace BOI_CI\Command\Build;

use BOI_CI\Command\BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class Symlinks extends BaseCommand
{
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


        // Determine the destination path and if it's relative
        // or absolute.
        if (strpos($symlink['destination'], '~/') === 0 ) {
          // If the destination is a user path replace it with the
          // home directory (ie: /Users/user/).
          $destination = str_replace('~/', getenv('HOME') . '/', $symlink['destination']);
          $absolute = TRUE;
        } else if (strpos($symlink['destination'], '/') === 0) {
          // If the root of the destination references an absolute path.
          $destination = $symlink['destination'];
          $absolute = TRUE;
        } else {
          // Otherwise, the path is relative to the build root.
          $destination = $this->config['build']['root'] . '/' . $symlink['destination'];
          $absolute = FALSE;
        }

        // Parse the destination info.
        $destination_info = pathinfo($destination);

        // If the mkdir flag is set, create the destination dir.
        if (!empty($symlink['mkdir'])) {
          $fs->mkdir($destination_info['dirname']);
        }

        // Validate the destination path exits.
        if (!$fs->exists($destination_info['dirname'])) {
          throw new \Exception('Destination directory does not exist and cannot be created"' . $key . '" symlink') ;
        }

        if (!empty($absolute)) {
          // Build the absolute path for the source.
          $source = $this->config['root'] . '/' . $symlink['source'];
        } else {
          // Build the relative path for the source from the
          // project root.
          $destination_parts = explode('/', $destination);
          $source = str_repeat('../', count($destination_parts) - 1) . $symlink['source'];
        }

        // Create the symlink.
        $fs->symlink($source, $destination);
        $output->writeln('Symlink created for ' . $key);
      }
    }
    $output->writeln('Finished setting up project symlinks');
  }
}
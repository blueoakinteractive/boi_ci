<?php

namespace BOI_CI\Command\Build;

use BOI_CI\Command\BaseCommand;
use Symfony\Component\Console\Input\InputArgument;
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
      ->setDescription('Sets up project symlinks')
      ->addArgument('environment', InputArgument::OPTIONAL, 'Symlinks for a specific environment. If not provided the project global symlkins are created.');

  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {

    $environment = $input->getArgument('environment');

    // If an environment was provided, make sure symlinks
    // exist for that environment skip the command call.
    if (isset($environment)) {
      if (empty($this->config['environments'][$environment]['symlinks']) || !is_array($this->config['environments'][$environment]['symlinks'])) {
        $output->writeln("<fg=magenta>Skipping symlinks for '$environment' because there are none defined in your config file.</>");
        return;
      }

      $symlinks = $this->config['environments'][$environment]['symlinks'];
    }

    // If no environment was provided determine if there are
    // global symlinks defined.
    else if (!empty($this->config['symlinks']) && is_array($this->config['symlinks'])) {
      $symlinks = $this->config['symlinks'];
    }
    else {
      $output->writeln("<fg=magenta>Skipping symlinks for all environments because there are none defined in your config file.</>");
      return;
    }

    $output->writeln("<fg=magenta>Setting up the following project symlinks:</>");
    $fs = new Filesystem();

    if (!empty($symlinks)) {

      $properties = ['source', 'destination'];

      foreach ($symlinks as $key => $symlink) {

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

        // If the force flag is set, delete any existing files before
        // attempting to create a new symlink.
        if (!empty($symlink['force']) && $symlink['force'] == true) {
            $fs->remove($destination);
        }

        // Create the symlink.
        $fs->symlink($source, $destination);
        $output->writeln('Symlink created for ' . $key);
      }
    }
    $output->writeln('Finished setting up project symlinks');
  }
}

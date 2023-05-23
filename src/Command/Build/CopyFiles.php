<?php

namespace BOI_CI\Command\Build;

use BOI_CI\Command\BaseCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class CopyFiles extends BaseCommand
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
      ->setName('build:copy_files')
      ->setDescription('Copies files that cannot be symlinks')
      ->addArgument('environment', InputArgument::OPTIONAL, 'Coipied files for a specific environment. If not provided the project global files are created.');
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {

    $environment = $input->getArgument('environment');

    // If an environment was provided, make sure copied files
    // exist for that environment skip the command call.
    if (isset($environment)) {
      if (empty($this->config['environments'][$environment]['copy_files']) || !is_array($this->config['environments'][$environment]['copy_files'])) {
        $output->writeln("<fg=yellow>Skipping copied files for '$environment' because there are none defined in your config file.</>");
        return 0;
      }

      $copy_files = $this->config['environments'][$environment]['copy_files'];
    }

    // If no environment was provided determine if there are
    // global copied files defined.
    else if (!empty($this->config['copy_files']) && is_array($this->config['copy_files'])) {
      $copy_files = $this->config['copy_files'];
    }
    else {
      $output->writeln("<fg=yellow>Skipping copied files for all environments because there are none defined in your config file.</>");
      return 0;
    }

    $output->writeln("<fg=green>Setting up the following project copied files:</>");
    $fs = new Filesystem();

    if (!empty($copy_files)) {

      $properties = ['source', 'destination'];

      foreach ($copy_files as $key => $copy_file) {

        // Validate all required properties are defined.
        if (array_diff($properties, array_keys($copy_file))) {
          throw new \Exception('You must define source and destination for each file on ' . $key);
        }

        // Validate the source file/directory exists.
        if (!$fs->exists($this->config['root'] . '/' . $copy_file['source'])) {
          $output->writeln('Skipping ' . $key . ' file because the source does not exist');
          continue;
        }


        // Determine the destination path and if it's relative
        // or absolute.
        if (strpos($copy_file['destination'], '~/') === 0 ) {
          // If the destination is a user path replace it with the
          // home directory (ie: /Users/user/).
          $destination = str_replace('~/', getenv('HOME') . '/', $copy_file['destination']);
          $absolute = TRUE;
        } else if (strpos($copy_file['destination'], '/') === 0) {
          // If the root of the destination references an absolute path.
          $destination = $copy_file['destination'];
          $absolute = TRUE;
        } else if (!empty($copy_file['project_root'])) {
          $destination = $copy_file['destination'];
          $absolute = FALSE;
        } else {
          // Otherwise, the path is relative to the build root.
          $destination = $this->config['build']['root'] . '/' . $copy_file['destination'];
          $absolute = FALSE;
        }

        // Parse the destination info.
        $destination_info = pathinfo($destination);

        // If the mkdir flag is set, create the destination dir.
        if (!empty($copy_file['mkdir'])) {
          $fs->mkdir($destination_info['dirname']);
        }

        // Validate the destination path exits.
        if (!$fs->exists($destination_info['dirname'])) {
          throw new \Exception('Destination directory does not exist and cannot be created"' . $key . '" file') ;
        }

        if (!empty($absolute)) {
          // Build the absolute path for the source.
          $source = $this->config['root'] . '/' . $copy_file['source'];
        } else {
          $source = $copy_file['source'];
        }

        // If the force flag is set, delete any existing files before
        // attempting to create a new file.
        if (!empty($copy_file['force']) && $copy_file['force'] == true) {
          $fs->remove($destination);
        }

        // Mirror directories when the source is a directory.
        if (is_dir($source)) {
          $fs->mirror($source, $destination);
        }
        // Create file copy when the source is a file.
        else {
          $fs->copy($source, $destination);
        }

        $output->writeln('Copied file created for ' . $key);
      }
    }
    $output->writeln('Finished setting up copied files');
    return 0;
  }
}

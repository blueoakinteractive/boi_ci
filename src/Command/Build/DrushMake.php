<?php

namespace BOI_CI\Command\Build;

use BOI_CI\Command\BaseCommand;
use BOI_CI\Service\Drush as DrushCommand;
use BOI_CI\Service\Rsync;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class DrushMake extends BaseCommand
{
  public function __construct($name = null)
  {
    parent::__construct($name);
  }

  protected function configure()
  {
    $this
      ->setName('build:drush-make')
      ->setDescription('Builds a site from a drush make file.')
      ->addArgument('makefile', InputArgument::OPTIONAL, 'The location of the makefile')
      ->addOption('make-arguments', null, InputOption::VALUE_OPTIONAL);
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $makefile = $input->getArgument('makefile');

    // If a makefile is not provided, attempt to
    // locate one from a default list of file names.
    if (empty($makefile)) {
      foreach ($this->defaultMakeFiles() as $filename) {
        if (file_exists($this->dir . '/' . $filename)) {
          $makefile = $filename;
          break;
        }
      }
    }

    // Verify that the makefile location exists.
    if (!file_exists($this->dir . '/'. $makefile)) {
      throw new \Exception('Unable to load specified makefile. Please make sure file exists');
    }

    // Setup the drush service and execute drush make.
    $output->writeln("Building site from $this->dir/$makefile");
    $drush = new DrushCommand($this->dir);
    $path = $this->config['temp'] . '/' . uniqid();
    if ($drush->version() > 9) {
      mkdir($path, 0777, TRUE);
    }

    // Clear the drush cache to make sure package data is updated.
    $drush->drush('cc drush');

    $make_arguments = $input->getOption('make-arguments');

    // Run drush make to build the project.
    $drush->drushMake($this->dir . '/'. $makefile, $path, $make_arguments);

    // Initialize the rsync server to sync files from the
    // temporary build directory into the build root.
    $output->writeln("Copying build files into the build root of $this->build_root");
    $rsync = new Rsync();
    $rsync->setSource($path);
    $rsync->setDestination($this->build_root);
    $rsync->setFlags('vqr');
    $rsync->addOption('--delete');

    // Exclude project symlinks from the rsync command so they
    // are not deleted.
    if (!empty($this->config['symlinks'])) {
      foreach ($this->config['symlinks'] as $symlink) {
        if (!empty($symlink['destination'])) {
          $rsync->addExclude($symlink['destination']);
        }
      }
    }

    // Execute the rsync command.
    $rsync->sync();

    // Clean up the temporary directory.
    (new Filesystem)->remove($path);

    $output->writeln('Build complete');
    return 0;
  }

  /**
   * Returns an array of default make file names.
   */
  protected function defaultMakeFiles() {
    return [
      'project.make',
      'project.make.yml',
      'drush.make',
      'drush.make.yml',
    ];
  }
}

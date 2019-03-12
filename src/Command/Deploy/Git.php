<?php

namespace BOI_CI\Command\Deploy;

use BOI_CI\Command\BaseCommand;
use BOI_CI\Service\Git as GitCommand;
use BOI_CI\Service\Rsync as RsyncCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class Git extends BaseCommand
{
  protected function configure()
  {
    $this
      ->setName('deploy:git')
      ->setDescription('Deploy project to a git artifact repo')
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
    if (empty($this->config['environments'][$environment]['git']['uri'])) {
      throw new \Exception("Please specify a git uri for the '$environment' environment in your config file'");
    }

    $uri = $this->config['environments'][$environment]['git']['uri'];
    $branch = !empty($this->config['environments'][$environment]['git']['branch']) ? $this->config['environments'][$environment]['git']['branch'] : 'master';

    // Define a temporary path for writing to during deployment.
    // If an artifact root is specified, use that. Otherwise,
    // create a unique temporary directory.
    if (!empty($this->config['build']['artifact_root'])) {

      // Determine if the artifact root path is absolute.
      if (strpos($this->config['build']['artifact_root'], '/') === 0) {
        $path = putenv($this->config['build']['artifact_root'] . '/' . $environment);
      }
      // For relative paths, append it to the temp directory.
      else {
        $path = $this->config['temp'] . '/' . $this->config['build']['artifact_root'] . '/' . $environment;
      }

    }
    else {
      $path = $this->config['temp'] . '/' . uniqid();
    }

    // Bring the local git repository into scope.
    $git_local = new GitCommand($this->dir);

    // Make sure all changes to the local repo have been committed.
    if (!strstr($git_local->gitStatus(), 'nothing to commit')) {
      throw new \Exception('Unable to deploy on an unclean project. Make sure all local changes have been committed' . PHP_EOL . $git_local->gitStatus() . PHP_EOL . $git_local->gitDiff());
    }

    // Determine the last commit message to use as the deployment
    // commit message.
    $last_commit_message = addslashes(trim($git_local->lastCommitMessage()));

    $output->writeln("Deploying project to a git artifact repo's '$branch' branch'");
    $git_remote = new GitCommand($path);
    $git_remote->setTimeout(null);

    // If the remote repo has already been cloned and exists in
    // the cache we can save time by fetch/merge. Otherwise,
    // we need to clone the entire repo.
    if (file_exists($path .'/.git')) {
      $output->writeln("Cached artifact repo exists. Fetching repo from $uri");
      $git_remote->gitFetch('origin', $branch);
      $git_remote->gitMerge('origin', $branch);
    } else {
      // Clone the repo.
      $output->writeln("No cached artifact repo. Cloning fresh artifact repo from $uri");
      $git_remote->gitClone($uri, $branch);
    }

    // Set require configuration options to push commits.
    $git_email = !empty($this->config['environments'][$environment]['git']['user']['email']) ? $this->config['environments'][$environment]['git']['user']['email'] : 'boici@example.com';
    $git_name = !empty($this->config['environments'][$environment]['git']['user']['email']) ? $this->config['environments'][$environment]['git']['user']['email'] : 'boici@example.com';
    $git_remote->gitConfig('user.email', $git_email);
    $git_remote->gitConfig('user.name', $git_name);

    $output->writeln('Syncing changes to artifact repo');
    $rsync = new RsyncCommand();

    // Define Rsync exclusions that should not be deployed.
    $rsync->addExclude('.git');
    if (!empty($this->config['environments'][$environment]['exclude'])) {
      foreach ($this->config['environments'][$environment]['exclude'] as $exclusion) {
        $rsync->addExclude($exclusion);
      }
    }

    // Determine the flags to be used by rsync.
    $flags = 'tr';
    // Add the flag to handle symbolic links (retained or resolved).
    $flags .= empty($this->config['environments'][$environment]['keep_symlinks']) ? 'L' : 'l';


    $rsync->setFlags($flags);
    $rsync->addOption('--delete');
    $rsync->addOption('--perms');
    $rsync->addOption('--executability');

    // Set the default source to be the build root.
    $source = $this->build_root;

    // Allow configuration to override the artifact root to use by
    // setting build_root or source_root.
    if (!empty($this->config['environments'][$environment]['root'])) {
      switch ($this->config['environments'][$environment]['root']) {
        case 'build_root':
          $source = $this->build_root;
          break;
        case 'source_root':
          $source = $this->config['root'];
          break;
        default:
          throw new \Exception('The git root must either be "source" or "build" in your config.');
      }
    }

    $rsync->setSource($source);

    // Determine the path to sync the build into for deployment.
    $destination = $path;
    if (!empty($this->config['environments'][$environment]['git']['sub_dir'])) {
      $destination .= '/' . $this->config['environments'][$environment]['git']['sub_dir'];
    }

    $rsync->setDestination($destination);
    $rsync->sync();

    // Make sure code changes have occurred before attempting
    // to push CI commits to remote repository.
    if (strstr($git_remote->gitStatus(), 'nothing to commit')) {
      throw new \Exception('Deploy canceled, no changes to deploy.');
    }

    // Add all changed files to be committed.
    $output->writeln('Committing changes');
    $git_remote->gitAdd('.');

    // Create a commit message based on the last local commit.
    $git_remote->gitCommit("CI BOT Commit: $last_commit_message");

    // Push changes to the artifact repo.
    $output->writeln('Pushing changes to artifact repo');
    $git_remote->gitPush("origin", $branch);

    // Clean up the artifact repo if a temporary directory
    // was used.
    if (empty($this->config['build']['artifact_root'])) {
      (new Filesystem)->remove($path);
    }
    $output->writeln('Deploy complete');
  }
}

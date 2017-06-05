<?php

namespace BOI_CI\Command\Deploy;

use BOI_CI\Command\BaseCommand;
use BOI_CI\Service\Git as GitCommand;
use BOI_CI\Service\Rsync as RsyncCommand;
use Drupal\Driver\Exception\Exception;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
    $path = $this->config['temp'] . '/' . uniqid();

    // Bring the local git repository into scope.
    $git_local = new GitCommand($this->dir);
    // @todo: make sure local git does not have uncommitted changes.

    // Determine the last commit message to use as the deployment
    // commit message.
    $last_commit_message = trim($git_local->lastCommitMessage());

    $output->writeln("Deploying project to a git artifact repo's '$branch' branch'");
    $output->writeln("Cloning artifact repo from $uri");
    $git_remote = new GitCommand($path);
    $git_remote->gitClone($uri, $branch, $path);

    $output->writeln('Syncing changes to artifact repo');
    $rsync = new RsyncCommand();

    // Define Rsync exclusions that should not be deployed.
    $rsync->addExclude('.git');
    if (!empty($this->config['environments'][$environment]['exclude'])) {
      foreach ($this->config['environments'][$environment]['exclude'] as $exclusion) {
        $rsync->addExclude($exclusion);
      }
    }

    $rsync->setFlags('vrL');
    $rsync->setSource($this->build_root);
    $rsync->setDestination($path);
    $rsync->sync();

    $output->writeln('Committing changes');
    $git_remote->gitAdd('.');

    // commit
    $git_remote->gitCommit("BOI CI commit $last_commit_message");

    $output->writeln('Pushing changes to artfact repo');
    $git_remote->gitPush("origin", $branch);

    $output->writeln('Deploy complete');
  }
}

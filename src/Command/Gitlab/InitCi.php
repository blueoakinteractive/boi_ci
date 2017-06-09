<?php

namespace BOI_CI\Command\Gitlab;

use BOI_CI\Command\BaseCommand;
use BOI_CI\Service\Shell;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class InitCi extends BaseCommand
{
  protected function configure()
  {
    $this
      ->setName('gitlab:init-ci')
      ->setDescription('Initializes dependencies for CI');
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $this->sshAuth($output);
    $this->setPath();
    $this->setTimezone();
  }

  /**
   * Set up SSH authentication dependencies.
   * @param \Symfony\Component\Console\Output\OutputInterface $output
   */
  protected function sshAuth(OutputInterface $output) {
    // Set up SSH Authentication.
    $deploy_key = getenv("DEPLOY_KEY");
    if (!empty($deploy_key)) {
      $shell = new Shell();
      $ssh_agent = trim($shell->execute("which ssh-agent"));

      // Warn that ssh agent is not available.
      if (empty($ssh_agent)) {
        $output->writeln("<fg=magenta>Unable to locate ssh-agent command. Commands that require ssh authentication will not pass.</>");
      }
      else {
        $ssh_add = trim($shell->execute("which ssh-add"));
        $key_file = $this->config['temp'] .'/'.  uniqid() . '.pem';

        // Copy the DEPLOY_KEY to a file and execute ssh-add.
        $fs = new Filesystem();
        $fs->remove($key_file);
        $fs->touch($key_file);
        $fs->appendToFile($key_file, $deploy_key);
        $fs->chmod($key_file, 0400);
        $shell->execute("$ssh_add $key_file");
        $fs->remove($key_file);

        // Disable strict host key checking for deployments.
        $fs = new Filesystem();
        $fs->mkdir("~/.ssh/config");
        $fs->appendToFile("~/.ssh/config","\"Host *\n\tStrictHostKeyChecking no\n\n\"");
      }
    }
    else {
      // Warn that no deploy key was provided.
      $output->writeln("<fg=magenta>DEPLOY_KEY environment variable is not available. Commands that require ssh authentication will not pass.</>");
    }
  }

  /**
   * Update environment $PATH for composer bins.
   */
  protected function setPath() {
    $shell = new Shell();
    $path = '$PATH:$CI_PROJECT_DIR/bin:$CI_PROJECT_DIR/vendor/bin:$HOME/.composer/vendor/bin';

    // Allow additional paths to be added via config.
    if (!empty($this->config['ci']['path'])) {
      $path .= ":$this->config['ci']['path']";
    }
    $shell->execute("export PATH=$path");
  }

  /**
   * Set PHP's timezone in the ini file.
   */
  protected function setTimeZone() {
    $ini_path = php_ini_loaded_file();
    if (!empty($this->config['ci']['timezone'])) {
      $timezone = $this->config['ci']['timezone'];
    }
    else {
      $timezone = "America/New_York";
    }
    $shell = new Shell();
    $shell->execute("echo date.timezone=$timezone >> $ini_path");
  }
}

<?php

namespace BOI_CI\Service;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class Shell extends BaseService
{
  protected $dir = null;
  protected $env = null;
  protected $input = null;
  protected $timeout = 60;
  protected $options = array();

  public function __construct()
  {
    parent::__construct();
    $this->initEnv();
  }

  /**
   * Set the environment variables to use for all commands.
   */
  private function initEnv() {
    // Fetch the environment variables from $_ENV if they
    // exist. If not, use $_SERVER.
    $env = !empty($_ENV) ? (array) $_ENV : (array) $_SERVER;

    // Append ~/.composer/vendor/bin to the PATH for
    // global composer binaries.
    $env['PATH'] .= ':' . getenv('HOME') . '/.composer/vendor/bin';

    // If the site is running on GitLab CI, append possible
    // local vendor binaries.
    $gitlab_dir = getenv('CI_PROJECT_DIR');
    if (!empty($gitlab_dir)) {
      $env['PATH'] .= ':' . $gitlab_dir . '/vendor/bin';
      $env['PATH'] .= ':' . $gitlab_dir . '/bin';
    }

    // Update the environment variables for this class.
    $this->setEnv($env);
  }

  /**
   * Sets working dir for \Symfony\Component\Process\Process.
   * @param $dir
   */
  public function setDir($dir) {
    $this->dir = $dir;
  }

  /**
   * Sets environment variables for \Symfony\Component\Process\Process.
   * @param $env
   */
  public function setEnv($env) {
   $this->env = $env;
  }

  /**
   * Sets input for \Symfony\Component\Process\Process.
   * @param $input
   */
  public function setInput($input) {
    $this->input = $input;
  }

  /**
   * Sets timeout for \Symfony\Component\Process\Process.
   * @param $timeout
   */
  public function setTimeout($timeout) {
    $this->timeout = $timeout;
  }

  /**
   * Sets options for \Symfony\Component\Process\Process.
   * @param $options
   */
  public function setOptions($options)
  {
    $this->options = $options;
  }

  /**
   * Executes a command using \Symfony\Component\Process\Process.
   * @param $command
   *   The string command.
   * @return string
   *   The output from the command.
   */
  public function execute($command)
  {
    $process = new Process($command, $this->dir, $this->env, $this->input, $this->timeout, $this->options);
    $process->run();

    if (!$process->isSuccessful()) {
      throw new ProcessFailedException($process);
    }

    return $process->getOutput();
  }

  /**
   * Starts a background process with the provided command.
   * @param $command
   */
  public function background($command)
  {
    $this->setTimeout(null);
    $process = new Process($command, $this->dir, $this->env, $this->input, $this->timeout, $this->options);
    $process->start();
    $process->wait();
  }
}

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
  }

  public function setDir($dir) {
    $this->dir = $dir;
  }

  public function setEnv($env) {
   $this->env = $env;
  }

  public function setInput($input) {
    $this->input = $input;
  }

  public function setTimeout($timeout) {
    $this->timeout = $timeout;
  }

  public function setOptions($options) {
    $this->options = $options;
  }

  public function execute($command)
  {
    $process = new Process($command, $this->dir, $this->env, $this->input, $this->timeout, $this->options);
    $process->run();

    if (!$process->isSuccessful()) {
      throw new ProcessFailedException($process);
    }

    return $process->getOutput();
  }

  public function background($command) {
    $process = new Process($command, $this->dir, $this->env, $this->input, $this->timeout, $this->options);
    $process->start();
    $process->wait();
  }
}

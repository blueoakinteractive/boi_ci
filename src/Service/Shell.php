<?php

namespace BOI_CI\Service;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class Shell extends BaseService {

  public function __construct()
  {
    parent::__construct();
  }

  public function execute($command)
  {
    $process = new Process($command);
    $process->run();

    if (!$process->isSuccessful()) {
      throw new ProcessFailedException($process);
    }

    return $process->getOutput();
  }
}

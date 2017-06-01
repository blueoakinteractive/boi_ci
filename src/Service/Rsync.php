<?php

namespace BOI_CI\Service;

class Rsync extends Shell {
  private $rsync;

  public function __construct() {
    parent::__construct();
    $this->rsync = trim($this->execute("which rsync"));

    // Make sure git is installed and available.
    if (empty($this->rsync)) {
      throw new \Exception('Rsync not found');
    }

  }

  public function sync($source, $destination, $flags = '-vr', $options = '') {
    return $this->execute("$this->rsync $flags $source/ $destination/ $options");
  }
}
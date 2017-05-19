<?php

namespace BOI_CI\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

class BaseCommand extends Command
{
  protected $config;

  public function __construct($name = null) {
    parent::__construct($name);
    $this->setConfig();
  }

  private function setConfig()
  {
    $fs = new Filesystem();
    // @todo: fix path.
    $root = getcwd();
    if ($fs->exists($root . '/.boi_ci.yml')) {
      $this->config = Yaml::parse(file_get_contents($root . '/.boi_ci.yml'));
      $this->config['root'] = $root;
    }
  }
}

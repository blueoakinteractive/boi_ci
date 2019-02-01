<?php

namespace BOI_CI\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

class BaseCommand extends Command
{
  protected $config;
  protected $dir;
  protected $build_root;

  public function __construct($name = null) {
    parent::__construct($name);
    $this->dir = getcwd();
    $this->setConfig();

    // Verify temporary directory is defined and writable.
    if (empty($this->config['temp']) || (!file_exists($this->config['temp']) && !mkdir($this->config['temp'], 0777, true))) {
      throw new \Exception("The temp directory is not defined or does not exist in your config file.");
    }

    // Verify build root is defined and writable.
    if (empty($this->config['build']['root']) || (!file_exists($this->config['build']['root']) && !mkdir($this->config['build']['root']) )) {
      throw new \Exception("The build root directory is not defined or does not exist in your config file.");
    }

    $this->build_root = $this->dir . '/' . $this->config['build']['root'];
    if (!file_exists($this->build_root)) {
      throw new \Exception("The build root specified in your config is not writable");
    }
  }

  /**
   * Sets the configuration from a .boi_ci.yml file.
   */
  protected function setConfig()  {
    $fs = new Filesystem();
    // @todo: fix path.
    $root = getcwd();
    if ($fs->exists($root . '/.boi_ci.yml')) {
      $this->config = Yaml::parse(file_get_contents($root . '/.boi_ci.yml'));
      $this->config['root'] = $root;
    }
  }

  /**
   * Return the loaded config array.
   *
   * @return mixed
   */
  public function getConfig() {
    return $this->config;
  }

}

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
    $this->setDir();
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
    $parts = explode('/', getcwd());
    $path = '';
    // Loop over all the path parts and find the first .boi_ci.yml file.
    foreach ($parts as $part) {
      $path .= $part . '/';
      if ($fs->exists($path . '.boi_ci.yml')) {
        $this->config = Yaml::parse(file_get_contents($path . '.boi_ci.yml'));
        $this->config['root'] = $path;
        return;
      }
    }
    throw new \Exception('BOI CI configuration not found. Please ensure a .boi_ci.yml file exists in your project somewhere.');
  }


  /**
   * Return the loaded config array.
   *
   * @return mixed
   */
  public function getConfig() {
    return $this->config;
  }

  /**
   * Sets the working directory for all commands.
   */
  protected function setDir() {
    $this->dir = getcwd();
  }

  /**
   * Gets the working directory for all commands.
   * @return mixed
   */
  public function getDir() {
    return $this->dir;
  }

}

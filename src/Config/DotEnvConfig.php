<?php
namespace BOI_CLI\Config;

use BOI_CI\Config\Config;

/**
 * Class DotEnvConfig
 */
class DotEnvConfig extends Config
{
  /**
   * @var string
   */
  protected $file;
  /**
   * DotEnvConfig constructor.
   *
   * @param $dir
   */
  public function __construct($dir)
  {
    parent::__construct();
    $file = $dir . '/.env';

    // Load environment variables from __DIR__/.env
    if (file_exists($file)) {
      // Remove comments (which start with '#')
      $lines = file($file);
      $lines = array_filter($lines, function ($line) {
        return strpos(trim($line), '#') !== 0;
      });
      $info = parse_ini_string(implode($lines, "\n"));
      $this->fromArray($info);
    }
  }
}
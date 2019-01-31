<?php

namespace BOI_CI\Service;

class Rsync extends Shell
{
  protected $rsync;
  protected $rsync_options;
  protected $rsync_flags = '-vr';
  protected $source;
  protected $destination;

  public function __construct()
  {
    parent::__construct();
    $this->rsync = trim($this->execute("which rsync"));

    // Make sure rsync is installed and available.
    if (empty($this->rsync)) {
      throw new \Exception('Rsync not found');
    }

    // Disable the timeout for rsync commands.
    $this->setTimeout(null);
  }

  /**
   * Executes the rsync command based on class properties.
   * @return string
   * @throws \Exception
   */
  public function sync()
  {
    $rsync_options = !empty($this->rsync_options) ? implode(" ", $this->rsync_options) : "";

    if (empty($this->source)) {
      throw new \Exception('No source specified for rsync');
    }

    if (empty($this->destination)) {
      throw new \Exception('No destination specified for rsync');
    }

    return $this->execute("$this->rsync $this->rsync_flags $this->source $this->destination $rsync_options");
  }

  /**
   * The source to copy from.
   * @param $source
   */
  public function setSource($source)
  {
    $this->source = $source . '/';
  }

  /**
   * The destination to copy to.
   * @param $destination
   */
  public function setDestination($destination)
  {
    $this->destination = $destination . '/';
  }

  /**
   * Paths to be excluded from the rsync command.
   * @param $path
   */
  public function addExclude($path)
  {
    $this->rsync_options[] = "--exclude=$path";
  }

  /**
   * Add option to the rsync command.
   * @param $option
   */
  public function addOption($option){
    $this->rsync_options[] = $option;
  }

  /**
   * Set rsync_flags on the rsync command.
   * @param $rsync_flags
   */
  public function setFlags($rsync_flags)
  {
    $this->rsync_flags = '-' . $rsync_flags;
  }
}

<?php

namespace BOI_CI\Config;

class Config {
  public function __construct()
  {

  }

  /**
   * Sets a config value.
   *
   * @param string $key
   * @param mixed $value
   *
   * @return $this
   */
  public function set($key, $value)
  {
    $this->$key = $value;
    return $this;
  }

  /**
   * Set add all the values in the array to this Config object.
  }
   * @param array $array
   */
  public function fromArray(array $array = [])
  {
    foreach ($array as $key => $val) {
      $this->set($key, $val);
    }
  }
}

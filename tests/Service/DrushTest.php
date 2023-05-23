<?php

namespace BOI_CI\Service;

use PHPUnit\Framework\TestCase;

class DrushTest extends TestCase
{
  public function testDrush()
  {
    $drush = new Drush(\getcwd());
    $output = $drush->drush('status');
    $this->assertStringContainsStringIgnoringCase('Drush version', $output);
    return $drush;
  }
}

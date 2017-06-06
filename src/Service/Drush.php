<?php

namespace BOI_CI\Service;

use Behat\Mink\Exception\Exception;

class Drush extends Shell {
  protected $drush;
  protected $drush_dir;
  protected $alias;

  public function __construct($drush_dir) {
    parent::__construct();
    $this->drush = trim($this->execute("which drush"));

    // Make sure drush is installed and available.
    if (empty($this->drush)) {
      throw new \Exception('Drush not found');
    }

    $this->drush_dir = $drush_dir;
  }

  public function setAlias($alias) {
    $this->alias = $alias;
  }

  protected function getScope() {
    // If an alias is specified, use it as the scope.
    if (!empty($this->alias)) {
      return $this->alias;
    }
    // If a build dir is specified, use it as the scope.
    if (!empty($this->drush_dir)) {
      return "-r $this->drush_dir";
    }
  }

  public function drush($command) {
    $scope = $this->getScope();
    return $this->execute("$this->drush $scope $command");
  }

  public function siteInstall($db_url, $profile = "standard", $site_name="boi_ci", $account_mail = "boi_ci@example.com", $account_name = "boi_ci", $account_pass = "boi_ci", $options = null) {
    $scope = $this->getScope();
    $this->execute("$this->drush $scope site-install -y $profile $options --db-url=\"$db_url\" --site-name=\"$site_name\"  --account-mail=\"$account_mail\" --account-name=\"$account_name\" --account-pass=\"$account_pass\"");
  }

  public function runServer($uri, $options = '--server=builtin --strict=0') {
    $scope = $this->getScope();
    $this->background("$this->drush $scope runserver --uri=$uri $options");
  }

  public function syncDatabase() {
    if (empty($this->alias)) {
      throw new \Exception('A remote drush alias is required to sync database');
    }

    $this->setTimeout(null);
    $this->execute("$this->drush $this->alias sql-dump | $this->drush -r $this->drush_dir sqlc");
  }
}
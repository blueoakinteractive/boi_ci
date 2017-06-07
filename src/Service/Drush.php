<?php

namespace BOI_CI\Service;

class Drush extends Shell
{
  protected $drush;
  protected $drush_dir;
  protected $alias;

  public function __construct($drush_dir)
  {
    parent::__construct();
    $this->drush = trim($this->execute("which drush"));

    // Make sure drush is installed and available.
    if (empty($this->drush)) {
      throw new \Exception('Drush is not installed or cannot be found');
    }

    $this->drush_dir = $drush_dir;
  }

  /**
   * Sets a drush alias for executed commands.
   * @param $alias
   */
  public function setAlias($alias)
  {
    $this->alias = $alias;
  }

  /**
   * Determines appropriate scope for drush commands.
   * @return string
   */
  protected function getScope()
  {
    // If an alias is specified, use it as the scope.
    if (!empty($this->alias)) {
      return $this->alias;
    }
    // If a build dir is specified, use it as the scope.
    if (!empty($this->drush_dir)) {
      return "-r $this->drush_dir";
    }
  }

  /**
   * Executes drush $command.
   * @param $command
   * @return string
   */
  public function drush($command)
  {
    $scope = $this->getScope();
    return $this->execute("$this->drush $scope $command");
  }

  /**
   * Executes drush site-install with options.
   * @param $db_url
   * @param string $profile
   * @param string $site_name
   * @param string $account_mail
   * @param string $account_name
   * @param string $account_pass
   * @param null $options
   */
  public function siteInstall($db_url, $profile = "standard", $site_name="boi_ci", $account_mail = "boi_ci@example.com", $account_name = "boi_ci", $account_pass = "boi_ci", $options = null)
  {
    $scope = $this->getScope();
    $this->execute("$this->drush $scope site-install $profile install_configure_form.update_status_module='array(FALSE,FALSE)' -y $options --db-url=\"$db_url\" --site-name=\"$site_name\"  --account-mail=\"$account_mail\" --account-name=\"$account_name\" --account-pass=\"$account_pass\"");
  }

  /**
   * Executes drush run-server with options/
   * @param $uri
   * @param string $options
   */
  public function runServer($uri, $options = '--server=builtin --strict=0')
  {
    $scope = $this->getScope();
    $this->background("$this->drush $scope runserver --uri=$uri $options");
  }

  /**
   * Exports the database from an alias and imports it locally.
   * @throws \Exception
   */
  public function syncDatabase()
  {
    if (empty($this->alias)) {
      throw new \Exception('A remote drush alias is required to sync database');
    }

    $this->setTimeout(null);
    $this->setDir(getenv("HOME"));
    $this->execute("$this->drush $this->alias status");
    $this->execute("$this->drush $this->alias sql-dump --gzip | gzip -cd | $this->drush -r $this->drush_dir sql-cli");
  }
}

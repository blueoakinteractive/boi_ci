<?php
namespace BOI_CI;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use BOI_CI\Command;

/**
 * Class BOI_CI
 * @package BOI_CI
 */
class BOI_CI {
  /**
   * @var \Symfony\Component\Console\Application
   */
  private $app;

  /**
   * BOI_CI constructor.
   * @param \Symfony\Component\Console\Input\InputInterface|NULL $input
   * @param \Symfony\Component\Console\Output\OutputInterface|NULL $output
   */
  public function __construct(InputInterface $input = null, OutputInterface $output = null)
  {
    $this->app = new Application();
    $this->app->addCommands($this->getCommands());
  }

  /**
   * Run the Symfony Application.
   *
   * @param \Symfony\Component\Console\Input\InputInterface $input
   * @param \Symfony\Component\Console\Output\OutputInterface $output
   * @return int
   */
  public function run(InputInterface $input, OutputInterface $output)
  {
    return $this->app->run($input, $output);
  }

  /**
   * Return the available CLI commands
   *
   * @return array
   */
  private function getCommands()
  {
    return [
      new Command\Build\DrushMake(),
      new Command\Build\Symlinks(),
      new Command\Build\Tasks(),
      new Command\Deploy\Git(),
      new Command\Drupal\DrushCommand(),
      new Command\Drupal\DrushRunServer(),
      new Command\Drupal\DrushSiteInstall(),
      new Command\Drupal\DrushSyncDb()
    ];
  }
}

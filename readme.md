# BOI CI
The BOI CI was developed by [Blue Oak Interactive](https://www.blueoakinteractive.com) to streamline the build and deployment processes of php based web applications across a variety of hosting providers with a focus on Drupal.

**Reasons to use this project:**

1) You host Drupal websites across multiple providers and want a unified system to manage deployments.
2) You want to perform tests via CI on your codebase prior to deploying to production.
3) You want to simplify your build process.

## Installation Requirements:
- [Composer](https://getcomposer.org/) - PHP Dependency management 
- [Symfony ~2.8](https://symfony.com/) - Symfony 2 is required  as the Drupal Console for Drupal 8 currently runs on Symfony
 - [Drush](http://www.drush.org/en/master/) - Drupal Shell
- [Git](https://git-scm.com/) - For version control
- [GitLab](https://about.gitlab.com/) - Git repository hosting and CI

## Installation Instructions
To simplify the configuration process we have created example projects that you can use as a starting point for configuring boi_ci for your Drupal project.

**We plan to have boilerplate configurations for the following providers:**
- [Platform.sh](https://platform.sh/)
- [Pantheon.io](https://pantheon.io/)
- [Acquia.com](https://www.acquia.com/)
- As well as other non-Drupal specific providers 

**See:** [blueoakinterative/boi_ci_examples](https://gitlab.com/blueoakinteractive/boi_ci_examples)


## Manual Installation Instructions:
###Create the following folder structure:

- ./local/
- ./local/files/
- ./local/settings.local.php
- ./.boi_ci.yml
- ./.gitignore
- ./gitlab-ci.yml
- ./example.aliases.drushrc.php
- ./composer.json
- ./project.make
- ./settings.php

###composer.json:
```json
{
  "name": "blueoakinteractive/example",
  "description": "example.com Drupal 7",
  "repositories": [
    {
      "type": "vcs",
      "url": "https://gitlab.com/blueoakinteractive/boi_ci.git"
    }
  ],
  "require": {
    "blueoakinteractive/boi_ci": "dev-master",
    "drush/drush": "8.*"
  },
  "config": {
    "bin-dir": "bin/",
    "vendor-dir": "vendor"
  },
  "minimum-stability": "dev",
  "prefer-stable": true,
  "scripts": {
    "post-update-cmd": [
      "bin/boi_ci build:drush-make",
      "bin/boi_ci build:symlinks",
      "bin/boi_ci build:tasks"
    ],
    "post-install-cmd": [
      "bin/boi_ci build:drush-make",
      "bin/boi_ci build:symlinks",
      "bin/boi_ci build:tasks"
    ]
  }
}
```

###Create your .boi_ci.yml file:
```yaml
# Required build root directory. This should match
# the folder that composer.json is using in installer
# paths.
build:
  root: www

# Required temporary directory for build tasks.
temp: /tmp

# Symbolic links to create for the project
# Do not include the build root defined
# above in the destination.
symlinks:
  robots.txt:
    source: robots.txt
    destination: robots.txt
    force: true
  themes:
    source: themes
    destination: sites/default/themes
  modules:
    source: modules
    destination: sites/default/modules
  libraries:
    source: libraries
    destination: sites/default/libraries
  settings.local:
    source: local/settings.local.php
    destination: sites/default/settings.local.php
  settings.php:
    source: settings.php
    destination: sites/default/settings.php
  files:
    source: local/files
    destination: sites/default/files
  alias:
    source: "example.aliases.drushrc.php"
    destination: "~/.drush/example.aliases.drushrc.php"
    mkdir: "true"
    force: "true"

# Environments for deployments.
environments:
  development:
    git:
      uri: example@git.example.com:example.git
      branch: development
    # Specify which root to use for the artifact repo.
    # - build_root (default) : Use the build root as the artifact root.
    # - source_root : Use the source root as the artifact root.
    root: source_root
    drush:
      alias: example.development
    exclude:
     # Global CI excludes.
     - /local
     - /composer.*
     - /.gitignore
     - /project.make
  master:
    git:
      uri: example@git.example.com:example.git
      branch: master
    # Specify which root to use for the artifact repo.
    # - build_root (default) : Use the build root as the artifact root.
    # - source_root : Use the source root as the artifact root.
    root: source_root
    drush:
      alias: example.master
    exclude:
     # Global CI excludes.
     - /local
     - /composer.*
     - /.gitignore
     - /project.make

```

###Create your .gitlab-ci.yml file:
```yaml
image: blueoakinteractive/php-fpm:7.0.11-ci

before_script:
  - composer install --prefer-dist --no-interaction --no-progress
  - bin/boi_ci gitlab:init-ci

stages:
  - deploy

# Deploy the site to development.
job_deploy_development:
  only:
    - development
  stage: deploy
  script:
    - bin/boi_ci build:tasks development
    - bin/boi_ci deploy:git development
    - bin/boi_ci drush:updatedb development

# Deploy the site to master.
job_deploy_master:
  only:
    - master
  stage: deploy
  script:
    - bin/boi_ci build:tasks master
    - bin/boi_ci deploy:git master
    - bin/boi_ci drush:updatedb master

```

###Run your local build:

```bash
composer install --prefer-dist --no-interaction --no-progress
```


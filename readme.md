# BOI CI
The BOI CI was developed by [Blue Oak Interactive](https://www.blueoakinteractive.com) to streamline the build and deployment processes of php based web applications across a variety of hosting providers with a focus on Drupal.

**Reasons to use this project:**

1) You host Drupal websites across multiple providers and want a unified system to manage deployments.
2) You want to perform tests via CI on your codebase prior to deploying to production.
3) You want to simplify your build process.

## Installation Requirements
- [Composer](https://getcomposer.org/) - PHP Dependency management
- [Symfony ~2.8](https://symfony.com/) - Symfony 2 is required  as the Drupal Console for Drupal 8 currently runs on Symfony
 - [Drush ~8](http://www.drush.org/en/master/) - Drupal Shell
- [Git ~2](https://git-scm.com/) - For version control
- [GitLab](https://about.gitlab.com/) - Git repository hosting and CI

## Installation Instructions
To simplify the configuration process we have created example projects that you can use as a starting point for configuring boi_ci for your Drupal project.

**We plan to have boilerplate configurations for the following providers:**
- [Platform.sh](https://platform.sh/)
- [Pantheon.io](https://pantheon.io/)
- [Acquia.com](https://www.acquia.com/)
- As well as other non-Drupal specific providers

**See:** [blueoakinterative/boi_ci_examples](https://gitlab.com/blueoakinteractive/boi_ci_examples)


## Files and Folders
### Required files
The following files are required for all projects running boi_ci.
```bash
├── .boi_ci.yml
└── composer.json
```

### Sample folder structure
Below is a typical folder structure for building a project using the boi_ci.  This includes all of the necessary components to build a project using drush make including patches, custom modules/themes/libraries, as well as deployment using Gitlab CI.

```bash
├── libraries/
│   └── library_name/
├── local/
│   ├── files/
│   └── settings.local.php
├── modules/
│   ├── custom_module_name/
│   ├── another_custom_module_name/
├── patches/
│   ├── example_patch.patch
│   ├── another_example.patch
├── themes/
│   └── custom_theme/
├── .boi_ci.yml
├── .gitignore
├── .gitlab-ci.yml
├── composer.json
├── composer.lock
├── example.aliases.drushrc.php
├── project.make
└── settings.php
```

## BOI CI Specific Configuration

### Build

In the `.boi_ci.yml` file, there is a section called "build".

#### Build Root

`build.root` allows you to define where your project will be built into, both for local development and when pushing to external environments. In this example, the build root is "www". When running `composer install` all of the build assets will be placed into the www sub-directory. The build root should be excluded in your .gitignore file.

### Temporary Directory

A writable temp directory is required to have in your .boi-ci.yml file.
Usually `/tmp` will suffice.

### Symlinks

In the `.boi_ci.yml` file, there is a section called "symlinks". This section allows you to define files or folders outside of your build root that should be included in your build.

In this example, we're sym-linking or custom modules, themes, and libraries folders from the root of the project into www/sites/default/______ (note the "www" is inferred from your build root). This is also the location they will be when pushing the code to your external environments.

We use symlinks so that you can edit the files at the root of your project during development and have them update in your active build directory. In otherwords, you don't have to run `composer install` every time you make a change to your custom module during development.

The required values for a symlink are are "source" and "destination". Source is the source relative to your project root. Destination is the destination relative to your build root. For example, the ./local/settings.local.php file in the example config will be placed into www/sites/default/settings.local.php (note "www" is inferred from your build root).

We typically use a `./local` directory during local development that contains settings.php and files (sites/default/files). To follow this method, create `./local/settings.local.php` (file) and `./local/files` (directory). Then either re-run `composer install` or run `bin/boi_ci build:symlinks`

### Environments

The environments section allows you to define external environments that will be used to push git artifacts to. An artifact is another git repo that contains all of your build files. We use artifacts for several reasons, but the main reason is that most hosting providers provide their own git repo for pushing to their environment. BOI CI allows you to build your project into their repos and provides the ability to have a common workflow, regardless of the hosting provider. You can even push your artifacts to multiple hosting providers from the same project, as seen in the example.

#### Environment Specific Git Artifact Repo Configuration

Each environment should have a git artifact repo defined. This represents the git remote and branch that will be pushed to whenever a build it being deployed.

#### Environment Artifiact Repo Root

When configuring your environment you can specify the `root` variable that defines the artifact repo's desired git root.

The default root value is `build_root` which essentially means that the build directory will be committed and pushed to your artifact repo.  Alternatively you can set the value to `source_root` which will commit the entire project directory to your artifact repo.

#### Environment Drush Alias Definition

In order to call drush commands on remote environments you will need to define a drush alias per environment.  These aliases are defined in your project drush alias file as seen in the sample `.boi_ci.yml` file below.

*ie: `example.aliases.drushrc.php`*

```
drush:
  alias: example.development
```

#### Environment Artifiact Repo Exclusions

When building to an artifact repo there may be instances where you do not need all of the files stored in the `source_root`.  This is where exlusions come in handy.

```
exclude:
  # Global CI excludes.
  - /locals
  - /composer.*
  - /.gitignore
  - /project.make
```

## Sample .boi_ci.yml file
```yaml
# Required build root directory. This should match
# the folder that composer.json is using in installer
# paths.
build:
  root: www
  tasks:
    example_task:
      # Commands are run in the build root. To run in the project root, use a relative path.
      directory: "../"
      command: "echo 'create a file in the root' > example.txt"
  tests:
    example_test:
      directory: "../"
      command: "phpcs --standard=phpcs.xml ./"


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
    tasks:
      example_task:
        # Commands are run in the build root. To run in the project root, use a relative path.
        directory: "../"
        command: "echo 'create a file in the root' > example.txt"
    tests:
      example_test:
        directory: "../"
        command: "phpcs --standard=phpcs.xml ./"

```

## Sample .gitlab-ci.yml file

See [Gitlab Documentation](https://docs.gitlab.com/ee/ci/yaml/) for more information.

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

## Sample composer.json
Below is a sample composer.json file that can be modified to suit the needs of your project.  The key components that should be noted are the packages defined in the "require" statement as well as the configured post install/update "scripts".
```json
{
  "name": "blueoakinteractive/example",
  "description": "example.com Drupal 7",
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

## Run your local build

```bash
composer install --prefer-dist --no-interaction --no-progress
```


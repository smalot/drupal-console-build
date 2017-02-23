# Drupal Console Build

Expose new commands to `Drupal Console` to run builds using a config file.

[![Latest Stable Version](https://poser.pugx.org/smalot/drupal-console-build/v/stable)](https://packagist.org/packages/smalot/drupal-console-build)
[![Latest Unstable Version](https://poser.pugx.org/smalot/drupal-console-build/v/unstable)](https://packagist.org/packages/smalot/drupal-console-build)
[![License](https://poser.pugx.org/smalot/drupal-console-build/license)](https://packagist.org/packages/smalot/drupal-console-build)
[![composer.lock](https://poser.pugx.org/smalot/drupal-console-build/composerlock)](https://packagist.org/packages/smalot/drupal-console-build)

[![Total Downloads](https://poser.pugx.org/smalot/drupal-console-build/downloads)](https://packagist.org/packages/smalot/drupal-console-build)
[![Monthly Downloads](https://poser.pugx.org/smalot/drupal-console-build/d/monthly)](https://packagist.org/packages/smalot/drupal-console-build)
[![Daily Downloads](https://poser.pugx.org/smalot/drupal-console-build/d/daily)](https://packagist.org/packages/smalot/drupal-console-build)


## Setup

Include this library in your Drupal project:

````sh
composer require "smalot/drupal-console-build:*"
````

Must be added in your `settings.yml` file.

````yaml
services:
  console.deploy_run:
    class: Smalot\Drupal\Console\Build\Command\RunCommand
    arguments: []
    tags:
      - { name: drupal.command }

  console.deploy_list:
    class: Smalot\Drupal\Console\Build\Command\ListCommand
    arguments: []
    tags:
      - { name: drupal.command }
````


## Sample config file

````yaml
# List stages enabled
stages:
    - compile
    - cache
    - features

# Commands section
compile_css:
    stage: compile
    script:
        - echo "Compile SASS file into CSS"
        - compass build
    allow_failure: true

cache_rebuild:
    stage: cache
    script:
        - echo "Cache Rebuild"
        - cd web && drush cache-rebuild

features_revert:
    stage: features
    script:
        - echo "Features revert all"
        - cd web && drush fra -y
    except:
        - master
````


### Settings

`stages` allows to group commands and to order them during process.
If `stages` entry is specified, all commands without `stage` attribute won't be run.

Both `only` and `except` accepts regex patterns (must be surrounded by backslash `/`).
You can't specify `only` and `except` at the same time.

````yaml
command:
    only:
        - "/^fix-.*$/i"
````

The `allow_failure` will report a command in error, but won't stop future commands.
Otherwise, the whole build will stop at the first command line error.


## Usage

Run all tasks

````sh
drupal build:run
````

Run all tasks of one stage

````sh
drupal build:run --stage=cache
````

Run specific tasks

````sh
drupal build:run compile_css cache_rebuild
````

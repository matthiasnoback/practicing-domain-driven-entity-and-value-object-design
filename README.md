# PHP testing playground

[![Build Status](https://travis-ci.org/matthiasnoback/testing-playground.svg?branch=master)](https://travis-ci.org/matthiasnoback/testing-playground)

## Option 1: Use with locally installed PHP

### Requirements

- PHP (>= 7.1)
- Composer

### Getting started

- Clone this repository and `cd` into it.
- Run `composer install --prefer-dist` to install the project's dependencies.

### Usage

- Run `./run_tests.sh` to run the tests.

## Option 2: Use with Docker

### Requirements

- [Docker Engine](https://docs.docker.com/engine/installation/)
- [Docker Compose](https://docs.docker.com/compose/install/)

### Getting started

- [Follow the instructions](https://github.com/matthiasnoback/php-workshop-tools/blob/master/README.md) for setting environment variables `HOST_UID`, `HOST_GID` and `DOCKER_HOST_IP`.
- Clone this repository and `cd` into it.
- Run `docker-compose pull`.
- Run `bin/composer.sh install --prefer-dist` to install the project's dependencies.
- Optionally, [configure PhpStorm for debugging](https://github.com/matthiasnoback/php-workshop-tools/blob/master/README.md).

### Usage

- Run `docker/composer.sh` to use Composer (e.g. `docker/composer.sh require --dev symfony/var-dumper`).
- Run `docker/run_tests.sh` to run the tests.

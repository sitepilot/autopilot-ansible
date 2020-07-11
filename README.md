# Autopilot

![Autopilot](https://github.com/sitepilot/autopilot/workflows/run-tests/badge.svg)

Autopilot is a (cloud) webhosting control panel for managing multiple servers, sites and WordPress installations. We use Autopilot at [Sitepilot](https://sitepilot.io) for our managed WordPress webhosting platform. With Autopilot you can:

* Provision WordPress optimized (Openlitespeed) web servers and (Caddy) load balancers.
* Monitor server and site health of the provisioned servers and sites.
* Manage and maintain WordPress sites.

Autopilot is build on top of [Laravel](https://laravel.com/) and uses [Ansible](https://www.ansible.com/) to provision servers, users, databases and sites on Ubuntu 20.04 LTS servers.

## Supported Server Providers

Autopilot supports the following server providers:

* [UpCloud](https://upcloud.com/signup/?promo=HGMAN9)

If your preferred provider is not baked into Autopilot, you can always use the Custom VPS option. There are a few requirements to ensure that this works successfully:

* The server your connecting to must be running a fresh installation of Ubuntu 20.04 x64.
* Your server must be accessible by the Autopilot host.
* There must be a root user with no password.
* During the creation process, you may customize the SSH Port that is used (defaulted to 22).

## Web Server Configuration

### Packages & Services

The following packages/services will be installed and configured on web servers (together with dependencies):

* [OpenLitespeed (web server)](https://www.litespeedtech.com/open-source/openlitespeed)
* [LSPHP 7.4](https://www.litespeedtech.com/open-source/litespeed-sapi/php)
* [LSPHP 7.3](https://www.litespeedtech.com/open-source/litespeed-sapi/php)
* [Composer](https://getcomposer.org/)
* [WPCLI](https://wp-cli.org/)
* [WordMove](https://github.com/welaika/wordmove)
* [UFW (firewall)](https://help.ubuntu.com/community/UFW)
* [Fail2Ban](https://en.wikipedia.org/wiki/Fail2ban)
* [OpenSSH Server & SFTP](https://www.openssh.com/)
* [SSMTP (email relay)](https://wiki.archlinux.org/index.php/SSMTP)
* [Docker](https://www.docker.com/)
* [Docker Compose](https://hub.docker.com/_/redis/)
* [Docker Redis 5](https://redis.io/)
* [Docker MariaDB 10.4](https://hub.docker.com/_/mariadb)
* [phpMyAdmin 5](https://www.phpmyadmin.net/)
* [Restic (for backups)](https://restic.net/)
* [Node Exporter (for monitoring)](https://prometheus.io/docs/guides/node-exporter/)

Users are isolated and allowed to use SFTP with password authentication (chroot directory `/opt/sitepilot/users/%u`).

### Tools

* phpMyAdmin: `http://<domain.name>/.sitepilot/pma/`.
* Health check: `http://<domain.name>/.sitepilot/health/`.

### Filesystem

* Users folder: `/opt/sitepilot/users`.
* Site public folder: `/opt/sitepilot/users/{{ user.name }}/{{ app.name }}/public`.
* Site logs folder: `/opt/sitepilot/users/{{ user.name }}/{{ app.name }}/logs`.
* OpenLitespeed logs folder: `/opt/sitepilot/services/olsws/logs`.
* OpenLitespeed temp folder: `/opt/sitepilot/services/olsws/tmp`.
* Docker MySQL data folder: `/opt/sitepilot/services/mysql/data`.
* Docker MySQL logs folder: `/opt/sitepilot/services/mysql/logs`.
* Docker Redis data folder: `/opt/sitepilot/services/redis/data`.

## Load Balancer Configuration

### Packages & Services

The following packages/services will be installed and configured on load balancer servers (together with dependencies):

* [Caddy Web Server (for proxy and auto ssl)](https://caddyserver.com/)
* [UFW (firewall)](https://help.ubuntu.com/community/UFW)
* [Restic (for backups)](https://restic.net/)
* [Node Exporter (for monitoring)](https://prometheus.io/docs/guides/node-exporter/)

### Filesystem

* Caddy vhosts folder: `/opt/sitepilot/services/caddy/vhosts`.

## Monitoring

Autopilot uses [Prometheus](https://prometheus.io/), [Alertmanager](https://prometheus.io/docs/alerting/latest/alertmanager/), [Blackbox Exporter](https://github.com/prometheus/blackbox_exporter) and [Grafana](https://grafana.com/) to monitor servers and sites. These services are included in the [Autopilot Stack](https://github.com/sitepilot/autopilot-stack).

## Installation, Updates & Development

### Installation

The recommended way to install Autopilot is using the preconfigured Autopilot Stack. [You can find the installation instructions here.](https://github.com/sitepilot/autopilot-stack)

### Development

* Clone this repository.
* Copy the example environment file and modify it to your needs: `cp .env.example .env`.
* Install composer packages with `composer install`. *NOTE: This will prompt for your Laravel Nova username and password.*
* Generate application key with `php artisan key:generate`.
* Migrate and seed the database with `php artisan migrate --seed`.
* Navigate to the application domain and login. Default user: `admin@sitepilot.io`, default password: `supersecret`.

## License

MIT / BSD

## Author

Autopilot was created in 2020 by [Nick Jansen](https://nbejansen.com/).

# Screenshots 

![screenshot](screenshot.png)
![screenshot](screenshot-status.png)
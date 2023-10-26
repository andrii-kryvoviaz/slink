# Symfony Swoole Runtime Template
This is a simple example of how to use Symfony with Swoole Runtime. It uses Docker to run the application.

## Requirements
- Docker
- Docker Compose Plugin
- Make (optional)

## Usage
To run the application, you need to have Docker installed. Then, run the following command:

```bash
make run
```

The above will build the Docker image and run the application in `production` mode. You can access the application at http://localhost:8080.

Alternatively, you can run the following command:

```bash
docker compose -f docker-compose.yaml up -d --build
```

To run the application in `development` mode, run the following command:

```bash
make run-dev
```

or with Docker Compose:

```bash
docker compose -f docker-compose.yaml -f docker-compose.dev.yaml up -d --build
```

**Be aware that the development mode will not use the Swoole Runtime, but the Symfony Web Server instead.**

### PhpStorm Xdebug Configuration

The `development` mode has Xdebug enabled. To use it with PhpStorm, you need to configure the following:

1. Navigate to `Settings > PHP > Servers` and add a new server with the following configuration:
    - Name: `localhost`
    - Host: `localhost`
    - Port: `8080`
    - Debugger: `Xdebug`
    - Use path mappings: `Yes`
    - Absolute path on the server: `/app`
    - Absolute path on the local machine: `<path-to-project>/app`


2. Navigate to `Settings > PHP > CLI Interpreter` and create a new one based on the docker compose file:
    - Name: `Docker Compose Interpreter`
    - Server: `Docker`
    - Configuration file: `docker-compose.yaml`
    - Service: `app`
    - Connect to existing container: `Yes`
   

3. Create new `Run/Debug Configuration` of type `PHP Remote Debug` with the following configuration:
    - Name: `Docker Compose`
    - Server: `localhost`
    - Ide key(session id): `PHPSTORM`
    - Filter debug connection by IDE key: `Yes`

Now you can run the application in `development` mode and debug it with PhpStorm.

## Customizing PHP Runtime

The template is configured to use Swoole Runtime for `production` mode and Symfony Web Server for `development`.
The following supervisor configuration files could be found in the `docker/runtime` directory.

Be aware that it might require some additional configuration and adjustments made in Dockerfile to work properly.

Here is an example of a `PHP-FPM` configuration file:

```ini
[program:php-fpm]
command = /usr/local/sbin/php-fpm --force-stderr --nodaemonize --fpm-config /usr/local/etc/php-fpm.d/www.conf
autostart=true
autorestart=true
priority=5
stdout_events_enabled=true
stderr_events_enabled=true
stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0
stderr_logfile=/dev/stderr
stderr_logfile_maxbytes=0
stopsignal=QUIT
```

This will require to use `php-fpm` image and configuring a reverse proxy to it. e.g. `Nginx`.

## License

This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.

FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
    libpq-dev \
    && docker-php-ext-install pgsql pdo_pgsql

WORKDIR /app

COPY . /app

RUN chmod +x /app/start.sh

EXPOSE 8080

CMD ["/app/start.sh"]
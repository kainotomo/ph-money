cd C:\intergo\local\docker
docker-compose exec -T --workdir=/code_ph-money/build/ php-fpm php build_phmoney.php --step=1

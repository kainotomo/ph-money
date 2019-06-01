cd C:\intergo\local\docker
docker-compose exec -T --workdir=/code_ph-money/build/ php-fpm php build_phmoney.php --step=2 --exclude-gzip --exclude-zip --exclude-bzip2

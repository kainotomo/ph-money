cd C:\intergo\local\docker
docker-compose exec -T --workdir=/code_ph-money/build/ php-fpm php build_phmoney.php --remote=%1 --step=2 --exclude-zip --exclude-bzip2

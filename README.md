`sudo docker-compose up -d`

`sudo docker exec -i valantic-soccer-team-akamm-db-1 mysql -u pimcore -ppimcore pimcore < import/dump.sql`

`chmod -R 775 public/var`

`sudo docker exec -it valantic-soccer-team-akamm-php-1 bin/console app:import:csv import/teams.csv import/players.csv`
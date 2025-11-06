<?php

namespace App\Command;

use Pimcore\Model\DataObject\Data\GeoCoordinates;
use Pimcore\Model\DataObject\Team;
use Pimcore\Model\DataObject\Player;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Pimcore\Model\Asset;

class ImportCsvCommand extends Command
{
    protected static $defaultName = 'app:import:csv';
    protected static $defaultDescription = 'Importiert Teams und Spieler aus CSV-Dateien.';

    protected function configure(): void
    {
        $this
            ->addArgument('teams', InputArgument::REQUIRED, 'Pfad zur Team-CSV')
            ->addArgument('players', InputArgument::REQUIRED, 'Pfad zur Player-CSV');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $teamsCsv = $input->getArgument("teams");
        $playersCsv = $input->getArgument("players");

        $this->importTeams($teamsCsv, $output);
        $this->importPlayers($playersCsv, $output);

        return Command::SUCCESS;
    }

    private function importTeams(string $path, OutputInterface $output): void
    {
        $csv = fopen($path, "r");
        $headers = fgetcsv($csv);

        while(($line = fgetcsv($csv)) !== false)
        {
            $data = array_combine($headers, $line);
            $key = str_replace(" ", "-", strtolower($data["name"]));

            $existingTeam = Team::getByPath("/Teams/" . $key);

            if($existingTeam instanceof Team && $existingTeam->isPublished())
            {
                $team = $existingTeam;
            }
            else
            {
                $team = new Team();
                $team->setParentId(2);
                $team->setKey($key);
            }

            $team->setName($data["name"]);
            $team->setLeague($data["league"]);
            $team->setDescription($data["description"]);
            $team->setYear((int)$data["year"]);
            $team->setTrainer($data["trainer"]);
            $team->setPublished(true);  

            if (!empty($data['logo'])) {
                $assetPath = '/logos/' . $data['logo'];
                $asset = Asset::getByPath($assetPath);

                if ($asset instanceof Asset\Image) {
                    $team->setLogo($asset);
                }
            }
            
            $geo = new GeoCoordinates();
            $geo->setLatitude((float) $data['location_lat']);
            $geo->setLongitude((float) $data['location_lng']);

            $team->setLocation($geo);

            $team->save();
        }
        fclose($csv);
    }

    private function importPlayers(string $path, OutputInterface $output): void
    {
        $csv = fopen($path, "r");
        $headers = fgetcsv($csv);

        while(($line = fgetcsv($csv)) !== false)
        {
            $data = array_combine($headers, $line);

            $name = strtolower($data["firstname"]) . " " . strtolower($data["lastname"]);
            $key = str_replace(" ", "-", $name);

            $existingPlayer = Player::getByPath("/Players/" . $key);

            $team = Team::getByName($data["team"]);
            if (!$team->current()) {
                continue;
            }

            if($existingPlayer instanceof Player && $existingPlayer->isPublished())
            {
                $player = $existingPlayer;
            } 
            else
            {
                $player = new Player();
                $player->setParentId(5);
                $player->setKey($key);                
            }

            $player->setFirstname($data["firstname"]);
            $player->setLastname($data["lastname"]);
            $player->setNumber($data["number"]);
            $player->setAge($data["age"]);
            $player->setPosition($data["position"]);
            $player->setTeam($team->current());
            $player->setPublished(true);

            $player->save();
        }
        fclose($csv);
    }
}
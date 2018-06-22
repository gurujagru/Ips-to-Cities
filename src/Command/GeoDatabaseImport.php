<?php

namespace App\Command;

use Exception;
use function file_exists;
use function file_put_contents;
use GuzzleHttp\Client;
use function mkdir;
use function rename;
use function shell_exec;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use function unlink;

/**
 * Class GeoDatabaseImport
 * @package App\Command
 */
class GeoDatabaseImport extends Command
{
    protected function configure()
    {
        $this
            ->setName('geo:database:update')
            ->setDescription('Updates MaxMind databases for Country and City.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->getFormatter();
        $output->writeln('<comment>Starting MaxMind database update...</comment>');

        try
        {
            $client = new Client([
                'timeout' => 60,
                'base_uri' => 'http://geolite.maxmind.com/download/geoip/database/'
            ]);

            if (!file_exists('var/misc'))
                mkdir('var/misc');

            mkdir('var/misc/geoTmp');

            $response = $client->get('GeoLite2-Country.tar.gz');

            if ($response->getStatusCode() !== 200)
                throw new Exception('Can\'t download Country database.');

            file_put_contents('var/misc/geoTmp/country.tar.gz', $response->getBody());
            shell_exec('cd var/misc/geoTmp/ && tar -zxvf country.tar.gz');

            $output->writeln('<info>Downloaded and saved country database.</info>');

            $response = $client->get('GeoLite2-City.tar.gz');

            if ($response->getStatusCode() !== 200)
                throw new Exception('Can\'t download City database.');

            file_put_contents('var/misc/geoTmp/city.tar.gz', $response->getBody());
            shell_exec('cd var/misc/geoTmp/ && tar -zxvf city.tar.gz');

            $output->writeln('<info>Downloaded and saved city database.</info>');

            $finder = new Finder();
            $finder
                ->files()
                ->name('*.mmdb')
                ->in('var/misc/geoTmp');
            foreach ($finder as $fileInfo)
            {
                $currentFilePath = 'var/misc/' . $fileInfo->getFilename();
                if (file_exists($currentFilePath))
                    unlink($currentFilePath);

                rename($fileInfo->getPathname(), $currentFilePath);
            }

            shell_exec('rm -rf var/misc/geoTmp/');
        }
        catch (Exception $e)
        {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
        }

        $output->writeln('<info>Finished MaxMind database update.</info>');
    }
}

// END
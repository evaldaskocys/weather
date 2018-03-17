<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Psr\Log\LoggerInterface;

class AppWeatherCommand extends Command
{
    protected static $defaultName = 'app:weather';
    private $cacheDir;
    private $logger;

    public function __construct(string $cacheDir, LoggerInterface $logger)
    {
        $this->cacheDir = $cacheDir;
        $this->logger = $logger;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Print weather information')
            ->addArgument('location', InputArgument::REQUIRED, 'Location')
            ->addOption('noCache', null, InputOption::VALUE_NONE, 'Disable cache');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $location = urlencode($input->getArgument('location'));
        $url = 'https://query.yahooapis.com/v1/public/yql?q=select%20*%20from%20weather.'.
            'forecast%20where%20woeid%20in%20(select%20woeid%20from%20geo.places(1)%20where%20text%3D%22'.
            $location.'%22)&format=json&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys&u=c';

        $cache = $this->cacheDir . '/' . sha1($url) . date('YmdHi') . '.cache';
        if (file_exists($cache) && !$input->getOption('noCache')) {
            $result = file_get_contents($cache);
            $io->text('Loading from cache...');
        } else {
            $curl = curl_init();
            curl_setopt_array(
                $curl,
                array(
                    CURLOPT_RETURNTRANSFER => 1,
                    CURLOPT_URL => $url
                )
            );
            $result = curl_exec($curl);
            if (!empty($result)) {
                file_put_contents($cache, $result);
            } else {
                $result = curl_exec($curl);
                if (!empty($result)) {
                    file_put_contents($cache, $result);
                }
            }
            curl_close($curl);
        }
        if (empty($result)) {
            $io->warning(sprintf('Could not find weather data for location "%s"', $location));
        }
        $json = json_decode($result, true);

        $windDirection = $json['query']['results']['channel']['wind']['direction'];
        $windSpeed = $json['query']['results']['channel']['wind']['speed'];
        $temperature = $json['query']['results']['channel']['item']['condition']['temp'];
        $text = $json['query']['results']['channel']['item']['condition']['text'];

        $io->table(
            ['Temperature', 'Condition', 'Wind'],
            [[$temperature .'C', $text, $windSpeed . 'm/s (direction: ' . $windDirection . ')']]
        );
        
        $io->success('Done');
        $this->logger->info('Command executed.');
    }
}

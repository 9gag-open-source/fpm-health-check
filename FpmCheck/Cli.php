<?php

namespace FpmCheck;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Application;
use EBernhardson\FastCGI\Client as FastCGIClient;

class Cli {
    public static function makeApp() 
    {
        $app = new Application('health-check-cli');
        $app->add(
            new class extends Command
            {
                public function __construct()
                {
                    parent::__construct('run');
                }

                protected function execute(InputInterface $input, OutputInterface $output)
                {
                    $fpmHost = $input->getOption('fpm-host');
                    $fpmPort = $input->getOption('fpm-port');
                    $fastcgi = new FastCGIClient($fpmHost, $fpmPort);
                    $output->writeln("Checking at $fpmHost:$fpmPort");

                    $script = $input->getArgument('script');
                    $path = $input->getArgument('path');
                    $checkFpm = $input->getOption('check-fpm-status');
                    $response = $fastcgi->request([
                        'REQUEST_METHOD' => 'GET',
                        'SCRIPT_NAME' => $script,
                        'SCRIPT_FILENAME' => $script,
                        'REQUEST_URI' => $path,
                        'QUERY_STRING' => $checkFpm ? 'json' : '',
                    ], '');

                    if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                        $output->writeln('Server response : ' . json_encode($response, JSON_PRETTY_PRINT));
                    }

                    $this->_checkHttp($output, $response['body'], $response['statusCode'], $input->getOption('fail-on-empty'));
                    if ($checkFpm) {
                        $this->_checkFpmStatus($output, $response['body']);
                    }

                    $output->writeln("Check passed.", OutputInterface::VERBOSITY_NORMAL);
                }

                private function _checkFpmStatus(OutputInterface $output, $response)
                {
                    $status = json_decode($response, true);
                    if ($status['listen queue'] > 0
                        && $status['active processes'] >= $status['total processes']
                    ) {
                        $output->writeln(
                            sprintf('FPM is at capacity [queue=%d],[processes=%d/%d]',
                                $status['active processes'] >= $status['total processes'])
                        );
                        exit(1);
                    }
                }

                private function _checkHttp(OutputInterface $output, $response, $status, $failOnEmpty)
                {
                    if ($failOnEmpty && empty($response)) {
                        $output->writeln('Empty response');
                        exit(1);
                    }

                    if ($status < 200 || $status > 399) {
                        $output->writeln("Abnormal status code : $status");
                        exit(1);
                    }
                }

                protected function configure()
                {
                    $this
                        ->addArgument('script', InputArgument::REQUIRED, 'Full path to the index PHP script')
                        ->addArgument('path', InputArgument::OPTIONAL, 'The path of the health-check or status endpoint')
                        ->addOption('fpm-host', null, InputOption::VALUE_REQUIRED, 'FPM server host', '127.0.0.1')
                        ->addOption('fpm-port', null, InputOption::VALUE_REQUIRED, 'FPM server port', '9000')
                        ->addOption('check-fpm-status', null, InputOption::VALUE_NONE, 'Runs an additional check on FPM status response')
                        ->addOption('fail-on-empty', null, InputOption::VALUE_NONE, 'Fails the check if response body is empty');
                }
            }
        );

        return $app;
    }
}
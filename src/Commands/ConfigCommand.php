<?php

namespace PowerDICLI\Commands;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'config',
    description: 'Configure PowerDI Project'
)]
class ConfigCommand extends Command {

    protected function configure() {
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        if (!file_exists(getcwd() . "/powerdi.yaml")) {
            $output->writeln("<error>Error: You are not in a PowerDI project directory!</error>");
            return Command::FAILURE;
        }

        $config = yaml_parse_file(getcwd() . '/powerdi.yaml');
        $output->writeln("<info>Current Configuration:</info>");
        $output->writeln(yaml_emit($config));
        return Command::SUCCESS;
    }
}
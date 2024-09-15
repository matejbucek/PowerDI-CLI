<?php

namespace PowerDICLI\Commands;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: "serve",
    description: "Serve the project",
    aliases: ["server"]
)]
class ServeCommand extends Command {
    protected function configure(): void {
        parent::configure();
        $this->addArgument("port", InputArgument::OPTIONAL, "Port to run the server on");
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $port = $input->getArgument("port") ?? "8000";

        if(!file_exists(getcwd() . "/powerdi.yaml")) {
            $output->writeln("<error>Error: You are not in a PowerDI project directory!</error>");
            return Command::FAILURE;
        }

        if (preg_match("/^\d+$/", $port) !== 1) {
            $output->writeln("<error>Invalid port number</error>");
            return Command::FAILURE;
        }
        $output->writeln("<info>Server running on http://localhost:$port</info>");
        $output->writeln("<comment>Press Ctrl+C to stop the server</comment>");
        $rc = exec("php -S localhost:$port -t " . getcwd() . "/public");
        if($rc != 0 && $rc != 130) {
            $output->writeln("<error>Error while starting the server</error>");
            return Command::FAILURE;
        }
        return Command::SUCCESS;
    }
}
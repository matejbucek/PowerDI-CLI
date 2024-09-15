<?php

namespace PowerDICLI\Commands;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

#[AsCommand(
    name: 'run',
    description: 'Run application task from powerdi.yaml'
)]
class RunCommand extends Command {

    protected function configure() : void {
        parent::configure();
        $this->addArgument('task', InputArgument::REQUIRED, 'Task name to run');
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int {
        $task = $input->getArgument('task');
        $output->writeln("<info>Running task: $task</info>");

        if(!file_exists(getcwd() . "/powerdi.yaml")) {
            $output->writeln("<error>Error: You are not in a PowerDI project directory!</error>");
            return Command::FAILURE;
        }

        $config = yaml_parse_file(getcwd() . '/powerdi.yaml');
        if(!isset($config['tasks'][$task])) {
            $output->writeln("<error>Error: Task $task not found in powerdi.yaml</error>");
            return Command::FAILURE;
        }

        $process = new Process([$config['tasks'][$task]]);
        if($process->run() !== 0) {
            $output->writeln("<error>Error while running the task: " . $process->getErrorOutput() . "</error>");
            return Command::FAILURE;
        }

        $output->writeln("<info>Task $task completed successfully</info>");
        return Command::SUCCESS;
    }
}
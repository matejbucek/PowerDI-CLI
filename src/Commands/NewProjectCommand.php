<?php

namespace PowerDICLI\Commands;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Process\Process;

#[AsCommand(
    name: "new:project",
    description: "Create a new project",
    aliases: ["new"]
)]
class NewProjectCommand extends Command {
    protected function configure(): void {
        parent::configure();
        $this->addArgument("name", InputArgument::REQUIRED, "The name of the project");
        $this->addArgument("username", InputArgument::OPTIONAL, "Username used by composer");
        $this->addArgument("description", InputArgument::OPTIONAL, "Description used by composer");
    }

    protected function execute($input, $output): int {
        $name = $input->getArgument("name");
        $username = $input->getArgument("username");
        $description = $input->getArgument("description" ?? "");

        if(!$username) {
            $process = new Process(["whoami"]);
            $process->run();
            $username = trim($process->getOutput());
        }

        if($username === "") {
            $output->writeln("<error>Error: Please provide a username or run the command as a user with a username</error>");
            return Command::FAILURE;
        }

        if(file_exists($name)) {
            $output->writeln("<error>Error: The directory $name already exists</error>");
            return Command::FAILURE;
        }

        $output->writeln("<info>Creating project $name</info>");
        $process = new Process(["git", "clone", "https://github.com/matejbucek/PowerDI-Project.git", $name]);
        if ($process->run() !== 0) {
            $output->writeln("<error>Error while cloning the repository: " . $process->getErrorOutput() . "</error>");
            return Command::FAILURE;
        }

        $output->writeln("<comment>Setting up the project</comment>");
        $composerJson = json_decode(file_get_contents("$name/composer.json"), true);
        $composerJson["name"] = "$username/$name";
        $composerJson["description"] = $description ? $description : "A new PowerDI project";
        file_put_contents("$name/composer.json", json_encode($composerJson, JSON_PRETTY_PRINT));
        $process = new Process(["composer", "install", "--working-dir=$name"]);
        if ($process->run() !== 0) {
            $output->writeln("<error>Error while installing dependencies: " . $process->getErrorOutput() . "</error>");
            return Command::FAILURE;
        }

        $output->writeln("<info>Project $name created successfully</info>");
        return Command::SUCCESS;
    }
}
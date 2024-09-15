<?php

namespace PowerDICLI\Commands;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;

#[AsCommand(
    name: "build",
    description: "Build the project"
)]
class BuildCommand extends Command {

    protected function configure(): void {
        parent::configure();
        $this->addArgument("destination", InputArgument::OPTIONAL, "Destination directory", "build");
        $this->addArgument("archive", InputArgument::OPTIONAL, "zip/tar/phar", "zip");
        $this->addArgument("exclude", InputArgument::OPTIONAL, "Files to exclude", "");
    }

    protected function execute($input, $output): int {
        $destination = strtolower($input->getArgument("destination"));
        $archive = $input->getArgument("archive");

        if (!file_exists($destination)) {
            mkdir($destination);
        }

        if(!file_exists(getcwd() . "/powerdi.yaml")) {
            $output->writeln("<error>Error: You are not in a PowerDI project directory!</error>");
            return Command::FAILURE;
        }

        $output->writeln("<info>Building the project</info>");
        if ($archive === "zip") {
            $process = new Process(["zip", "-r", "$destination/project.zip", "."]);
            $process->run();
        } else if ($archive === "phar") {
            $phar = new \Phar("$destination/project.phar");
            $phar->compressFiles(\Phar::GZ);
            $phar->setSignatureAlgorithm(\Phar::SHA1);
            $phar->startBuffering();

            $output->writeln("<comment>Adding composer files</comment>");
            $composerFiles = new Finder();
            $composerFiles->files()->ignoreVCS(true)->name('/.*\.(php|bash|fish|zsh)/')->in(getcwd() . '/vendor');

            $output->writeln("<comment>Adding vendor files</comment>");
            foreach ($composerFiles as $file) {
                $path = $file->getRealPath();
                $path = str_replace(getcwd() . '/', '', $path);
                $phar->addFile($file->getRealPath(), $path);
            }

            $projectFiles = new Finder();
            $projectFiles->files()
                ->ignoreVCS(true)
                ->name('*')
                ->in(getcwd() . '/src')
                ->in(getcwd() . "/config")
                ->in(getcwd() . "/public")
                ->in(getcwd() . "/templates");

            $output->writeln("<comment>Adding project files</comment>");
            foreach ($projectFiles as $file) {
                $path = $file->getRealPath();
                $path = str_replace(getcwd() . '/', '', $path);
                $phar->addFile($file->getRealPath(), $path);
            }

            $output->writeln("<comment>Adding autoload file</comment>");
            $phar->setStub("
                <?php
                Phar::mapPhar('project.phar');
                require 'phar://project.phar/vendor/autoload.php';
                require 'phar://project.phar/public/index.php';
                __HALT_COMPILER();
                ");
            $phar->stopBuffering();
        } else {
            $output->writeln("<error>Invalid archive type</error>");
            return Command::FAILURE;
        }
        $output->writeln("<info>Project built</info>");
        return Command::SUCCESS;
    }

}
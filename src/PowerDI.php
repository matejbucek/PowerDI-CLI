<?php

namespace PowerDICLI;

use PowerDICLI\Commands\BuildCommand;
use PowerDICLI\Commands\ConfigCommand;
use PowerDICLI\Commands\NewProjectCommand;
use PowerDICLI\Commands\RunCommand;
use PowerDICLI\Commands\ServeCommand;
use Symfony\Component\Console\Application;

$application = new Application("PowerDI CLI", "1.0.0");

$application->add(new NewProjectCommand());
$application->add(new ServeCommand());
$application->add(new BuildCommand());
$application->add(new RunCommand());
$application->add(new ConfigCommand());

$application->run();
<?php

namespace Incrootal\Migration\SymfonyBundle;

use Incrootal\Migration\SymfonyBundle\Command\ConsoleCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class SprintMigrationBundle extends Bundle
{
    public function registerCommands(Application $application)
    {
        $application->add(new ConsoleCommand());
    }
}

<?php

declare(strict_types=1);

namespace App\Database;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class MakeMigrationCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('make:migration')
            ->setDescription('Cria uma migration Phinx')
            ->addArgument(
                'name',
                InputArgument::REQUIRED,
                'Nome da migration'
            );
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        $name = (string) $input->getArgument('name');

        $timestamp = date('YmdHis');
        $fileName = "{$timestamp}_{$name}.php";

        $dir = dirname(__DIR__) . '/Database/Migration';

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $path = "{$dir}/{$fileName}";

        if (file_exists($path)) {
            $output->writeln(
                "<error>Migration já existe.</error>"
            );

            return Command::FAILURE;
        }

        file_put_contents(
            $path,
            $this->buildTemplate($name)
        );

        $output->writeln(
            "<info>Migration criada:</info> {$fileName}"
        );

        return Command::SUCCESS;
    }

    private function buildTemplate(string $name): string
    {
        return <<<PHP
<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class {$this->migrationClassName($name)} extends AbstractMigration
{
    public function change(): void
    {
        //
    }
}

PHP;
    }

    private function migrationClassName(string $name): string
    {
        return str_replace(
            ' ',
            '',
            ucwords(str_replace('_', ' ', $name))
        );
    }
}

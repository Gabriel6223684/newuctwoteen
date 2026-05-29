<?php

declare(strict_types=1);

arch('todos os arquivos usam strict types')
    ->expect('app')
    ->toUseStrictTypes();

arch('controllers usam strict types')
    ->expect('app\Controller')
    ->toUseStrictTypes();

arch('middleware usa strict types')
    ->expect('app\Middleware')
    ->toUseStrictTypes();

arch('database usa strict types')
    ->expect('app\Database')
    ->toUseStrictTypes();

arch('helpers usam strict types')
    ->expect('app\Helpers')
    ->toUseStrictTypes();

arch('library usa strict types')
    ->expect('app\Library')
    ->toUseStrictTypes();

arch('trait usa strict types')
    ->expect('app\Trait')
    ->toUseStrictTypes();

// -------------------------------------------------------

it('não contém chamadas de debug no código fonte', function () {
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(__DIR__ . '/../../app')
    );

    $debugFunctions = ['var_dump(', 'dd(', 'dump(', 'print_r(', 'var_export('];
    $violations = [];

    foreach ($files as $file) {
        if ($file->getExtension() !== 'php') continue;
        if (str_contains($file->getPathname(), '/Routes/')) continue;

        $content = file_get_contents($file->getPathname());

        foreach ($debugFunctions as $fn) {
            if (preg_match('/(?<![a-zA-Z0-9_])' . preg_quote($fn, '/') . '/', $content)) {
                $violations[] = $file->getPathname() . " contém `{$fn}`";
            }
        }
    }

    expect($violations)->toBe([]);
});

// -------------------------------------------------------

arch('controllers não acessam banco direto')
    ->expect('app\Controller')
    ->not->toUse('PDO');

arch('helpers não acessam banco direto')
    ->expect('app\Helpers')
    ->not->toUse('PDO');

arch('library não acessa banco direto')
    ->expect('app\Library')
    ->not->toUse('PDO');

// -------------------------------------------------------

arch('sem funções perigosas no código')
    ->expect('app')
    ->not->toUse([
        'eval',
        'exec',
        'shell_exec',
        'system',
        'passthru',
        'proc_open',
    ]);

// -------------------------------------------------------

arch('controllers devem ser classes finais')
    ->expect('app\Controller')
    ->toBeFinal()
    ->ignoring('app\Controller\Base');

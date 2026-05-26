<?php

declare(strict_types=1);

test('DatabaseValueNormalizer: normalizeToFloat converte formatos numéricos', function () {
    // Aqui não usamos trait diretamente (para evitar erro de autoload em ambientes onde a trait
    // não é resolvida pelo Pest). Em vez disso, validamos o comportamento dos métodos via classe stub.
    $stub = new class {
        use \App\Trait\DatabaseValueNormalizer;
    };

    expect($stub->normalizeToFloat(42))->toBe(42.0);
    expect($stub->normalizeToFloat(3.14))->toBe(3.14);

    expect($stub->normalizeToFloat('1.234,56'))->toBe(1234.56);
    expect($stub->normalizeToFloat('R$ 99,90'))->toBe(99.90);

    expect($stub->normalizeToFloat('1,234.56'))->toBe(1234.56);

    expect($stub->normalizeToFloat(''))->toBe(0.0);
    expect($stub->normalizeToFloat('abc'))->toBe(0.0);
});


test('DatabaseValueNormalizer: conversão de datas ida e volta preserva valor', function () {
    $stub = new class {
        use \App\Trait\DatabaseValueNormalizer;
    };

    $dataBanco = '2025-06-15 08:45:00';
    $dataBr = '15/06/2025 08:45:00';

    expect(
        $stub->convertBrDateToDatabaseFormat(
            $stub->convertDateToBrFormat($dataBanco)
        )
    )->toBe($dataBanco);

    expect(
        $stub->convertDateToBrFormat(
            $stub->convertBrDateToDatabaseFormat($dataBr)
        )
    )->toBe($dataBr);
});


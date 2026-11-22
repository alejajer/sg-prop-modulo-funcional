<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/Finanzas/UnidadInmobiliaria.php';
require_once __DIR__ . '/../src/Finanzas/AvisoDeCobro.php';
require_once __DIR__ . '/../src/Finanzas/ReportePago.php';
require_once __DIR__ . '/../src/Finanzas/ServicioEmisionCobros.php';
require_once __DIR__ . '/../src/Finanzas/ServicioConciliacionPagos.php';

use SgProp\Finanzas\AvisoDeCobro;
use SgProp\Finanzas\ReportePago;
use SgProp\Finanzas\ServicioConciliacionPagos;
use SgProp\Finanzas\ServicioEmisionCobros;
use SgProp\Finanzas\UnidadInmobiliaria;

$assertTrue = static function (bool $condicion, string $mensaje): void {
    if (!$condicion) {
        fwrite(STDERR, "FALLO: {$mensaje}\n");
        exit(1);
    }
};

$assertThrows = static function (callable $fn, string $mensaje): void {
    $lanzoExcepcion = false;
    try {
        $fn();
    } catch (Throwable) {
        $lanzoExcepcion = true;
    }

    if (!$lanzoExcepcion) {
        fwrite(STDERR, "FALLO (se esperaba excepcion): {$mensaje}\n");
        exit(1);
    }
};

// 1. Camino principal: emision masiva con suma de alicuotas igual a 100%
$u1 = new UnidadInmobiliaria('A-01', 60.0);
$u2 = new UnidadInmobiliaria('A-02', 40.0);
$servicioEmision = new ServicioEmisionCobros();
$avisos = $servicioEmision->emitirLoteMensual('2026-10', 1000.0, 200.0, [$u1, $u2]);

$assertTrue(count($avisos) === 2, 'Deben emitirse 2 avisos de cobro.');
$assertTrue($avisos[0]->getTotalPagar() === 720.0, 'Cuota total A-01 debe ser 720.00.');
$assertTrue($avisos[1]->getTotalPagar() === 480.0, 'Cuota total A-02 debe ser 480.00.');
$assertTrue($u1->getSaldoPendiente() === 720.0, 'Saldo acumulado de A-01 debe ser 720.00.');

// 2. Caso de borde: suma de alicuotas distinta de 100%
$assertThrows(
    static function () use ($servicioEmision): void {
        $servicioEmision->emitirLoteMensual(
            '2026-10',
            1000.0,
            0.0,
            [new UnidadInmobiliaria('A-01', 50.0), new UnidadInmobiliaria('A-02', 30.0)]
        );
    },
    'Rechazar lote cuando las alicuotas no suman 100%.'
);

// 3. Caso de borde: monto cero o negativo en UnidadInmobiliaria
$assertThrows(
    static function () use ($u1): void {
        $u1->calcularCuotaPorAlicuota(0.0);
    },
    'Rechazar monto total cero al calcular cuota.'
);

// 4. Camino principal y borde: conciliacion de pago y exceso de saldo
$reporteValido = new ReportePago($avisos[0]->getNumeroAviso(), '48921033', '2026-10-24', 720.0, 'comprobante_pago.jpg');
$servicioConciliacion = new ServicioConciliacionPagos();
$servicioConciliacion->conciliarPago($reporteValido, $avisos[0], $u1);

$assertTrue($avisos[0]->getEstado() === AvisoDeCobro::ESTADO_PAGADO, 'Aviso debe pasar a PAGADO tras abono total.');
$assertTrue($u1->getSaldoPendiente() === 0.0, 'Saldo pendiente de A-01 debe quedar en 0.00.');
$assertTrue($reporteValido->getEstado() === ReportePago::ESTADO_CONCILIADO, 'Reporte debe quedar CONCILIADO.');

$assertThrows(
    static function () use ($reporteValido): void {
        new ReportePago('AV-2026-10-001', '123', '2026-10-24', 100.0, 'comp.jpg');
    },
    'Rechazar referencia bancaria con menos de 8 digitos.'
);

echo "Todas las pruebas del modulo Finanzas pasaron correctamente.\n";

<?php

declare(strict_types=1);

namespace SgProp\Finanzas;

use InvalidArgumentException;

final class ServicioConciliacionPagos
{
    public function conciliarPago(
        ReportePago $reporte,
        AvisoDeCobro $aviso,
        UnidadInmobiliaria $unidad
    ): void {
        if ($reporte->getNumeroAviso() !== $aviso->getNumeroAviso()) {
            throw new InvalidArgumentException('El reporte de pago no corresponde al aviso indicado.');
        }

        $monto = $reporte->getMontoReportado();

        if ($monto > $aviso->getSaldoPendiente()) {
            throw new InvalidArgumentException('El monto reportado supera el saldo pendiente del aviso.');
        }

        $aviso->registrarAbono($monto);
        $unidad->aplicarAbono($monto);
        $reporte->conciliar();
    }
}

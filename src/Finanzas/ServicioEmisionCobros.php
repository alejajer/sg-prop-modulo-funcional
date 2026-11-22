<?php

declare(strict_types=1);

namespace SgProp\Finanzas;

use InvalidArgumentException;

final class ServicioEmisionCobros
{
    /**
     * @param UnidadInmobiliaria[] $unidades
     * @return AvisoDeCobro[]
     */
    public function emitirLoteMensual(
        string $periodoMes,
        float $montoTotalMantenimiento,
        float $montoTotalFondoReserva,
        array $unidades
    ): array {
        if ($unidades === []) {
            throw new InvalidArgumentException('Debe indicar al menos una unidad inmobiliaria.');
        }

        if ($montoTotalMantenimiento <= 0.0) {
            throw new InvalidArgumentException('El monto total de mantenimiento debe ser mayor que cero.');
        }

        if ($montoTotalFondoReserva < 0.0) {
            throw new InvalidArgumentException('El fondo de reserva total no puede ser negativo.');
        }

        $sumaAlicuotas = 0.0;
        foreach ($unidades as $unidad) {
            if (!$unidad instanceof UnidadInmobiliaria) {
                throw new InvalidArgumentException('Todos los elementos deben ser instancias de UnidadInmobiliaria.');
            }
            $sumaAlicuotas += $unidad->getAlicuota();
        }

        if (abs(round($sumaAlicuotas, 2) - 100.0) > 0.01) {
            throw new InvalidArgumentException('La suma de alicuotas de las unidades debe equivaler al 100%.');
        }

        $avisosEmitidos = [];
        $correlativo = 1;

        foreach ($unidades as $unidad) {
            $cuotaMantenimiento = $unidad->calcularCuotaPorAlicuota($montoTotalMantenimiento);
            $cuotaFondoReserva = $montoTotalFondoReserva > 0.0
                ? $unidad->calcularCuotaPorAlicuota($montoTotalFondoReserva)
                : 0.0;

            $numeroAviso = sprintf('AV-%s-%03d', $periodoMes, $correlativo);
            $aviso = new AvisoDeCobro(
                $numeroAviso,
                $periodoMes,
                $cuotaMantenimiento,
                $cuotaFondoReserva
            );

            $unidad->acumularDeuda($aviso->getTotalPagar());
            $avisosEmitidos[] = $aviso;
            $correlativo++;
        }

        return $avisosEmitidos;
    }
}

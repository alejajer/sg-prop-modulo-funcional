<?php

declare(strict_types=1);

namespace SgProp\Finanzas;

use InvalidArgumentException;

final class UnidadInmobiliaria
{
    public function __construct(
        private readonly string $codigoUnidad,
        private readonly float $alicuota,
        private float $saldoPendiente = 0.0
    ) {
        if (trim($this->codigoUnidad) === '') {
            throw new InvalidArgumentException('El codigo de la unidad es obligatorio.');
        }

        if ($this->alicuota <= 0.0 || $this->alicuota > 100.0) {
            throw new InvalidArgumentException('La alicuota debe estar entre 0 y 100.');
        }
    }

    public function getCodigoUnidad(): string
    {
        return $this->codigoUnidad;
    }

    public function getAlicuota(): float
    {
        return $this->alicuota;
    }

    public function getSaldoPendiente(): float
    {
        return round($this->saldoPendiente, 2);
    }

    public function calcularCuotaPorAlicuota(float $montoTotalCondominio): float
    {
        if ($montoTotalCondominio <= 0.0) {
            throw new InvalidArgumentException('El monto total del condominio debe ser mayor que cero.');
        }

        return round(($montoTotalCondominio * $this->alicuota) / 100.0, 2);
    }

    public function acumularDeuda(float $monto): void
    {
        if ($monto <= 0.0) {
            throw new InvalidArgumentException('El monto a acumular debe ser mayor que cero.');
        }

        $this->saldoPendiente = round($this->saldoPendiente + $monto, 2);
    }

    public function aplicarAbono(float $monto): void
    {
        if ($monto <= 0.0) {
            throw new InvalidArgumentException('El monto del abono debe ser mayor que cero.');
        }

        if ($monto > $this->getSaldoPendiente()) {
            throw new InvalidArgumentException('El abono no puede superar el saldo pendiente.');
        }

        $this->saldoPendiente = round($this->saldoPendiente - $monto, 2);
    }
}

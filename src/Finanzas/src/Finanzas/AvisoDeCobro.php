<?php

declare(strict_types=1);

namespace SgProp\Finanzas;

use InvalidArgumentException;

final class AvisoDeCobro
{
    public const ESTADO_PENDIENTE = 'PENDIENTE';
    public const ESTADO_PAGADO = 'PAGADO';
    public const ESTADO_ANULADO = 'ANULADO';

    private string $estado;

    public function __construct(
        private readonly string $numeroAviso,
        private readonly string $periodoMes,
        private readonly float $montoMantenimiento,
        private readonly float $fondoReserva = 0.0,
        private float $saldoPendiente = 0.0
    ) {
        if (trim($this->numeroAviso) === '') {
            throw new InvalidArgumentException('El numero de aviso es obligatorio.');
        }

        if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $this->periodoMes)) {
            throw new InvalidArgumentException('El periodo debe tener formato YYYY-MM.');
        }

        if ($this->montoMantenimiento <= 0.0) {
            throw new InvalidArgumentException('El monto de mantenimiento debe ser mayor que cero.');
        }

        if ($this->fondoReserva < 0.0) {
            throw new InvalidArgumentException('El fondo de reserva no puede ser negativo.');
        }

        $totalCalculado = $this->getTotalPagar();
        $this->saldoPendiente = $this->saldoPendiente > 0.0
            ? round($this->saldoPendiente, 2)
            : $totalCalculado;
        $this->estado = $this->saldoPendiente === 0.0 ? self::ESTADO_PAGADO : self::ESTADO_PENDIENTE;
    }

    public function getNumeroAviso(): string
    {
        return $this->numeroAviso;
    }

    public function getPeriodoMes(): string
    {
        return $this->periodoMes;
    }

    public function getTotalPagar(): float
    {
        return round($this->montoMantenimiento + $this->fondoReserva, 2);
    }

    public function getSaldoPendiente(): float
    {
        return round($this->saldoPendiente, 2);
    }

    public function getEstado(): string
    {
        return $this->estado;
    }

    public function registrarAbono(float $montoAbonado): void
    {
        if ($this->estado === self::ESTADO_ANULADO) {
            throw new InvalidArgumentException('No se puede abonar a un aviso anulado.');
        }

        if ($montoAbonado <= 0.0) {
            throw new InvalidArgumentException('El monto abonado debe ser mayor que cero.');
        }

        if ($montoAbonado > $this->getSaldoPendiente()) {
            throw new InvalidArgumentException('El abono excede el saldo pendiente del aviso.');
        }

        $this->saldoPendiente = round($this->saldoPendiente - $montoAbonado, 2);

        if ($this->saldoPendiente === 0.0) {
            $this->estado = self::ESTADO_PAGADO;
        }
    }

    public function anular(): void
    {
        if ($this->estado === self::ESTADO_PAGADO) {
            throw new InvalidArgumentException('No se puede anular un aviso ya pagado.');
        }

        $this->estado = self::ESTADO_ANULADO;
    }
}

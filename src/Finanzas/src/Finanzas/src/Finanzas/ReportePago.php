<?php

declare(strict_types=1);

namespace SgProp\Finanzas;

use InvalidArgumentException;

final class ReportePago
{
    public const ESTADO_EN_REVISION = 'EN_REVISION';
    public const ESTADO_CONCILIADO = 'CONCILIADO';
    public const ESTADO_RECHAZADO = 'RECHAZADO';

    private string $estado;

    public function __construct(
        private readonly string $numeroAviso,
        private readonly string $referenciaBancaria,
        private readonly string $fechaTransaccion,
        private readonly float $montoReportado,
        private readonly string $comprobanteUrl
    ) {
        if (trim($this->numeroAviso) === '') {
            throw new InvalidArgumentException('El numero de aviso asociado es obligatorio.');
        }

        if (!preg_match('/^\d{8,20}$/', $this->referenciaBancaria)) {
            throw new InvalidArgumentException('La referencia bancaria debe contener entre 8 y 20 digitos.');
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $this->fechaTransaccion)) {
            throw new InvalidArgumentException('La fecha de transaccion debe tener formato YYYY-MM-DD.');
        }

        if ($this->montoReportado <= 0.0) {
            throw new InvalidArgumentException('El monto reportado debe ser mayor que cero.');
        }

        if (trim($this->comprobanteUrl) === '') {
            throw new InvalidArgumentException('El comprobante digital es obligatorio.');
        }

        $this->estado = self::ESTADO_EN_REVISION;
    }

    public function getNumeroAviso(): string
    {
        return $this->numeroAviso;
    }

    public function getReferenciaBancaria(): string
    {
        return $this->referenciaBancaria;
    }

    public function getMontoReportado(): float
    {
        return round($this->montoReportado, 2);
    }

    public function getEstado(): string
    {
        return $this->estado;
    }

    public function conciliar(): void
    {
        if ($this->estado !== self::ESTADO_EN_REVISION) {
            throw new InvalidArgumentException('Solo se pueden conciliar reportes en revision.');
        }

        $this->estado = self::ESTADO_CONCILIADO;
    }

    public function rechazar(string $motivo): void
    {
        if (trim($motivo) === '') {
            throw new InvalidArgumentException('Debe indicar el motivo del rechazo.');
        }

        $this->estado = self::ESTADO_RECHAZADO;
    }
}

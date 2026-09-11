<?php

namespace App\Enums;

enum EstadoPedido: string
{
    case PENDIENTE = 'pendiente';
    case PAGADO = 'pagado';
    case ENVIADO = 'enviado';
    case ENTREGADO = 'entregado';
    case CANCELADO = 'cancelado';

    public function label(): string
    {
        return match ($this) {
            self::PENDIENTE => 'Pendiente',
            self::PAGADO => 'Pagado',
            self::ENVIADO => 'Enviado',
            self::ENTREGADO => 'Entregado',
            self::CANCELADO => 'Cancelado',
        };
    }
}

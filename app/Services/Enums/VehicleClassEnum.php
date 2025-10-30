<?php

namespace App\Services\Enums;

enum VehicleClassEnum: string
{
    case Standard = "Стандарт";
    case Comfort = 'Комфорт';
    case Business = 'Бизнес';
    case Representative = 'Представительский';
    case RepresentativeW223 = 'Представительский (W223)';
    case Minivan = 'Минивэн';
    case Minibus = 'Микроавтобус';
    case RepresentativeMaybach = 'Maybach';
    case MinivanVIP = 'Минивэн VIP';
    case Bus30 = 'Автобус (30 мест)';
    case Bus45 = 'Автобус (45 мест)';
    case Bus50 = 'Автобус (50 мест)';
    case BusVIP = 'Автобус ВИП';

    public static function byId(int $id): VehicleClassEnum {
        return match ($id) {
            2 => self::Comfort,
            3 => self::Business,
            4 => self::Representative,
            5 => self::RepresentativeW223,
            6 => self::Minivan,
            7 => self::Minibus,
            11 => self::RepresentativeMaybach,
            12 => self::MinivanVIP,
            14 => self::Bus30,
            15 => self::Bus45,
            16 => self::Bus50,
            18 => self::BusVIP,
            default => self::Standard,
        };
    }
}


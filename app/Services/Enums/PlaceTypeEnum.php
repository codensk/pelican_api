<?php

namespace App\Services\Enums;

enum PlaceTypeEnum: string
{
    case Address = 'address';
    case Airport = 'airport';
    case RailwayStation = 'railway_station';

    public static function byId(?int $id): PlaceTypeEnum {
        return match ($id) {
            5 => self::Airport,
            6 => self::RailwayStation,
            default => self::Address,
        };
    }
}

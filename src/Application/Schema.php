<?php

declare(strict_types=1);

namespace FlightHub\Application;

use Prooph\EventMachine\JsonSchema\JsonSchema;
use Prooph\EventMachine\JsonSchema\Type\ArrayType;
use Prooph\EventMachine\JsonSchema\Type\IntType;
use Prooph\EventMachine\JsonSchema\Type\StringType;
use Prooph\EventMachine\JsonSchema\Type\TypeRef;
use Prooph\EventMachine\JsonSchema\Type\UuidType;

class Schema
{
    public static function id(): UuidType
    {
        return JsonSchema::uuid();
    }

    public static function flightNumber(): StringType
    {
        return JsonSchema::string(['pattern' => '^[A-Z0-9]{3,}$']);
    }

    public static function seat(): StringType
    {
        return JsonSchema::string(['pattern' => '[0-9]{1,2}[A-Z]{1}$']);
    }

    public static function flightNumberFilter(): StringType
    {
        return JsonSchema::string()->withMinLength(1);
    }

    public static function flight(): TypeRef
    {
        return JsonSchema::typeRef(Query::FLIGHT);
    }

    public static function flightList(): ArrayType
    {
        return JsonSchema::array(self::flight());
    }

    public static function version(): IntType
    {
        return JsonSchema::integer()->withMinimum(1);
    }

    public static function healthCheck(): TypeRef
    {
        return JsonSchema::typeRef(Query::HEALTH_CHECK);
    }

    public static function queryPagination(): array
    {
        return [
            Payload::SKIP => JsonSchema::nullOr(JsonSchema::integer(['minimum' => 0])),
            Payload::LIMIT => JsonSchema::nullOr(JsonSchema::integer(['minimum' => 1])),
        ];
    }

    public static function iso8601DateTime(): StringType
    {
        return JsonSchema::string()->withPattern('^\d{4}-\d\d-\d\dT\d\d:\d\d:\d\d(\.\d+)?(([+-]\d\d:\d\d)|Z)?$');
    }
    
    public static function iso8601Date(): StringType
    {
        return JsonSchema::string()->withPattern('^\d{4}-\d\d-\d\d$');
    }
}

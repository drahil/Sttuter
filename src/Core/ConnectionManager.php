<?php

namespace Stutter\Core;

class ConnectionManager
{
    private static array $connections = [];
    private static ?string $defaultConnection = null;

    public static function addConnection(array $config, string $name = 'default'): void
    {
        self::$connections[$name] = new Connection($config);

        if (self::$defaultConnection === null) {
            self::$defaultConnection = $name;
        }
    }

    public static function getConnection(?string $name = null): Connection
    {
        $name = $name ?? self::$defaultConnection;

        if (!isset(self::$connections[$name])) {
            throw new \RuntimeException("Connection [$name] not configured.");
        }

        return self::$connections[$name];
    }
}
<?php

namespace drahil\Stutter\Core;

use drahil\Stutter\Exceptions\QueryException;
use InvalidArgumentException;
use PDO;
use PDOException;
use RuntimeException;

class Connection
{
    private PDO $pdo;
    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->connect();
    }

    private function connect(): void
    {
        try {
            $dsn = $this->buildDsn();
            $this->pdo = new PDO(
                $dsn,
                $this->config['username'] ?? '',
                $this->config['password'] ?? '',
                $this->config['options'] ?? [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch (PDOException $e) {
            throw new RuntimeException("Database connection failed: {$e->getMessage()}");
        }
    }

    private function buildDsn(): string
    {
        $driver = $this->config['driver'] ?? 'mysql';

        return match ($driver) {
            'mysql' => "mysql:host={$this->config['host']};dbname={$this->config['database']};",
            'pgsql' => "pgsql:host={$this->config['host']};dbname={$this->config['database']};user={$this->config['username']};password={$this->config['password']}",

            // Add other drivers as needed
            default => throw new InvalidArgumentException("Unsupported driver: {$driver}")
        };
    }

    public function query(string $sql, array $bindings = []): array
    {
        try {
            $statement = $this->pdo->prepare($sql);
            $statement->execute($bindings);
            return $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new QueryException($sql, $bindings, $e);
        }
    }

    public function execute(string $sql, array $bindings = []): int
    {
        try {
            $statement = $this->pdo->prepare($sql);
            $statement->execute($bindings);
            return $statement->rowCount();
        } catch (PDOException $e) {
            throw new QueryException($sql, $bindings, $e);
        }
    }

    public function lastInsertId(): string
    {
        return $this->pdo->lastInsertId();
    }

    public function beginTransaction(): bool
    {
        return $this->pdo->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->pdo->commit();
    }

    public function rollBack(): bool
    {
        return $this->pdo->rollBack();
    }

}
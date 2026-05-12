<?php
/**
 * QueryBuilder — SQL Injection Protection Utility
 * Builds parameterized WHERE clauses automatically.
 *
 * Usage:
 *   $qb = new QueryBuilder();
 *   $qb->eq('C.`department`', $params["department"])
 *      ->gte('DATE(C.`dayCheckIn`)', $params["dateFrom"]);
 *   $result = $qb->execute($db, $baseSql, 'ORDER BY id DESC')->fetchAll();
 */

class QueryBuilder
{
    private string $where = '';
    private array $binds = [];

    /**
     * Equal condition: column = ?
     */
    public function eq(string $column, $value): self
    {
        if ($value !== null && $value !== '') {
            $this->where .= " AND {$column} = ?";
            $this->binds[] = $value;
        }
        return $this;
    }

    /**
     * Greater than or equal: column >= ?
     */
    public function gte(string $column, $value): self
    {
        if ($value !== null && $value !== '') {
            $this->where .= " AND {$column} >= ?";
            $this->binds[] = $value;
        }
        return $this;
    }

    /**
     * Less than or equal: column <= ?
     */
    public function lte(string $column, $value): self
    {
        if ($value !== null && $value !== '') {
            $this->where .= " AND {$column} <= ?";
            $this->binds[] = $value;
        }
        return $this;
    }

    /**
     * LIKE condition: column LIKE ?
     */
    public function like(string $column, $value): self
    {
        if ($value !== null && $value !== '') {
            $this->where .= " AND {$column} LIKE ?";
            $this->binds[] = '%' . $value . '%';
        }
        return $this;
    }

    /**
     * IN condition: column IN (?, ?, ...)
     */
    public function in(string $column, array $values): self
    {
        $values = array_filter($values, fn($v) => $v !== null && $v !== '');
        if (!empty($values)) {
            $placeholders = implode(',', array_fill(0, count($values), '?'));
            $this->where .= " AND {$column} IN ({$placeholders})";
            foreach ($values as $v) {
                $this->binds[] = $v;
            }
        }
        return $this;
    }

    /**
     * Raw condition without user input (for fixed conditions)
     * e.g. ->raw("AND w.wSystemAmelia = 1")
     */
    public function raw(string $condition): self
    {
        $this->where .= " " . $condition;
        return $this;
    }

    /**
     * Get WHERE clause string
     */
    public function getWhere(): string
    {
        return $this->where;
    }

    /**
     * Get bind values array
     */
    public function getBinds(): array
    {
        return $this->binds;
    }

    /**
     * Execute query via $db->query()
     * @param object $db       DB instance
     * @param string $baseSql  Base SQL (should include WHERE 1=1 or similar)
     * @param string $suffix   ORDER BY / LIMIT clause
     * @return object          DB query result (chain ->fetchAll() or ->fetchArray())
     */
    public function execute($db, string $baseSql, string $suffix = ''): object
    {
        $sql = $baseSql . $this->where;
        if (!empty($suffix)) {
            $sql .= ' ' . $suffix;
        }

        if (!empty($this->binds)) {
            return $db->query($sql, $this->binds);
        }
        return $db->query($sql);
    }

    /**
     * Reset for reuse
     */
    public function reset(): self
    {
        $this->where = '';
        $this->binds = [];
        return $this;
    }
}

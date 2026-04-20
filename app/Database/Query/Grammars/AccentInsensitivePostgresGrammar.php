<?php

namespace App\Database\Query\Grammars;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Grammars\PostgresGrammar;

class AccentInsensitivePostgresGrammar extends PostgresGrammar
{
    protected function whereBasic(Builder $query, $where): string
    {
        if (str_contains(strtolower((string) $where['operator']), 'like')) {
            $operator = str_ireplace('ilike', 'like', (string) $where['operator']);

            return sprintf(
                'unaccent(lower(%s::text)) %s unaccent(lower(%s::text))',
                $this->wrap($where['column']),
                $operator,
                $this->parameter($where['value'])
            );
        }

        return parent::whereBasic($query, $where);
    }
}


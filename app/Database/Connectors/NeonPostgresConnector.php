<?php

namespace App\Database\Connectors;

use Illuminate\Database\Connectors\PostgresConnector;

class NeonPostgresConnector extends PostgresConnector
{
    /**
     * Add SSL and Neon-specific options to the DSN.
     *
     * @param  string  $dsn
     * @param  array  $config
     * @return string
     */
    protected function addSslOptions($dsn, array $config)
    {
        $dsn = parent::addSslOptions($dsn, $config);

        if (isset($config['endpoint'])) {
            $endpoint = str_replace("'", "\\'", (string) $config['endpoint']);
            $dsn .= ";options='endpoint={$endpoint}'";
        }

        return $dsn;
    }
}

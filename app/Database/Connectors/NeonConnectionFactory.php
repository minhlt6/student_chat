<?php

namespace App\Database\Connectors;

use Illuminate\Database\Connectors\ConnectionFactory;

class NeonConnectionFactory extends ConnectionFactory
{
    /**
     * Create a connector instance based on the config.
     *
     * @param  array  $config
     * @return mixed
     */
    public function createConnector(array $config)
    {
        if (($config['driver'] ?? null) === 'pgsql') {
            return new NeonPostgresConnector;
        }

        return parent::createConnector($config);
    }
}

<?php

namespace Libelula\ErrorHandler\interfaces;

interface ConfigRecord
{

    public function getConfig(string $code): array;
}

<?php

declare(strict_types=1);

namespace Settermjd\Mezzio\Twilio\Exception;

use Exception;
use Psr\Container\NotFoundExceptionInterface;

class InvalidConfigException extends Exception implements NotFoundExceptionInterface
{
}

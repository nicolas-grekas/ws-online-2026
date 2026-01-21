<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag]
interface FooInterface
{
    public function doSomething(): void;
}

<?php

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    public function getCacheDir(): string
    {
        return str_replace('core','',dirname(__DIR__)).'cache/'.$this->environment.'/cache';
    }

    public function getLogDir(): string
    {
        return str_replace('core','',dirname(__DIR__)).'logs/'.$this->environment.'/log';
    }
}

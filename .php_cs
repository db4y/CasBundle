<?php

$finder = Symfony\CS\Finder::create()
    ->files()
    ->name('*.php')
    ->in(__DIR__)
;

return Symfony\CS\Config::create()
    ->setUsingCache(true)
    ->level(Symfony\CS\FixerInterface::SYMFONY_LEVEL)
    ->finder($finder)
;

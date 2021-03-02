<?php


use Composer\Script\Event;
use Composer\Installer\PackageEvent;

class UserScript
{
    public static function postPackageInstall(PackageEvent $event)
    {
        mkdir("asdass");
        
    }
}
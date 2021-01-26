<?php

namespace Framework\Modules;

class InsiderFrameworkTestingMain {
    public static function it(String $testName, callable $callback){
        echo "\r\n";
        echo "Testing: ".$testName;
        echo "\r\n";
        $callback();
    }      
}
<?php

function it(String $testName, callable $callback){
  echo "Testing: ".$testName;
  $callback();
}

it("Should run test", function () {
  echo "teste";
});
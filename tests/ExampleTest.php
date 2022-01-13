<?php

namespace tests;

class ExampleTest extends TestCase
{

  public function testBasicExample()
  {
    $this->visit('/')->see('ThinkPHP');
  }
}

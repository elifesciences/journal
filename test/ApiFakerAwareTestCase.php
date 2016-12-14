<?php

namespace test\eLife\Journal;

use eLife\ApiFaker\Factory;

trait ApiFakerAwareTestCase
{
    protected $faker;

    /**
     * @before
     */
    final public function setUpFaker()
    {
        $this->faker = Factory::create();

        $this->faker->seed('g123he1j1h12');
    }
}

<?php
/**
 * Copyright (c) 2019 TASoft Applications, Th. Abplanalp <info@tasoft.ch>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

/**
 * ArgumentReferencesTest.php
 * Event Manager
 *
 * Created on 2019-05-28 21:33 by thomas
 */

use PHPUnit\Framework\TestCase;
use TASoft\EventManager\Event\Event;
use TASoft\EventManager\EventManager;
use TASoft\EventManager\EventManagerInterface;

class ArgumentReferencesTest extends TestCase
{
    public function testArgumentReferences() {
        $em = new EventManager();
        $em->addListener("my.event", $m1 = new MockReference(), 10);

        $test = false;
        $event = new Event();
        $em->trigger("my.event", $event, [true, false, &$test]);

        $this->assertEquals([
            "my.event",
            $event,
            $em,
            true,
            false,
            false
        ], $m1->arguments);
        $this->assertEquals("Reached", $test);
    }
}

class MockReference {
    public $arguments;
    public function __invoke(string $eventName, Event $event, EventManagerInterface $eventManager, $argument1, $argument2, &$argument3)
    {
        $this->arguments = func_get_args();
        $argument3 = "Reached";
    }
}


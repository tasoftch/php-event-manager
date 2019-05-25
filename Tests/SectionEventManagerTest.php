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
 * SectionEventManagerTest.php
 * Event Manager
 *
 * Created on 2019-05-25 09:39 by thomas
 */

use PHPUnit\Framework\TestCase;
use TASoft\EventManager\EventManager;
use TASoft\EventManager\SectionEventManager;

/**
 * Class SectionEventManagerTest
 */
class SectionEventManagerTest extends TestCase
{
    public function testDefaultManager() {
        $sm = new SectionEventManager();
        $sm->addListener("my.event", $m1 = new STriggerMarker());
        $evt = $sm->trigger("my.event");
        $this->assertFalse($evt->isPropagationStopped());

        $this->assertEquals(0, $m1->index);
    }

    public function testDefaultWithSection() {
        $sm = new SectionEventManager();
        $sm->addListener("event", $m1 = new STriggerMarker());

        $sm2 = new EventManager();
        $sm2->addListener("event", $m2 = new STriggerMarker());

        $sm->addSectionEventManager("event", $sm2);

        $smr = new SectionEventManager();
        $smr->addSectionEventManager("my", $sm);



        $evt = $smr->trigger("my.event");
        $this->assertFalse($evt->isPropagationStopped());

        $this->assertEquals(1, $m1->index);

        $evt = $smr->trigger("my.event.event");
        $this->assertFalse($evt->isPropagationStopped());

        $this->assertEquals(2, $m2->index);
    }
}


class STriggerMarker {
    public $arguments;
    public $index = -1;

    public function __invoke()
    {
        $this->arguments = func_get_args();
        static $index = 0;
        $this->index = $index++;
    }
}
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
 * EventManagerTest.php
 * php-event-manager
 *
 * Created on 2019-04-13 10:04 by thomas
 */

use PHPUnit\Framework\TestCase;
use TASoft\EventManager\Event\EventInterface;
use TASoft\EventManager\EventManager;
use TASoft\EventManager\EventSubscriberInterface;
use TASoft\EventManager\SubscribableEventManager;

class EventManagerTest extends TestCase
{
    public function testAddListeners() {
        $em = new EventManager();
        $em->addListener("my.event", "strlen", 10);
        $em->addListener("my.event", "strcasecmp", -2);
        $em->addListener("my.event", "count", 5);
        $em->addListener("my.event", "array_search", -5);
        $em->addListener("my.event", "file", -3);
        $em->addListener("my.event", "is_dir", 5);

        $this->assertEquals([
            "array_search",
            "file",
            "strcasecmp",
            "count",
            "is_dir",
            "strlen"
        ], $em->getListeners("my.event"));
        $this->assertEquals([], $em->getListeners("no.listeners"));
    }

    public function testRemoveListeners() {
        $em = new EventManager();
        $em->addListener("my.event", "strlen", 10);
        $em->addListener("my.event", "array_search", -5);
        $em->addListener("my.event", "is_dir", 5);

        $em->addListener("my.test", "strlen", -2);
        $em->addListener("my.test", "array_search", 5);
        $em->addListener("my.test", "is_dir", -3);

        $em->removeListener("strlen");
        $this->assertCount(2, $em->getListeners("my.test"));
        $this->assertCount(2, $em->getListeners("my.event"));

        $em->removeListener("is_dir", "my.test");

        $this->assertCount(1, $em->getListeners("my.test"));
        $this->assertCount(2, $em->getListeners("my.event"));
    }

    public function testRemoveAllListeners() {
        $em = new EventManager();
        $em->addListener("my.event", "strlen", 10);
        $em->addListener("my.event", "array_search", -5);
        $em->addListener("my.event", "is_dir", 5);

        $em->addListener("my.test", "strlen", -2);
        $em->addListener("my.test", "array_search", 5);
        $em->addListener("my.test", "is_dir", -3);

        $em->removeAllListeners("my.test");
        $this->assertCount(0, $em->getListeners("my.test"));
        $this->assertCount(3, $em->getListeners("my.event"));
    }

    public function testRemoveAllListeners2() {
        $em = new EventManager();
        $em->addListener("my.event", "strlen", 10);
        $em->addListener("my.event", "array_search", -5);
        $em->addListener("my.event", "is_dir", 5);

        $em->addListener("my.test", "strlen", -2);
        $em->addListener("my.test", "array_search", 5);
        $em->addListener("my.test", "is_dir", -3);

        $em->removeAllListeners();
        $this->assertCount(0, $em->getListeners("my.test"));
        $this->assertCount(0, $em->getListeners("my.event"));
    }

    public function testSimpleEventTrigger() {
        $em = new EventManager();
        $em->addListener("my.event", $m1 = new TriggerMarker(), 10);
        $em->addListener("my.event", $m2 = new TriggerMarker(), -5);
        $em->addListener("my.event", $m3 = new TriggerMarker(), 5);

        $this->assertFalse(($ev = $em->trigger("my.event"))->isPropagationStopped());

        $this->assertEquals(0, $m2->index);
        $this->assertEquals(1, $m3->index);
        $this->assertEquals(2, $m1->index);

        $this->assertEquals([
            "my.event",
            $ev,
            $em
        ], $m1->arguments);
    }

    public function testArgumentForwarder() {
        $em = new EventManager();
        $em->addListener("my.event", $m1 = new TriggerMarker(), 10);

        $ev = $em->trigger("my.event", NULL, 14, "Thomas");
        $this->assertEquals([
            "my.event",
            $ev,
            $em,
            14,
            "Thomas"
        ], $m1->arguments);
    }

    public function testEventNamesAssignment() {
        $em = new EventManager();
        $em->addListener("my.event", $m1 = new TriggerMarker(), 10);
        $em->addListener("my.test", $m2 = new TriggerMarker(), -5);
        $em->addListener("my.event", $m3 = new TriggerMarker(), 5);

        $em->trigger("my.event");
        $this->assertEquals(-1, $m2->index);
        $this->assertGreaterThan(0, $m1->index);
        $this->assertGreaterThan(0, $m3->index);
    }

    public function testOnceListener() {
        $em = new EventManager();
        $em->addOnce("my.test", function() use (&$arguments) {
            $arguments = func_get_args();
        }, 200);

        $ev = $em->trigger("my.test", NULL, 14, "Ha");
        $this->assertEquals([
            "my.test",
            $ev,
            $em,
            14,
            "Ha"
        ], $arguments);

        $arguments = NULL;
        $em->trigger("my.test", NULL, 14, "Ha");

        $this->assertNull($arguments);
    }

    public function testStopPropagation() {
        $em = new EventManager();
        $em->addListener("my.event", $m1 = new TriggerMarker(), 10);
        $em->addListener("my.event", $m2 = new TriggerMarker(), -5);
        $em->addListener("my.event", function ($en, EventInterface $ev) {
            $ev->stopPropagation();
        }, 5);

        $em->trigger("my.event");


        $this->assertEquals(-1, $m1->index);
        $this->assertGreaterThan(0, $m2->index);
    }

    public function testSubscriber() {
        $em = new SubscribableEventManager();

        $this->assertTrue($em->subscribeClass(SubscriberClass::class));
        $this->assertFalse(SubscriberClass::$hits);

        $em->trigger("myEvent");
        $this->assertTrue(SubscriberClass::$hits);
    }
}

class TriggerMarker {
    public $arguments;
    public $index = -1;

    public function __invoke()
    {
        $this->arguments = func_get_args();
        static $index = 0;
        $this->index = $index++;
    }
}

class SubscriberClass implements EventSubscriberInterface {
    public static $hits = false;

    public static function getEventListeners(): array
    {
        return [
            [13, 'myEvent', [self::class, 'doStuff']]
        ];
    }

    public static function doStuff() {
        self::$hits = true;
    }
}

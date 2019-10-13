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

namespace TASoft\EventManager;

use TASoft\Collection\PriorityCollection;
use TASoft\EventManager\Event\Event;
use TASoft\EventManager\Event\EventInterface;
use TASoft\EventManager\Listener\EventNameAwareInterface;

/**
 * Trait EventManagerTrait provides an event manager to every object that needs.
 * @package TASoft\EventManager
 */
trait EventManagerTrait
{
    protected static $eventStack = [];

    /**
     * @var PriorityCollection[]
     */
    protected $listeners = [];
    protected $allowGlobalEventListeners = false;

    /**
     * Add a listener to the event manager.
     * The lowest priority number will be called first.
     *
     * @param string $eventName
     * @param callable $listener
     * @param int $priority
     * @return static
     */
    public function addListener(string $eventName, callable $listener, int $priority = 0) {
        if($listener instanceof EventNameAwareInterface) {
            $eventName = !is_null($n = $listener->getEventName()) ? $n : $eventName;
            $priority = !is_null($n = $listener->getPriority()) ? $n : $priority;
        }

        if(!isset($this->listeners[$eventName])) {
            $this->listeners[$eventName] = new PriorityCollection($priority, [$listener]);
        } else {
            $this->listeners[$eventName]->add($priority, $listener);
        }
        return $this;
    }

    /**
     * Add a listener to chain that listens only once
     *
     * @param string $eventName
     * @param callable $listener
     * @param int $priority
     * @return static
     */
    public function addOnce(string $eventName, callable $listener, int $priority = 0) {
        if($listener instanceof EventNameAwareInterface) {
            $eventName = !is_null($n = $listener->getEventName()) ? $n : $eventName;
            $priority = !is_null($n = $listener->getPriority()) ? $n : $priority;
        }

        $l = function($eventName, $event, $manager, ...$arguments) use ($listener, &$l) {
            $this->removeListener($l, $eventName);
            call_user_func($listener, $eventName, $event, $manager, ...$arguments);
        };
        return $this->addListener($eventName, $l, $priority);
    }

    /**
     * Remove a listener from event manager.
     * If eventName is specified, the listener is only removed from that event, otherwise from all events.
     *
     * @param $listener
     * @param string|NULL $eventName
     * @return static
     */
    public function removeListener($listener, string $eventName = NULL) {
        if($eventName) {
            if($list = $this->listeners[$eventName] ?? NULL) {
                $list->remove($listener);
            }
        } else {
            foreach($this->listeners as &$list) {
                $list->remove($listener);
            }
        }
        return $this;
    }

    /**
     * Remove all listeners.
     * If eventName is specified, it removes only for that event, otherwise all.
     *
     * @param string|NULL $eventName
     * @return static
     */
    public function removeAllListeners(string $eventName = NULL) {
        if($eventName) {
            if(isset($this->listeners[$eventName]))
                unset($this->listeners[$eventName]);
        } else {
            $this->listeners = [];
        }
        return $this;
    }

    /**
     * Sort the event listeners if needed and return them ordered.
     *
     * @param string $eventName
     * @return array
     */
    public function getListeners(string $eventName): array {
        if (!isset($this->listeners[$eventName])) {
            return [];
        }

        return $this->listeners[$eventName]->getOrderedElements();
    }

    /**
     * Triggers an event and pass all listeners
     *
     * The trigger calls the following callback signature:
     * function(string $eventName, EventInterface $event, $eventManager, ...$arguments)
     * Note that trigger("event", NULL, [$arg1, $arg2, &$arg3]) can be used also. In that case, arguments are forwarded by references. So if the called method uses func($eventName, $event, $manager, $arg1, $arg2, &$arg3) the $arg3 is still the reference.
     *
     * @param string $eventName
     * @param EventInterface|NULL $event
     * @param mixed[]|array $arguments
     * @return EventInterface
     */
    public function trigger(string $eventName, EventInterface $event = NULL, ...$arguments): EventInterface {
        if(!$event) {
            $event = new Event();
        }

        $call = function($listener) use ($eventName, $event, $arguments) {
            if(count($arguments) == 1 && is_array($arguments[0])) {
                $args = [$eventName, $event, $this];
                foreach($arguments[0] as &$value)
                    $args[] =& $value;
                call_user_func_array($listener, $args);
            } else
                call_user_func($listener, $eventName, $event, $this, ...$arguments);
        };

        static::$eventStack[] = $event;

        if($this->allowGlobalEventListeners) {
            foreach($this->getListeners("") as $listener) {
                $call($listener);
                if($event->isPropagationStopped())
                    return $event;
            }
        }

        foreach($this->getListeners($eventName) as $listener) {
            $call($listener);
            if($event->isPropagationStopped())
                break;
        }

        array_pop(static::$eventStack);

        return $event;
    }

    public static function getCurrentEvent(): ?EventInterface {
        return end( static::$eventStack );
    }
}
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

namespace TASoft\EventManager\Listener;

use TASoft\EventManager\Event\EventInterface;
use TASoft\EventManager\EventManager;
use TASoft\EventManager\EventManagerInterface;

/**
 * This event listener is able to decide if it wants to handle the event or not.
 * @package TASoft\EventManager\Listener
 */
abstract class AbstractAwareListener implements EventNameAwareInterface, EventListenerInterface
{
    /**
     * If this listener is able to handle the triggered event, return a method name to forward the event. Otherwise return null
     *
     * @param string $eventName
     * @param EventInterface $event
     * @param EventManagerInterface $eventManager
     * @param mixed ...$arguments
     * @return string|null
     */
    public abstract function acceptEvent(string $eventName, EventInterface $event, EventManagerInterface $eventManager, ...$arguments): ?string;

    /**
     * Default implementation to forward an event to a designated handler
     *
     * @param string $eventName
     * @param EventInterface $event
     * @param EventManagerInterface $eventManager
     * @param mixed ...$arguments
     */
    public function __invoke(string $eventName, EventInterface $event, EventManagerInterface $eventManager, ...$arguments)
    {
        if(is_string($cbl = $this->acceptEvent($eventName, $event, $eventManager, ...$arguments)) && method_exists($this, $cbl)) {
            call_user_func([$this, $cbl], $eventName, $event, $eventManager, ...$arguments);
        }
    }

    /**
     * @inheritDoc
     */
    public function getEventName(): ?string
    {
        return EventManager::GLOBAL_EVENT_NAME;
    }

    /**
     * @inheritDoc
     */
    public function getPriority(): ?int
    {
        return NULL;
    }
}
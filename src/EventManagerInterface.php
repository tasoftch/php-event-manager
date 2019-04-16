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

use TASoft\EventManager\Event\EventInterface;

/**
 * Interface EventManagerInterface
 * @package TASoft\EventManager
 */
interface EventManagerInterface
{
    /**
     * @param string $eventName
     * @param callable $listener
     * @param int $priority
     * @return self
     */
    public function addListener(string $eventName, callable $listener, int $priority = 0);

    /**
     * Triggers an event and pass all listeners
     *
     * The trigger calls the following callback signature:
     * function(string $eventName, EventInterface $event, $eventManager, ...$arguments)
     *
     * @param string $eventName
     * @param EventInterface|NULL $event
     * @param mixed $arguments
     * @return EventInterface
     */
    public function trigger(string $eventName, EventInterface $event = NULL, ...$arguments): EventInterface;
}
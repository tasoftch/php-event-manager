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
use TASoft\EventManager\EventManagerInterface;

/**
 * The dynamic event listener is designed to be only initialized completely if an event was accepted
 * @package TASoft\EventManager\Listener
 */
abstract class AbstractDynamicListener extends AbstractAwareListener
{
    /** @var bool */
    private $initialized;

    /**
     * @return bool
     */
    public function isInitialized(): bool
    {
        return $this->initialized;
    }

    /**
     * @return mixed
     */
    abstract protected function initialize();

    /**
     * @inheritDoc
     */
    public function __invoke(string $eventName, EventInterface $event, EventManagerInterface $eventManager, ...$arguments)
    {
        if(is_string($cbl = $this->acceptEvent($eventName, $event, $eventManager, ...$arguments)) && method_exists($this, $cbl)) {
            if(!$this->isInitialized()) {
                $this->initialize();
                $this->initialized = true;
            }
            call_user_func([$this, $cbl], $eventName, $event, $eventManager, ...$arguments);
        }
    }
}
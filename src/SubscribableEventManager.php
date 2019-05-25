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

/**
 * The subscribable event manager allows classes to declare event listeners themselves.
 * For performance and flexibility reasons the event manager uses subscriber handlers to definitively add the listeners.
 *
 * @package TASoft\EventManager
 */
class SubscribableEventManager extends EventManager
{
    private $subscriberHandlers;

    /**
     * EventManager constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->subscriberHandlers = new PriorityCollection();

        // Add default subscriütion handler accepting: array( int <priority>, string <eventname>, callable <everything that call_user_func can handle>)
        $this->subscriberHandlers->add(0, function($sub) {
            if(is_array($sub)) {
                $priority = array_shift($sub);
                $eventName = array_shift($sub);
                $callable = array_shift($sub);
                if(is_numeric($priority) && is_string($eventName) && (is_callable($callable) || is_array($callable))) {
                    $this->addListener($eventName, $callable, $priority);
                    return true;
                }
            }
            return false;
        });
    }

    /**
     * Adds a subscriber handler.
     * This handlers are responsable to register listeners from a subscriber class.
     *
     * @param callable $handler     The handler MUST return true, if it could add a listener or false if not. It will receive the subscription and the event manager. ex: [bool] function ( [mixed] $subscription, [SubscribableEventManager] $eventManager )
     * @param int $priority
     */
    public function addSubscriberHandler(callable $handler, int $priority = 0) {
        $this->subscriberHandlers->add($priority, $handler);
    }

    /**
     * Remove a subscriber handler
     *
     * @param $handler
     */
    public function removeSubscriberHandler($handler) {
        $this->subscriberHandlers->remove($handler);
    }

    /**
     * Calls the subscriber method of the passed class and adds the resulting event listeners
     *
     * @param string $className
     * @return bool
     */
    public function subscribeClass(string $className): bool {
        if(method_exists($className, 'getEventListeners')) {
            $subscribers = $className::getEventListeners();

            if($subscribers) {
                $handle = function($subscriber) {
                    foreach($this->subscriberHandlers->getOrderedElements() as $handler) {
                        if($handler($subscriber, $this))
                            return true;
                    }
                    return false;
                };

                foreach($subscribers as $idx => $subscriber) {
                    if(!$handle($subscriber))
                        trigger_error("Could not add event listeners for subscription #$idx of class $className", E_USER_WARNING);
                }
                return true;
            }
        }
        return false;
    }
}
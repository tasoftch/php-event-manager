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
 * The SectionEventManager is designed to split an event into several sections. In those sections different event listeners may be registered.
 * The Manager splits events by dots(.). So an event named SECTION.SUB_SECTION.EVENT_NAME is directed to the SECTION event manager, then to the SUB_SECTION event manager and then triggered as EVENT_NAME
 *
 * @package TASoft\EventManager\Section
 */
class SectionEventManager implements EventManagerInterface
{
    /** @var EventManagerInterface[] */
    private $sectionEventManagers = [];

    use EventManagerTrait {
        trigger AS _t_trigger;
    }

    /**
     * Defines an event manager to forward events matching section.
     *
     * @param string $sectionName
     * @param EventManagerInterface $eventManager
     */
    public function addSectionEventManager(string $sectionName, EventManagerInterface $eventManager) {
        $this->sectionEventManagers[$sectionName] = $eventManager;
    }

    /**
     * Removes an event manager if exists in section
     * @param string $sectionName
     */
    public function removeSection(string $sectionName) {
        if($this->sectionExists($sectionName))
            unset($this->sectionEventManagers[$sectionName]);
    }

    /**
     * Checks if a section exists
     *
     * @param string $sectionName
     * @return bool
     */
    public function sectionExists(string $sectionName): bool {
        return isset($this->sectionEventManagers[$sectionName]);
    }

    public function getEventManager(string $sectionName): ?EventManagerInterface {
        return $this->sectionEventManagers[$sectionName] ?? NULL;
    }

    /**
     * @inheritDoc
     */
    public function trigger(string $eventName, EventInterface $event = NULL, ...$arguments): EventInterface
    {
        $sections = explode(".", $eventName);
        if(count($sections)>1 && $this->sectionExists($section = array_shift($sections))) {
            $eventName = implode(".", $sections);
            return $this->getEventManager($section)->trigger($eventName, $event, ...$arguments);
        }

        return $this->_t_trigger($eventName, $event, ...$arguments);
    }

    /**
     * Lightweight method to declare section and event names separately
     *
     * @param string $sectionName
     * @param string $eventName
     * @param EventInterface|NULL $event
     * @param mixed ...$arguments
     * @return EventInterface
     */
    public function triggerSection(string $sectionName, string $eventName, EventInterface $event = NULL, ...$arguments): EventInterface {
        $eventName = "$sectionName.$eventName";
        return $this->trigger($eventName, $event, ...$arguments);
    }
}
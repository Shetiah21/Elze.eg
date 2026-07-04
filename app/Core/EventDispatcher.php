<?php

namespace App\Core;

class EventDispatcher
{
    private static ?EventDispatcher $instance = null;
    private array $listeners = [];

    private function __construct() {}

    public static function getInstance(): EventDispatcher
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Bind a listener to an event class
     * 
     * @param string $eventClass
     * @param callable $listener
     */
    public function addListener(string $eventClass, callable $listener): void
    {
        $this->listeners[$eventClass][] = $listener;
    }

    /**
     * Dispatch an event to all registered listeners
     * 
     * @param object $event
     */
    public function dispatch(object $event): void
    {
        $eventClass = get_class($event);
        
        if (isset($this->listeners[$eventClass])) {
            foreach ($this->listeners[$eventClass] as $listener) {
                call_user_func($listener, $event);
            }
        }
    }
}

<?php

namespace Common\Admin\Appearance\Events;

class AppearanceSettingSaved
{
    /**
     * @var string
     */
    public $type;

    /**
     * @var string
     */
    public $key;

    /**
     * @var string
     */
    public $value;

    /**
     * @var string
     */
    public $previousValue;

    /**
     * @param string $type
     * @param string $key
     * @param string $value
     * @param string $previousValue
     */
    public function __construct($type, $key, $value, $previousValue)
    {
        //
        $this->type = $type;
        $this->key = $key;
        $this->value = $value;
        $this->previousValue = $previousValue;
    }
}

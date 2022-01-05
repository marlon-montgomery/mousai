<?php

namespace Common\Settings\Events;

class SettingsSaved
{
    /**
     * @var array
     */
    public $dbSettings;

    /**
     * @var array
     */
    public $envSettings;

    /**
     * @param array $dbSettings
     * @param array $envSettings
     */
    public function __construct($dbSettings, $envSettings)
    {
        $this->dbSettings = $dbSettings;
        $this->envSettings = $envSettings;
    }
}

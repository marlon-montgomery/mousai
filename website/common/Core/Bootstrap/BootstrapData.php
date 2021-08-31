<?php


namespace Common\Core\Bootstrap;


interface BootstrapData
{
    /**
     * Get data needed to bootstrap the application.
     *
     * @return string
     */
    public function getEncoded(): string;

    /**
     * @return self
     */
    public function init();

    public function getThemes(): array;
}

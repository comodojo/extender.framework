<?php namespace Comodojo\Extender\Traits;

use \Comodojo\Foundation\Base\Configuration;

trait ConfigurationTrait {

    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * Get current configuration
     *
     * @return Configuration
     */
    public function getConfiguration() {

        return $this->configuration;

    }

    /**
     * Set current configuration
     *
     * @param Configuration $configuration
     * @return self
     */
    public function setConfiguration(Configuration $configuration) {

        $this->configuration = $configuration;

        return $this;

    }

}

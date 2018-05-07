<?php

namespace JBen87\ParsleyBundle\Exception\Builder;

class InvalidConfigurationException extends \Exception
{
    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        parent::__construct('The builder must be configured first by calling the "configure" method.');
    }
}

<?php

namespace JBen87\ParsleyBundle\Validator\ParsleyConstraints;

use JBen87\ParsleyBundle\Exception\Validator\ParsleyConstraints\MissingOptionsException;
use JBen87\ParsleyBundle\Validator\ParsleyConstraint;

/**
 * @author Benoit Jouhaud <bjouhaud@prestaconcept.net>
 */
class MinLength extends ParsleyConstraint
{
    /**
     * @var string
     */
    protected $attribute = 'data-parsley-minlength';

    /**
     * @var int
     */
    protected $min;

    /**
     * {@inheritdoc}
     */
    public function __construct(array $options = [])
    {
        parent::__construct($options);

        if (!isset($options['min'])) {
            throw new MissingOptionsException(['min']);
        }

        $this->min = $options['min'];
    }

    /**
     * {@inheritdoc}
     */
    protected function getValue()
    {
        return $this->min;
    }
}

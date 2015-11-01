<?php

namespace JBen87\ParsleyBundle\Tests\Unit\Validator\ParsleyConstraints;

use JBen87\ParsleyBundle\Validator\Constraints\MinLength;

/**
 * @author Benoit Jouhaud <bjouhaud@prestaconcept.net>
 */
class MinLengthTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @expectedException \Symfony\Component\OptionsResolver\Exception\MissingOptionsException
     */
    public function emptyConfiguration()
    {
        new MinLength();
    }

    /**
     * @test
     * @expectedException \Symfony\Component\OptionsResolver\Exception\InvalidArgumentException
     */
    public function invalidConfiguration()
    {
        new MinLength([
            'min' => '5',
        ]);
    }

    /**
     * @test
     */
    public function validConfiguration()
    {
        new MinLength([
            'min' => 5,
        ]);

        new MinLength([
            'min' => 5,
            'message' => 'Invalid',
        ]);
    }
}
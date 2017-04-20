<?php

namespace JBen87\ParsleyBundle\Tests\Unit\Validator\Constraints;

use JBen87\ParsleyBundle\Tests\Unit\Validator\Constraint;
use JBen87\ParsleyBundle\Validator\Constraints\Range;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Benoit Jouhaud <bjouhaud@prestaconcept.net>
 */
class RangeTest extends Constraint
{
    /**
     * {@inheritdoc}
     *
     * @test
     * @expectedException \Symfony\Component\OptionsResolver\Exception\MissingOptionsException
     */
    public function emptyConfiguration()
    {
        new Range();
    }

    /**
     * @test
     * @expectedException \Symfony\Component\OptionsResolver\Exception\MissingOptionsException
     */
    public function incompleteConfiguration()
    {
        new Range([
            'min' => 5,
            'maxMessage' => 'Too long',
        ]);
    }

    /**
     * {@inheritdoc}
     *
     * @test
     * @expectedException \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     */
    public function invalidConfiguration()
    {
        new Range([
            'min' => '5',
            'max' => '10',
        ]);
    }

    /**
     * @test
     */
    public function extraConfiguration()
    {
        // handle symfony version <= 2.5
        if (method_exists(new OptionsResolver, 'remove')) {
            $this->setExpectedException('Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException');
        }

        new Range([
            'min' => 5,
            'max' => 10,
            'message' => 'Invalid',
        ]);
    }

    /**
     * {@inheritdoc}
     *
     * @test
     */
    public function validConfiguration()
    {
        new Range([
            'min' => 5,
            'max' => 10,
        ]);

        new Range([
            'min' => 5,
            'max' => 10,
            'minMessage' => 'Too short',
            'maxMessage' => 'Too long',
        ]);

        new Range([
            'min' => '2017-04-20 00:01',
            'max' => '2017-04-20 00:30',
            'minMessage' => 'Not a valid datetime',
            'maxMessage' => 'Not a valid datetime',
        ]);
    }

    /**
     * {@inheritdoc}
     *
     * @test
     */
    public function normalization()
    {
        $constraint = new Range([
            'min' => 5,
            'max' => 10,
        ]);

        $this->assertSame([
            'data-parsley-min' => 5,
            'data-parsley-min-message' => 'Invalid.',
            'data-parsley-max' => 10,
            'data-parsley-max-message' => 'Invalid.',
        ], $constraint->normalize($this->normalizer->reveal()));
    }
}

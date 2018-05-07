<?php

namespace JBen87\ParsleyBundle\Tests\Unit\Factory;

use JBen87\ParsleyBundle\Factory\ConstraintFactory;
use JBen87\ParsleyBundle\Validator\Constraints as ParsleyAssert;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;

class ConstraintFactoryTest extends TestCase
{
    const TRANSLATOR_METHOD_TRANS = 'trans';
    const TRANSLATOR_METHOD_TRANSCHOICE = 'transChoice';

    /**
     * @var ObjectProphecy|TranslatorInterface
     */
    private $translator;

    /**
     * @var LoggerInterface|ObjectProphecy
     */
    private $logger;

    /**
     * @var NormalizerInterface|ObjectProphecy
     */
    private $normalizer;

    /**
     * @var string[]
     */
    private $patterns;

    /**
     * @param string $class
     * @param Constraint $constraint
     * @param string[] $translations
     *
     * @dataProvider validProvider
     */
    public function testValidConstraintCreation(string $class, Constraint $constraint, array $translations)
    {
        foreach ($translations as $translation) {
            if (count($translation['config']) <= 0) {
                continue;
            }

            $this->configureTranslator($translation['message'], $translation['config']);
        }

        $object = $this->createFactory()->create($constraint);
        $attributes = $object->normalize($this->normalizer->reveal());

        $this->assertInstanceOf($class, $object);

        foreach ($translations as $name => $translation) {
            $this->assertSame($translation['message'], $attributes[sprintf('%s-message', $name)]);
        }
    }

    /**
     * @return array
     */
    public function validProvider(): array
    {
        return array_merge(
            $this->patternProvider(),
            $this->typeProvider(),
            $this->lengthProvider(),
            $this->requiredProvider(),
            $this->rangeProvider(),
            $this->greaterThanProvider(),
            $this->lessThanProvider()
        );
    }

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->translator = $this->prophesize('Symfony\Component\Translation\Translator');
        $this->logger = $this->prophesize('Psr\Log\LoggerInterface');
        $this->normalizer = $this->prophesize('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
        $this->patterns = [
            'date_time' => '\d{4}-\d{2}-\d{2} \d{2}:\d{2}',
            'date' => '\d{4}-\d{2}-\d{2}',
            'time' => '\d{2}:\d{2}',
        ];
    }

    /**
     * @return array
     */
    private function patternProvider(): array
    {
        return [
            [
                ParsleyAssert\Pattern::class,
                new Assert\DateTime(),
                [
                    'data-parsley-pattern' => [
                        'message' => 'This value is not a valid datetime.',
                        'config' => [
                            'method' => self::TRANSLATOR_METHOD_TRANS,
                            'id' => 'This value is not a valid datetime.',
                            'params' => [],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    private function typeProvider(): array
    {
        return [
            [
                ParsleyAssert\Type::class,
                new Assert\Email(),
                [
                    'data-parsley-type' => [
                        'message' => 'This value is not a valid email address.',
                        'config' => [
                            'method' => self::TRANSLATOR_METHOD_TRANS,
                            'id' => 'This value is not a valid email address.',
                            'params' => [],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    private function lengthProvider(): array
    {
        return [
            [
                ParsleyAssert\Length::class,
                new Assert\Length(5),
                [
                    'data-parsley-length' => [
                        'message' => 'This value should have exactly 5 characters.',
                        'config' => [
                            'method' => self::TRANSLATOR_METHOD_TRANSCHOICE,
                            'id' => sprintf(
                                '%s|%s',
                                'This value should have exactly {{ limit }} character.',
                                'This value should have exactly {{ limit }} characters.'
                            ),
                            'params' => ['{{ limit }}' => 5],
                            'count' => 5,
                        ],
                    ],
                ],
            ],
            [
                ParsleyAssert\Length::class,
                new Assert\Length(['min' => 5, 'max' => 10]),
                [
                    'data-parsley-length' => [
                        'message' => 'This value should have 5 to 10 characters.',
                        'config' => [
                            'method' => self::TRANSLATOR_METHOD_TRANS,
                            'id' => 'This value should have {{ min }} to {{ max }} characters.',
                            'params' => ['{{ min }}' => 5, '{{ max }}' => 10],
                        ],
                    ],
                ],
            ],
            [
                ParsleyAssert\MinLength::class,
                new Assert\Length(['min' => 5]),
                [
                    'data-parsley-minlength' => [
                        'message' => 'This value is too short. It should have 5 characters or more.',
                        'config' => [
                            'method' => self::TRANSLATOR_METHOD_TRANSCHOICE,
                            'id' => sprintf(
                                '%s|%s',
                                'This value is too short. It should have {{ limit }} character or more.',
                                'This value is too short. It should have {{ limit }} characters or more.'
                            ),
                            'params' => ['{{ limit }}' => 5],
                            'count' => 5,
                        ],
                    ],
                ],
            ],
            [
                ParsleyAssert\MaxLength::class,
                new Assert\Length(['max' => 10]),
                [
                    'data-parsley-maxlength' => [
                        'message' => 'This value is too long. It should have 10 characters or less.',
                        'config' => [
                            'method' => self::TRANSLATOR_METHOD_TRANSCHOICE,
                            'id' => sprintf(
                                '%s|%s',
                                'This value is too long. It should have {{ limit }} character or less.',
                                'This value is too long. It should have {{ limit }} characters or less.'
                            ),
                            'params' => ['{{ limit }}' => 10],
                            'count' => 10,
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    private function requiredProvider(): array
    {
        return [
            [
                ParsleyAssert\Required::class,
                new Assert\NotBlank(),
                [
                    'data-parsley-required' => [
                        'message' => 'This value should not be blank.',
                        'config' => [
                            'method' => self::TRANSLATOR_METHOD_TRANS,
                            'id' => 'This value should not be blank.',
                            'params' => [],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    private function rangeProvider(): array
    {
        return [
            [
                ParsleyAssert\Range::class,
                new Assert\Range(['min' => 5, 'max' => 10]),
                [
                    'data-parsley-min' => [
                        'message' => 'This value should be 5 or more.',
                        'config' => [
                            'method' => self::TRANSLATOR_METHOD_TRANS,
                            'id' => 'This value should be {{ limit }} or more.',
                            'params' => ['{{ limit }}' => 5],
                        ],
                    ],
                    'data-parsley-max' => [
                        'message' => 'This value should be 10 or less.',
                        'config' => [
                            'method' => self::TRANSLATOR_METHOD_TRANS,
                            'id' => 'This value should be {{ limit }} or less.',
                            'params' => ['{{ limit }}' => 10],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    private function greaterThanProvider(): array
    {
        return [
            [
                ParsleyAssert\GreaterThan::class,
                new Assert\GreaterThan(['value' => 5]),
                [
                    'data-parsley-gt' => [
                        'message' => 'This value should be greater than 5.',
                        'config' => [
                            'method' => self::TRANSLATOR_METHOD_TRANS,
                            'id' => 'This value should be greater than {{ compared_value }}.',
                            'params' => ['{{ compared_value }}' => 5],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    private function lessThanProvider(): array
    {
        return [
            [
                ParsleyAssert\LessThan::class,
                new Assert\LessThan(['value' => 10]),
                [
                    'data-parsley-lt' => [
                        'message' => 'This value should be less than 10.',
                        'config' => [
                            'method' => self::TRANSLATOR_METHOD_TRANS,
                            'id' => 'This value should be less than {{ compared_value }}.',
                            'params' => ['{{ compared_value }}' => 10],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @param string $expected
     * @param array $config
     */
    private function configureTranslator(string $expected, array $config): void
    {
        switch ($config['method']) {
            case self::TRANSLATOR_METHOD_TRANSCHOICE:
                $this->translator
                    ->transChoice($config['id'], $config['count'], $config['params'], 'validators')
                    ->shouldBeCalled()
                    ->willReturn($expected);
                break;

            default:
                $this->translator
                    ->trans($config['id'], $config['params'], 'validators')
                    ->shouldBeCalled()
                    ->willReturn($expected);
        }
    }

    /**
     * @return ConstraintFactory
     */
    private function createFactory(): ConstraintFactory
    {
        return new ConstraintFactory($this->translator->reveal(), $this->logger->reveal(), $this->patterns);
    }
}

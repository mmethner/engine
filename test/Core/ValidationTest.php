<?php
/*
 * This file is part of the Engine framework.
 * (c) Mathias Methner <mathiasmethner@gmail.com>
 * Please view the LICENSE file
 */
namespace Engine\Test\Core;

use PHPUnit\Framework\TestCase;

class ValidationTest extends TestCase
{
    /**
     * @var Fixture\Validation
     */
    protected $validation;

    /**
     * @param int|string $value
     * @param bool $expected
     * @covers       \Engine\Core\Validation::validateMoney
     * @dataProvider providerValidateMoney
     */
    public function testValidateMoney($value, bool $expected)
    {
        $this->validation->setDefinition([
            'money' => [
                'validator' => 'money',
                'required' => true,
                'msg' => ''
            ]
        ]);

        $this->assertEquals($expected, $this->validation->validate([
            'money' => $value
        ]));
    }

    /**
     * @covers       \Engine\Core\Validation::validateMoney
     * @dataProvider providerValidateMoneyUnsigned
     * @param $value
     * @param bool $expected
     */
    public function testValidateMoneyUnsigned($value, bool $expected)
    {
        $this->validation->setDefinition([
            'discount' => [
                'validator' => 'money-unsigned',
                'required' => true,
                'msg' => ''
            ]
        ]);

        $this->assertEquals($expected, $this->validation->validate([
            'discount' => $value
        ]));
    }

    /**
     * @covers       \Engine\Core\Validation::validateBoolean
     * @dataProvider providerValidateBoolean
     * @param $value
     * @param bool $expected
     */
    public function testValidateBoolean($value, bool $expected)
    {
        $this->validation->setDefinition([
            'test' => [
                'validator' => 'bool',
                'required' => true,
                'msg' => ''
            ]
        ]);

        $this->assertEquals($expected, $this->validation->validate([
            'test' => $value
        ]));
    }

    /**
     * @covers       \Engine\Core\Validation::validateUrl
     * @dataProvider providerValidateUrl
     * @param $value
     * @param $expected
     */
    public function testValidateUrl($value, $expected)
    {
        $this->validation->setDefinition([
            'test' => [
                'validator' => 'url',
                'required' => true,
                'msg' => ''
            ]
        ]);

        $this->assertEquals($expected, $this->validation->validate([
            'test' => $value
        ]), 'url validation failure ' . $value);
    }

    public function providerValidateBoolean(): array
    {
        return [
            [
                '',
                false
            ],
            [
                50,
                false
            ],
            [
                '50',
                false
            ],
            [
                'test',
                false
            ],
            [
                'true',
                true
            ],
            [
                true,
                true
            ],
            [
                'false',
                true
            ],
            [
                false,
                true
            ],
            [
                1,
                true
            ],
            [
                '1',
                true
            ],
            [
                0,
                true
            ],
            [
                '0',
                true
            ]
        ];
    }

    public function providerValidateMoney(): array
    {
        return [
            [
                '',
                false
            ],
            [
                50,
                false
            ],
            [
                '50',
                false
            ],
            [
                'test',
                false
            ],
            [
                '-50.00',
                true
            ],
            [
                '-50,00',
                true
            ],
            [
                '67.01',
                true
            ],
            [
                '67,01',
                true
            ]
        ];
    }

    public function providerValidateMoneyUnsigned(): array
    {
        return [
            [
                '',
                false
            ],
            [
                50,
                false
            ],
            [
                '50',
                false
            ],
            [
                'test',
                false
            ],
            [
                '-50.00',
                false
            ],
            [
                '-50,00',
                false
            ],
            [
                '67.01',
                true
            ],
            [
                '67,01',
                true
            ]
        ];
    }

    public function providerValidateUrl(): array
    {
        return [
            [
                '',
                false
            ],
            [
                50,
                false
            ],
            [
                'http://www.mathiasmethner.de',
                true
            ],
            [
                'https://www.mathiasmethner.de',
                true
            ],
            [
                '-http://www.mathiasmethner.de',
                false
            ],
            [
                'https://www.mathiasmethner.de/login?parameter=wert',
                true
            ],
            [
                'http://www.mathiasmethner.de/#anchor',
                true
            ],
            [
                'http://www.mathiasmethner.de',
                true
            ],
            [
                'http://mathiasmethner.de/index.php/programme/ferienprogramme/daycamps-spezial/32-graffiti-camp?parameter=true',
                true
            ]
        ];
    }

    /**
     *
     * @return void
     */
    protected function setUp()
    {
        $this->validation = new Fixture\Validation();
    }
}

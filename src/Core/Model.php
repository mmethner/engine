<?php
/*
 * This file is part of the Engine framework.
 * (c) Mathias Methner <mathiasmethner@gmail.com>
 * Please view the LICENSE file
 */
namespace Engine\Core;

use Spot\Entity;

/**
 * @property \DateTime lastmod
 */
abstract class Model extends Entity
{
    /**
     *
     * @var bool
     */
    protected $updateLastmod = true;

    /**
     * @param array $data
     * @see \Spot\Entity:: __construct
     */
    public function __construct(array $data = [])
    {
        parent::__construct($data);

        if ($this->updateLastmod) {
            $this->lastmod = new \DateTime('now');
        }
    }

    /**
     *
     * @return array
     */
    public static function fields()
    {
        return [
            'id' => [
                'type' => 'integer',
                'autoincrement' => true,
                'primary' => true,
                'unsigned' => true,
                'index' => true
            ],
            'lastmod' => [
                'type' => 'datetime',
                'required' => true,
                'value' => new \DateTime('now')
            ]
        ];
    }
}
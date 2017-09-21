<?php
/*
 * This file is part of the Engine framework.
 * (c) Mathias Methner <mathiasmethner@gmail.com>
 * Please view the LICENSE file
 */
namespace Engine\Test\Core\Fixture;

class Validation extends \Engine\Core\Validation
{
    public function setDefinition(array $definition)
    {
        $this->definition = $definition;
    }
}

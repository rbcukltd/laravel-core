<?php
namespace Dapatchi\LaravelCore\Entities;

interface ValidateOnCreate
{
    /**
     * @return string
     */
    public function getCreateRequestClass();
}

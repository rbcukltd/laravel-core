<?php
namespace Dapatchi\LaravelCore\Entities;

interface ValidateOnUpdate
{
    /**
     * @return string
     */
    public function getUpdateRequestClass();
}

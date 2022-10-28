<?php

namespace Ssentezo\Util;

trait CommonFields
{
    protected $activeFlag = 1;
    protected $delFlag = 0;

    public function getActiveFlag(): string
    {
        return $this->activeFlag;
    }
    public function getDelFlag(): string
    {
        return $this->delFlag;
    }
    public function setActiveFlag(string $activeFlag): void
    {
        $this->activeFlag = $activeFlag;
    }
    public function setDelFlag(string $delFlag)
    {
        $this->delFlag = $delFlag;
    }
}

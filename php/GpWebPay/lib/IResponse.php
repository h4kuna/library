<?php

namespace Pay3dSecure;

interface IResponse
{
    /**
     * hodnoty k ověření, na pořadí záleží
     * @return array
     */
    public function getVerify();
}

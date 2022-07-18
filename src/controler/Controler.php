<?php
namespace Slurp\Controler;

abstract class Controler
{
    protected $request;
    protected $args;

    public function __construct($request, $args)
    {
        $this->request = $request;
        $this->args = $args;
    }
}
?>
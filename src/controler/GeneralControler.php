<?php

namespace Slurp\Controler;

use Slurp\View\GeneralView;

class GeneralControler extends Controler
{
    public function getHome()
    {
        $genView=new GeneralView();
        return $genView->getHTMLHome($this->request);
    }

    public function getHead()
    {
        $genView=new GeneralView();
        return $genView->getHead($this->request);
    }

    public function getHeader()
    {
        $genView=new GeneralView();
        return $genView->getHeader($this->request,$this->args);
    }

    public function getFooter(array $srcScript)
    {
        $genView=new GeneralView();
        return $genView->getFooter($this->request, $srcScript,$this->args);
    }

    public function getFoot(array $srcScript)
    {
        $genView=new GeneralView();
        return $genView->getFoot($this->request, $srcScript);
    }
}

?>
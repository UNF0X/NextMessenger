<?php
namespace app\forms;

use std, gui, framework, app;


class register extends AbstractForm
{

    /**
     * @event Close.action 
     */
    function doCloseAction(UXEvent $e = null)
    {
        exit();
    }

    /**
     * @event Minimize.action 
     */
    function doMinimizeAction(UXEvent $e = null)
    {
        app()->minimizeForm('MainForm');
    }

    /**
     * @event panel.step 
     */
    function doPanelStep(UXEvent $e = null)
    {
        $this->centerXY($this->panel);
    }

    /**
     * @event button3.action 
     */
    function doButton3Action(UXEvent $e = null)
    {    
        nextModule::register($this->editAlt->text, $this->edit3->text, $this->edit4->text, $this->edit->text, $this->passwordField->text);
    }

    function centerX($obj)
    {
        $obj->x = $this->width/2 - $obj->width/2;
    }
    
    function centerY($obj)
    {
        $obj->y = $this->height/2 - $obj->height/2;
    }
    
    function centerXY($obj)
    {
        $obj->x = $this->width/2 - $obj->width/2;
        $obj->y = $this->height/2 - $obj->height/2 ;
    }
}

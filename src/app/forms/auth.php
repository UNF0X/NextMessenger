<?php
namespace app\forms;

use gui\Ext4JphpWindows;
use std, gui, framework, app;


class auth extends AbstractForm
{

    /**
     * @event showing 
     */
    function doShowing(UXWindowEvent $e = null)
    {    
        $ext4php = new Ext4JphpWindows;
        $ext4php->addBorder($this,0,'#8bc34a');
    }

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
        $this->loadForm(register);
    }

    /**
     * @event buttonAlt.action 
     */
    function doButtonAltAction(UXEvent $e = null)
    {    
        if (nextModule::checkAuth(trim($this->edit->text), trim($this->passwordField->text)) == true)
        {
            $this->loadForm(MainForm);
        }
    }

    /**
     * @event show 
     */
    function doShow(UXWindowEvent $e = null)
    {    
            if (nextModule::checkAuth(trim($this->edit->text), trim($this->passwordField->text)) == true)
            {
                $this->loadForm(MainForm);
            }
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

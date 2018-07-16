<?php
namespace app\forms;

use std, gui, framework, app;


class newChat extends AbstractForm
{

    /**
     * @event Close.action 
     */
    function doCloseAction(UXEvent $e = null)
    {
        $this->hide();
    }


    /**
     * @event Minimize.action 
     */
    function doMinimizeAction(UXEvent $e = null)
    {
        app()->minimizeForm('MainForm');
    }

    /**
     * @event buttonAlt.action 
     */
    function doButtonAltAction(UXEvent $e = null)
    {    
        nextModule::query('messages.createChat', ['title'=>$this->edit->text]);
        $this->edit->text = '';
        $this->hide();
    }

}

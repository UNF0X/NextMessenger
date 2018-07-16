<?php
namespace app\forms;

use std, gui, framework, app;


class userInfo extends AbstractForm
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

}

<?php
namespace app\forms;

use std, gui, framework, app;


class inviteChat extends AbstractForm
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
       global $chat_id;
       var_dump($chat_id);
       $this->inviteUserToChat($this->edit->text, $chat_id)
       $this->toast('Пользователь добавлен в чат!');
       $this->hide();
    }
    
    function inviteUserToChat($uid, $chat_id){
        $data=nextModule::query('messages.ChatUserInvite', ['chat_id'=>$chat_id, 'uid'=>$uid]);
    }

}

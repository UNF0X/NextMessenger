<?php
namespace app\forms;

use bundle\jurl\jURL;
use std, gui, framework, app;


class chatInfo extends AbstractForm
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
     * @event show 
     */
    function doShow(UXWindowEvent $e = null)
    {    
        global $chat_id;
        $chat_info=nextModule::query('messages.getChat', ['chat_id'=>$chat_id]);
            Element::loadContentAsync($this->chat_image, $chat_info['response']['photo'], function () use ($chat_image, $this) {
        });
        $users=explode(',',$chat_info['response']['users']);
        $this->chat_count_label->text= count($users).' members';
        $this->chat_title_label->text=$chat_info['response']['title'];
        $this->Users->items->clear();
        $users=nextModule::query('messages.getChatUsers', ['chat_id'=>$chat_id]);
        var_dump($users);
        foreach ($users['response'] as $key => $user){
                $this->addUser($user);
        }
    }

    /**
     * @event buttonAlt.action 
     */
    function doButtonAltAction(UXEvent $e = null)
    {    
        app()->showForm(inviteChat);
    }

    /**
     * @event button3.click-Left 
     */
    function doButton3ClickLeft(UXMouseEvent $e = null)
    {
        $this->fileChooser->execute();
        $file=$this->fileChooser->file->getPath();
        if(in_array($this->getExtension($file), ['png', 'jpg'])){
            $ch = new jURL('https://s1.unfox.ru/upload/index.php');
            $ch->setRequestMethod('POST');
            $ch->addPostFile('file', $file);
            $ch->asyncExec(function($result){
                $result=json_decode($result,1)['response'];
                global $chat_id;
                nextModule::query('chat.updatePhoto', ['photo'=>$result['url'], 'chat_id'=>$chat_id]);
                $pic=$this->chat_image;
                Element::loadContentAsync($pic, $result['url'],function () use ($this,$pic) {
                    app()->form('MainForm')->setBorderRadius($pic, 255);
                });
                app()->form('MainForm')->Dialogs->items->clear();
                app()->form('MainForm')->getChats();
            });
        }
    }
    
    public function getExtension( $filename ) {
        $explode=explode( '.', $filename );
        return $explode[count($explode)-1];
    }
    
    function addUser($user_data)
    {
            $main = new UXHBox;
            $main->style = "-fx-padding: 5px;";
            $photo = new UXImageView;
            $photo->size = [50,50];
            //var_dump($user_data['photo_100']);
            Element::loadContentAsync($photo, $user_data['photo_100'], function () use ($this, $photo) {
                $this->setBorderRadius($photo, 255);
            });
            $body = new UXVBox;
            $body->paddingLeft = 5;
            $name = new UXLabel($user_data['first_name'].' '.$user_data['last_name']);
            $name->font->bold = true;
            $name->textColor = '#FFFFFF';
            $message = new UXLabel();
            //$message->id=$dialog['chat_id'].'chat_d';
            $message->textColor = '#707378';
            $main->add($photo);
          //  $main->id=$dialog['chat_id'].'_chat';
            $body->add($name);
            $body->add($message);
            $main->add($body);
            $main->on('click', function () use ($dialog) {

            });
            Animation::fadeIn($main, 200);
            
        $this->Users->contextMenu = new UXContextMenu; 
        
        $invite_user = new UXMenuItem('Delete user from chat'); 
        $invite_user->on('action', function(){ 
            global $invite_chat_id;
            $data = $this->Users->selectedItem->id; 
            $invite_chat_id=str_replace('_chat', '', $data);
                //app()->form('MainForm')->$data->text='*Message deleted*';
                
        });
             
        $this->Users->contextMenu->items->add($invite_user);
        
        $this->Users->items->add($main);
    }

}

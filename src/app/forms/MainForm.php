<?php
namespace app\forms;

use httpclient;
use bundle\jurl\jURL;
use gui\Ext4JphpWindows;
use std, gui, framework, app;


class MainForm extends AbstractForm
{

    /**
     * @event Maximize.action 
     */
    function doMaximizeAction(UXEvent $e = null)
    {    
        $this->maximized = !$this->maximized;
    }

    /**
     * @event Minimize.action 
     */
    function doMinimizeAction(UXEvent $e = null)
    {    
        app()->minimizeForm('MainForm');
    }

    /**
     * @event showing 
     */
    function doShowing(UXWindowEvent $e = null)
    {    
        $ext4php = new Ext4JphpWindows;
        $ext4php->addBorder($this,0,'#8bc34a');
    }

    /**
     * @event show 
     */
    function doShow(UXWindowEvent $e = null)
    {    
    
        define('__UPLOAD__', 'https://s1.unfox.ru/');
    
        $this->Dialogs->items->clear();
        $this->InitStart();
        $status=nextModule::query('status');
        if($status!='ok'){alert('Сервер не отвечает!'); exit;}
        $this->getChats();
        nextModule::lp_poll();
        
        $this->panel->on('dragOver', function(UXDragEvent $e){
            if(sizeof($e->dragboard->files) > 0){
                $e->acceptTransferModes(['MOVE', 'COPY']);
            }
            $e->consume();
        });
        
        $this->panel->on('dragDrop', function(UXDragEvent $e){ 
            if(sizeof($e->dragboard->files) > 0){
                foreach($e->dragboard->files as $file){
                    $this->message_file = $file;
                    
                    $file=$this->message_file->getPath();
                    var_dump($file);
                    $ch = new jURL(__UPLOAD__.'/upload/index.php');
                    $ch->setRequestMethod('POST');
                    $ch->addPostFile('file', $file);
                    $ch->asyncExec(function($result){
                        global $attachment;
                        $result=json_decode($result,1);
                        if(in_array($this->getExtension($result['response']['filename']), ['png', 'jpg'])){
                            $attachment['photo']=$result['response'];
                            //var_dump($result);
                            $this->toast("Photo attached to message");
                        }else{
                            $attachment['doc']=$result['response'];
                           // var_dump($result);
                            $this->toast("Document attached to message");
                        }    
                        $this->textArea->requestFocus();
                    }); 
                    
                    //pre($this->message_file);
                    
                    return;
                }
            }
        }); 
        
        
        Logger::info("Loaded!");
        
    }
    
    public function getExtension( $filename ) {
        $explode=explode( '.', $filename );
        return $explode[count($explode)-1];
    }
    
    /**
     * @event count.mouseDrag 
     */
    function doCountMouseDrag(UXMouseEvent $e = null)
    {    
        $this->create_chat_pannel->hide();
        $this->create_chat_pannel->toBack();
    }

    /**
     * @event image.click-Left 
     */
    function doImageClickLeft(UXMouseEvent $e = null)
    {    
        //app()->showFormAndWait(newChat);
    }



    /**
     * @event showing 
     */
    function doShowing(UXWindowEvent $e = null)
    {    
      $ext4jphpw = new Ext4JphpWindows();
      $ext4jphpw->addBorder($this, 0, '#8bc34a');
      
     // $this->addStylesheet('./themes/black.css');
    }

    /**
     * @event username.click-Left 
     */
    function doUsernameClickLeft(UXMouseEvent $e = null)
    {    
        $this->form('MainForm')->hide();
        $this->form('Profile')->show();
    }

    /**
     * @event dlgpanel.click-Left 
     */
    function doDlgpanelClickLeft(UXMouseEvent $e = null)
    {    
        app()->showForm(chatInfo);
    }

    /**
     * @event textArea.globalKeyDown-Enter 
     */
    function doTextAreaGlobalKeyDownEnter(UXKeyEvent $e = null)
    {
        $message = trim($this->textArea->text);
        $this->textArea->clear();
        $this->sendMessage($message, $GLOBALS['chat_id']);
        $this->Messages->selectedIndex=-1;
    }

    /**
     * @event textArea.globalKeyDown-Up 
     */
    function doTextAreaGlobalKeyDownUp(UXKeyEvent $e = null)
    {
        global $message_id, $edit_message;
        $edit_message=true;
        global $chat_id;
        $message_id=nextModule::query('messages.lastMessage', ['chat_id'=>$chat_id])['response']['id'];
        $this->textArea->text=str_replace('(edit)','',app()->form('MainForm')->$message_id->text);
        $this->textArea->requestFocus();
        $this->textArea->end();
    }

    /**
     * @event imageAlt.click-Left 
     */
    function doImageAltClickLeft(UXMouseEvent $e = null)
    {
        $message = $this->textArea->text;
        $this->textArea->clear();
        
        $this->sendMessage($message, $GLOBALS['chat_id']);
        $this->Messages->selectedIndex=-1;
    }

    /**
     * @event buttonAlt.action 
     */
    function doButtonAltAction(UXEvent $e = null)
    {    
        app()->showForm(newChat);
    }

    /**
     * @event Close.action 
     */
    function doCloseAction(UXEvent $e = null)
    {    
        exit;
    }

    /**
     * @event textArea.keyPress 
     */
    function doTextAreaKeyPress(UXKeyEvent $e = null)
    {    
       /* global $sec, $chat_info, $user_info;
        if($sec<2 or !isset($sec)){
            var_dump($sec);
            //var_dump($chat_info);
            nextModule::setActivity($chat_info['response']['chat_id'], 'typing...', $user_info['response']['uid']);
            app()->module('tray')->timer->start();
        }*/
        
    }

    /**
     * @event Messages.click-Left 
     */
    function doMessagesClickLeft(UXMouseEvent $e = null)
    {    
        /*global $attachments;
        $msg_id = app()->form('MainForm')->Messages->selectedItem->id; 
        $msg_id=str_replace('_main', '', $msg_id);
        var_dump($attachments);
        if(isset($attachments[$msg_id])){
            if(isset($attachments[$msg_id]['photo'])){
                @mkdir(System::getProperty('user.home').'/NextMessenger/images');
                global $open_file;
                $open_file=System::getProperty('user.home').'/NextMessenger/images/'.$attachments[$msg_id]['photo']['d_filename'];
                $this->downloader->destDirectory = System::getProperty('user.home').'/NextMessenger/images/'; 
                $this->downloader->urls = [ $attachments[$msg_id]['photo']['url'] ]; 
                $this->downloader->start();
            }
            if(isset($attachments[$msg_id]['doc'])){
                @mkdir(System::getProperty('user.home').'/NextMessenger/doc');
                global $open_file;
                $open_file=System::getProperty('user.home').'/NextMessenger/doc/'.$attachments[$msg_id]['doc']['d_filename'];
              //  var_dump($open_file);
                $this->downloader->destDirectory = System::getProperty('user.home').'/NextMessenger/doc/'; 
                $this->downloader->urls = [ $attachments[$msg_id]['doc']['url'] ]; 
                $this->downloader->start();
            }
        }*/
        
        $this->Messages->selectedIndex=-1;
    }
    

    function centerX($obj)
    {
        $obj->x = $this->Dialogs->width + $this->panel3->width/2 - $obj->width/2;
    }
    
    function centerY($obj)
    {
        $obj->y = $this->panel->height + $this->panel3->height/2 - $obj->height/2;
    }

    function InitStart()
    {
        global $user_data_cache;
        $user_data_cache=[];
        
        $a = nextModule::query('users.get');
        $this->username->text = $a['response']['first_name'].' '.$a['response']['last_name']."\nUSER_ID: ".$a['response']['uid'];
        global $user_info;
        $user_info=$a;
        Element::loadContentAsync($this->image, $a['response']['photo_100']);
    }
    
    function getChats()
    {
        $a = nextModule::query('messages.getChats');
        foreach ($a['response']['items'] as $dialog)
        {
            $main = new UXHBox;
            $main->style = "-fx-padding: 5px;";
            $photo = new UXImageView;
            $photo->size = [50,50];
            Element::loadContentAsync($photo, $dialog['photo'], function () use ($this, $photo) {
                $this->setBorderRadius($photo, 255);
            });
            $body = new UXVBox;
            $body->paddingLeft = 5;
            $name = new UXLabel($dialog['title']);
            $name->font->bold = true;
            $name->textColor = '#FFFFFF';
            $message = new UXLabel(urldecode($dialog['last_message']['message']));
            $message->id=$dialog['chat_id'].'chat_d';
            $message->textColor = '#707378';
            $main->add($photo);
            $main->id=$dialog['chat_id'].'_chat';
            $body->add($name);
            $body->add($message);
            $main->add($body);
            $main->on('click', function () use ($dialog) {
                global $chat_info;
                $chat_info = nextModule::query('messages.getChat', ['chat_id'=>$dialog['chat_id']]);
                $this->chat_title->show();
                $this->chat_title->text=$chat_info['response']['title'];
                $this->count->show();
                $users=explode(',',$chat_info['response']['users']);
                $this->count->text= count($users).' members';
                $this->dlgpanel->show();
                $this->dlgpanel->enabled=true;
                $this->panel->show();
                $this->panel->enabled = true;
                $this->getMessages($dialog['chat_id']);
            });
            
            self::FadeIn($main, 'RIGHT');
            
            $this->Dialogs->items->add($main);
        }  
    }
    
    function inviteUserToChat($uid, $chat_id){
        $data=nextModule::query('messages.ChatUserInvite', ['chat_id'=>$chat_id, 'uid'=>$uid]);
    }
    
    function serverMessage($message)
    {
        $ux = new UXHBox;
        $ux->alignment = 'CENTER';
        $te = new UXLabel($message);
        $ux->add($te);
        #$this->Messages
    }
    
    function getMessages($id)
    {
        $GLOBALS['chat_id'] = $id;
        global $user_data_cache;
        
        $this->Messages->items->clear();
        $this->textArea->requestFocus();
        
        
        $th = new Thread(function () use ($id,$this) {
            $a = nextModule::query('messages.get', ['chat_id'=>$id]);
            UXApplication::runLater(function () use ($id,$this,$a) {
                $this->Messages->items->clear();
                global $user_data_cache, $user_info;
                $messages=[];
                foreach ($a['response']['messages'] as $message)
                {
                    if(isset($user_data_cache[$message['from_id']])){
                        $userdata=$user_data_cache[$message['from_id']];
                    }else{
                        $userdata = nextModule::query('users.get', ['uid'=>$message['from_id']])['response'];
                        $user_data_cache[$message['from_id']]=$userdata;
                    }
                    $message['message']=urldecode($message['message']);
                    if($user_info['response']['uid']==$userdata['uid']){$out=1;}else{$out=0;}
                    #var_dump($user_info);
                    #var_dump($userdata);
                    if(isset($message['server_message']) and $message['server_message']==1){
                           $messages[]=nextModule::addServerMessage($message['chat_id'], $message['message'], $message['create_date'], false);
                    }else{
                        $messages[]=nextModule::addMessage($userdata, $message, $out, false);   
                    }
                }
                $this->Messages->items->addAll($messages);      
                $index = app()->form('MainForm')->Messages->items->count()-1;
                app()->form('MainForm')->Messages->scrollTo($index);   
            });
        });
        $th->start();
    }
    
    function sendMessage($messagetext, $dialogid)
    {
        global $edit_message, $forward_message;
       // var_dump($forward_message);
        if($edit_message==false and $forward_message==false){
            $messagetext = trim($messagetext);
            if ($GLOBALS['chat_id'] != '' and $messagetext != '')
            {
                $send = new Thread(function() use($messagetext, $dialogid){
                    $args=['message'=>$messagetext, 'chat_id'=>$dialogid];
                    global $attachment;
                    if(isset($attachment)){$args['attachment']=json_encode($attachment);}
                    $a = nextModule::query('messages.send', $args); 
                    unset($attachment);
                });
                $send->start();
                //$this->getMessages($GLOBALS['chat_id']);
            }
            else 
            {
                //$this->toast('Выберите диалог');
            }
        }
        if($edit_message==true){
            global $message_id;
            $messagetext=trim($messagetext);
            $send = new Thread(function() use($messagetext, $dialogid, $message_id){
                $a = nextModule::query('messages.edit', ['message'=>$messagetext, 'message_id'=>$message_id, 'chat_id'=>$dialogid]); 
                var_dump($a);
            });
            $send->start();
            
            
            
            app()->form('MainForm')->{$message_id}->text=$messagetext;
            $edit_message=false;
            $this->Messages->selectedIndex=-1;
        }
        
        if($forward_message==true and $messagetext!=''){
            global $message_id;
            $messagetext=trim($messagetext);
            $send = new Thread(function() use($messagetext, $dialogid, $message_id){
                $a = nextModule::query('messages.send', ['message'=>$messagetext, 'fwd'=>$message_id, 'chat_id'=>$dialogid]); 
                var_dump($a);
            });
            $send->start();

            $forward_message=false;
            $this->Messages->selectedIndex=-1;
        }
    }
    
    function setBorderRadius($image, $radius) 
    {
            $rect = new UXRectangle;
            $rect->width = $image->width;
            $rect->height = $image->height;
            
            $rect->arcWidth = $radius*2;
            $rect->arcHeight = $radius*2;
    
            $image->clip = $rect;
            $circledImage = $image->snapshot();
    
            $image->clip = NULL;
            $rect->free();
    
            $image->image = $circledImage;
            return $image;
    }
    
    public static function fadeIn($node, $type = 'BASIC', $callback = null)
    {
    
        $node->opacity = 0;
        Animation::fadeIn($node, 550);
        switch ($type)
        {
        
            case ('TOP'):
                $node->y -= 50;
                Animation::displace($node, 550, 0, 50, $callback);
                break;
                
            case ('RIGHT'):
                $node->x += 50;
                Animation::displace($node, 450, -50, 0, $callback);
                break;
                
            case ('DOWN'):
                $node->y += 50;
                Animation::displace($node, 450, 0, -50, $callback);
                break;
                
            case ('LEFT'):
                $node->x -= 50;
                Animation::displace($node, 450, 50, 0, $callback);
                break;
                
        }
        
    }

}

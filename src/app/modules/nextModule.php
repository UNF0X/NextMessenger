<?php
namespace app\modules;

use httpclient;
use std, gui, framework, app;


class nextModule extends AbstractModule
{
    public static 
    $host = 'https://next.unfox.ru',
    $useragent = 'NextMessenger(0.0.1)',
    $token = '',
    $tokenFile = '.\token.temp';

       public static function get_filesize($size) {
            $a = array("B", "KB", "MB", "GB", "TB", "PB");
            $pos = 1;
            while ($size >= 1024) {
                $size /= 1024;
                $pos++;
            }
            return round($size,2)." ".$a[$pos];
        }
    
        public static function addMessage($user, $message, $out, $paint=true)
        {
        
            global $user_info;
                app()->form('MainForm')->Messages->contextMenu = new UXContextMenu; 
                
                $forward = new UXMenuItem('Forward message'); 
                $forward->on('action', function() use ($message){  
                    global $user_info;   
                    #if($user_info['response']['uid']==$message['from_id']){
                        $data = app()->form('MainForm')->Messages->selectedItem->id; 
                        $data=str_replace('_main', '', $data);
                        global $message_id, $forward_message;
                        $forward_message=true;
                        $message_id=$data;
                        app()->form('MainForm')->textArea->requestFocus();
                    #}    
                    //app()->form('MainForm')->$data->text='*Message deleted*';
                    
                });
                
                $delete = new UXMenuItem('Delete message'); 
                $delete->on('action', function() use ($message){ 
                    global $chat_info, $user_info;
                    //var_dump($chat_info);
                    #if($user_info['response']['uid']==$message['from_id']){
                        $data = app()->form('MainForm')->Messages->selectedItem->id; 
                        $data=str_replace('_main', '', $data);
                        self::query('messages.delete', ['message_id'=>$data, 'chat_id'=>$chat_info['response']['chat_id']]);
                    //app()->form('MainForm')->$data->text='*Message deleted*';
                    #}
                });
                
                $edit = new UXMenuItem('Edit message'); 
                $edit->on('action', function() use ($message){  
                    global $user_info;   
                    var_dump($user_info);
                    #if($user_info['response']['uid']==$message['from_id']){
                        $data = app()->form('MainForm')->Messages->selectedItem->id; 
                        $data=str_replace('_main', '', $data);
                        global $message_id, $edit_message;
                        $edit_message=true;
                        $message_id=$data;
                        app()->form('MainForm')->textArea->text=str_replace('(edit)','',app()->form('MainForm')->$data->text);
                        app()->form('MainForm')->textArea->requestFocus();
                        app()->form('MainForm')->textArea->end();
                    #}    
                    //app()->form('MainForm')->$data->text='*Message deleted*';
                    
                });
                
                $copy = new UXMenuItem('Copy message'); 
                $copy->on('action', function() use ($message){     
                    $data = app()->form('MainForm')->Messages->selectedItem->id; 
                    $data=str_replace('_main', '', $data);
                    $edit_message=true;
                    $message_id=$data;
                    UXClipboard::setText(app()->form('MainForm')->$data->text);
                    app()->form('MainForm')->toast('Текст сообщения успешно скопирован!');
                    //app()->form('MainForm')->$data->text='*Message deleted*';
                    
                });
                 
                app()->form('MainForm')->Messages->contextMenu->items->add($forward);
                app()->form('MainForm')->Messages->contextMenu->items->add($delete);
                app()->form('MainForm')->Messages->contextMenu->items->add($edit);
                app()->form('MainForm')->Messages->contextMenu->items->add($copy);
                
            
            $pic = new UXImageArea;
            $pic->size = [50, 50];
            $pic->stretch = true;
            Element::loadContentAsync($pic, $message['user_photo'],function () use ($this,$pic) {
                app()->form('MainForm')->setBorderRadius($pic, 255);
            });
            $name_box = new UXHBox;
            $name = new UXLabel;
            $name->textColor = '#8bc34a';
            $name->font->bold = true;
            $name->text = $message['first_name'].' '.$message['last_name'];
            $name_box->add($name);
            $rand=rand(0,100);
            $verif = new UXImageView; 
            if ($user['verified'] == 1) 
            { 
                Element::loadContentAsync($verif, 'res://.data/img/icons8-verified-account-16.png'); 
                $name_box->add($verif);
                
             }   
            $msg = new UXLabel;
            if($message['edited']==1){$message['message']=urldecode($message['message']).' (edit)';}
            $msg->text = urldecode($message['message']);
            $msg->textColor = '#FFFFFF';
            $msg->font->size = 14;
            $msg->id=$message['id'];
            $date = new UXLabel;
            $date->text = $message['create_date'];
            $date->textColor = '#999';
            $date->font->size = 12;
            $adinfo = new UXHBox([$date]);
            
            $fwds = new UXVBox;
            if(isset($message['fwd']) and $message['fwd']!='null'){
                //$message['fwd']=str_replace('\"', '"', $message['fwd']);
                $message['fwd']=urldecode($message['fwd']);
                $message['fwd']=json_decode($message['fwd'],1);
                //var_dump($message);
                foreach ($message['fwd'] as $key => $fwd)
                {
                    $fwds->add(self::renderFWD($fwd));
                }
            }
            
            $image = new UXImageArea;
            $document = new UXHBox;
            if(isset($message['attachment']) and $message['attachment']!='null'){
                $message['attachment']=json_decode(urldecode($message['attachment']),1);
                //var_dump($message);
                global $attachments;
                $attachments[$message['id']]=$message['attachment'];
                if(isset($message['attachment']['photo'])){
                    $image->size = [250, 150];
                    $image->stretch = true;
                    Element::loadContentAsync($image, $message['attachment']['photo']['url'],function () use ($this,$pic) {
                       
                    });
                    $image->on('click', function () use($image, $message){
                        if(isset($message['attachment']['photo'])){
                            @mkdir(System::getProperty('user.home').'/NextMessenger');
                            @mkdir(System::getProperty('user.home').'/NextMessenger/images');
                            global $open_file;
                            $open_file=System::getProperty('user.home').'/NextMessenger/images/'.$message['attachment']['photo']['d_filename'];
                            $downloader=new HttpDownloader;
                            $downloader->destDirectory = System::getProperty('user.home').'/NextMessenger/images/'; 
                            $downloader->urls = [ $message['attachment']['photo']['url'] ]; 
                            $downloader->start();
                            
                            $downloader->on('successAll', function() use($open_file){ 
                                open($open_file);
                            });
                        }
                    });
                }elseif(isset($message['attachment']['doc'])){
                    $document->paddingTop=5;
                    $document->paddingBottom=5;
                    $document_icon=new UXImageView;
                    $document_icon->size = [30, 30];
                    Element::loadContentAsync($document_icon, 'res://.data/img/icons8-document-50.png'); 
                    $document_text_box = new UXVBox;
                    $document_text=new UXLabel($message['attachment']['doc']['filename']);
                    $document_text->style="
                        -fx-font-size: 12px;
                    ";
                    $document_text->textColor='white';
                    $document_size=new UXLabel(self::get_filesize($message['attachment']['doc']['size']/1024));
                    $document_size->style="
                        -fx-font-size: 9px;
                    ";
                    $document_size->textColor='gray';
                    
                    $document_text_box->add($document_text);
                    $document_text_box->add($document_size);
                    $document->add($document_icon);
                    $document->add($document_text_box);
                    
                    $document->on('click', function () use($document, $message){
                        if(isset($message['attachment']['doc'])){
                            @mkdir(System::getProperty('user.home').'/NextMessenger');
                            @mkdir(System::getProperty('user.home').'/NextMessenger/images');
                            global $open_file;
                            $open_file=System::getProperty('user.home').'/NextMessenger/images/'.$message['attachment']['doc']['d_filename'];
                            $downloader=new HttpDownloader;
                            $downloader->destDirectory = System::getProperty('user.home').'/NextMessenger/images/'; 
                            $downloader->urls = [ $message['attachment']['doc']['url'] ]; 
                            $downloader->start();
                            
                            $downloader->on('successAll', function() use($open_file){ 
                                open($open_file);
                            });
                        }
                    });
                }    
            }    
    
            
            $data = new UXVBox([$name_box,$msg,$image,$document,$fwds,$adinfo]);
            $data->padding = 5;
            $data->style = 
            "
            -fx-background-color: #34393f;
            -fx-background-radius: 10px;
            ";
            if ($out == 0)
            {
                $data->translateX = 5;
                $main = new UXHBox([$pic,$data]);
                $main->width = app()->form('MainForm')->Messages->width;
                $main->alignment = "TOP_LEFT";
            }
            else 
            {
                $data->translateX = -5;
                $main = new UXHBox([$data,$pic]);
                $main->width = app()->form('MainForm')->Messages->width;
                $main->alignment = "TOP_RIGHT";
            }
                $timer = new UXAnimationTimer(function () use ($data) {
                $data->width = app()->form('MainForm')->Messages->width*0.75;
            });
            $main->id = $message['id'].'_main';
            $timer->start();
            $msg->maxWidth = app()->form('MainForm')->Messages->width*0.75;
            $msg->wrapText = true;
            
            $main->opacity=0;
            
            if($paint==true){
                app()->form('MainForm')->Messages->items->add($main);
            }
            Animation::fadeIn($main, 200);
            
            if($paint==true){
                $index = app()->form('MainForm')->Messages->items->count()-1;
                app()->form('MainForm')->Messages->scrollTo($index);    
            }
            
            if($paint==false){
                return $main;
            }
               
        }
        
        public static function setActivity($chat_id, $text, $uid){
            global $old_count, $chat_info;
            if(!isset($old_count) and !$old_count){
                $old_count=app()->form('MainForm')->count->text;
            }    
            if($chat_info['response']['chat_id']==$chat_id){
                
                global $user_data_cache;
                if(!isset($user_data_cache[$uid])){
                    $user_data_cache[$uid]=self::query('users.get', ['user_id'=>$uid])['response'];
                }
                
                app()->form('MainForm')->count->text=$user_data_cache[$uid]['first_name'].' '.$text;
                self::query('messages.setAcvitity', ['chat_id'=>$chat_id, 'activity'=>$text, 'uid'=>$uid]);
                
                
                Timer::after('5s', function () {
                    UXApplication::runLater(function () {
                      //  var_dump('dsasdffd');
                        global $old_count;
                        app()->form('MainForm')->count->text=count(explode(',',$chat_info['response']['users']).' members');
                    });
                });
            }else{
                app()->form('MainForm')->count->text=count(explode(',',$chat_info['response']['users']).' members');
            }
        }
    
        public static function renderFWD($fwd)
        {
        
        
            $v = new UXSeparator;
            $v->orientation = 'VERTICAL';
            $v->paddingLeft = 10;
            $fname = new UXLabel(urldecode($fwd['first_name']).' '.urldecode($fwd['last_name']));
            $fname->textColor = '#8bc34a';
            $ftext = new UXLabel(urldecode($fwd['message']));
            $ftext->textColor = '#FFFFFF';
            $ftext->font->size = 14;
            $fdate = new UXLabel($fwd['create_date']);
            $fdate->textColor = '#999';
            $fdate->font->size = 12;
            $fffwd = new UXVBox;
            
            if(isset($fwd['fwd']) and $fwd['fwd']!='null'){
                //$fwd['fwd']=str_replace('\"', '"', $fwd['fwd']);
              //  $fwd['fwd']=urldecode($fwd['fwd']);
              //  $fwd['fwd']=json_decode($fwd['fwd'],1);
             }   
            
            if (isset($fwd['fwd']) and $fwd['fwd']!='null')
            {
             //   var_dump($fwd);
                foreach ($fwd['fwd'] as $ffwd)
                {
                  //  $ffwd['fwd']=urldecode($ffwd['fwd']);
                 //   $ffwd['fwd']=json_decode($ffwd['fwd'],1);
                   // var_dump($ffwd);
                    $fffwd->add(self::renderFWD($ffwd));
                }
            }
                $image = new UXImageArea;
                $document = new UXHBox;
                if(isset($fwd['attachment']) and $fwd['attachment']!='null'){
                $fwd['attachment']=json_decode(urldecode($fwd['attachment']),1);
                //var_dump($message);
                if(isset($fwd['attachment']['photo'])){
                    $image->size = [250, 150];
                    $image->stretch = true;
                    $image->proportional=true;
                    Element::loadContentAsync($image, $fwd['attachment']['photo']['url'],function () use ($this,$pic) {
                       
                    });
                    $image->on('click', function () use($image, $fwd){
                        if(isset($fwd['attachment']['photo'])){
                            @mkdir(System::getProperty('user.home').'/NextMessenger/images');
                            global $open_file;
                            $open_file=System::getProperty('user.home').'/NextMessenger/images/'.$fwd['attachment']['photo']['d_filename'];
                            $downloader=new HttpDownloader;
                            $downloader->destDirectory = System::getProperty('user.home').'/NextMessenger/images/'; 
                            $downloader->urls = [ $fwd['attachment']['photo']['url'] ]; 
                            $downloader->start();
                            
                            $downloader->on('successAll', function() use($open_file){ 
                                open($open_file);
                            });
                        }
                    });
                }elseif(isset($fwd['attachment']['doc'])){
                    $document->paddingTop=5;
                    $document->paddingBottom=5;
                    $document_icon=new UXImageView;
                    $document_icon->size = [30, 30];
                    Element::loadContentAsync($document_icon, 'res://.data/img/icons8-document-50.png'); 
                    $document_text_box = new UXVBox;
                    $document_text=new UXLabel($fwd['attachment']['doc']['filename']);
                    $document_text->style="
                        -fx-font-size: 12px;
                    ";
                    $document_text->textColor='white';
                    $document_size=new UXLabel(self::get_filesize($fwd['attachment']['doc']['size']/1024));
                    $document_size->style="
                        -fx-font-size: 9px;
                    ";
                    $document_size->textColor='gray';
                    
                    $document_text_box->add($document_text);
                    $document_text_box->add($document_size);
                    $document->add($document_icon);
                    $document->add($document_text_box);
                    
                    $document->on('click', function () use($image, $fwd){
                        if(isset($fwd['attachment']['doc'])){
                            @mkdir(System::getProperty('user.home').'/NextMessenger/images');
                            global $open_file;
                            $open_file=System::getProperty('user.home').'/NextMessenger/images/'.$fwd['attachment']['doc']['d_filename'];
                            $downloader=new HttpDownloader;
                            $downloader->destDirectory = System::getProperty('user.home').'/NextMessenger/images/'; 
                            $downloader->urls = [ $fwd['attachment']['doc']['url'] ]; 
                            $downloader->start();
                            
                            $downloader->on('successAll', function() use($open_file){ 
                                open($open_file);
                            });
                        }
                    });
                }    
                
            }  
            
            $fdata = new UXVBox([$fname,$ftext,$image,$document,$fffwd,$fdate]);
            $fdata->padding=5;
            $fmain = new UXHBox([$v,$fdata]);
            return $fmain;
        }
    
        public static function checkAuth($login = '', $password = '')
        {
            Logger::info('checkAuth');
            if ($login == '' and $password == '')
            {
                if (file_exists(self::$tokenFile) == true)
                {
                    self::$token = file_get_contents(self::$tokenFile);
                    return true;
                }
            }
            else 
            {
                $reqe = new HttpClient;
                $reqe->userAgent = self::$useragent;
                $reqe->responseType = 'JSON';
                $a = $reqe->get(self::$host."/method/auth.login?".http_build_query(['login'=>$login,'password'=>$password]));
                if (isset($a->body()['error']))
                {
                    self::onError($a->body());
                }
                else 
                {
                    file_put_contents(self::$tokenFile, $a->body()['response']['access_token']);
                    self::$token = $a->body()['response']['access_token'];
                    return true;
                }
            }
        }
        
        public static function register($firstname,$lastname,$nickname,$login,$password)
        {
            $reqe = new HttpClient;
            $reqe->userAgent = self::$useragent;
            $reqe->responseType = 'JSON';
            $a = $reqe->get(self::$host."/method/auth.register?".http_build_query(['login'=>$login,'password'=>$password, 'nickname'=>$nickname,'first_name'=>$firstname, 'last_name'=>$lastname]));
            if (isset($a->body()['error']))
            {
                self::onError($a->body());
            }
            else 
            {
                self::$token = $a->body()['response']['access_token'];
                file_put_contents(self::$tokenFile, $a->body()['response']['access_token']);
                app()->hideForm('register');
                app()->showNewForm('MainForm');
            }
        }
        
        public static function query($method, $params = [])
        {
            Logger::info('query');
            $reqe = new HttpClient;
            $reqe->userAgent = self::$useragent;
            $reqe->responseType = 'JSON';
            $params['access_token'] = self::$token;
           $a  = $reqe->get(self::$host."/method/".$method.'?'.http_build_query($params));
           if (isset($a->body()['error']))
           {
               if ($a->body()['error']['error_code'] == 1)
               {
                   self::$token = '';
                   unlink(self::$tokenFile);
                   app()->hideForm('MainForm');
                   app()->showNewForm('authUser');
               }
               else 
               {
                   self::onError($a->body());
               }
           }
           else 
           {
               return $a->body();
           }
        }
        
        public static function onError($error)
        {
            Logger::info('onError');
            $dialog = new UXAlert('ERROR');
            $dialog->title = 'Error';
            $dialog->headerText = 'Server given an error !';
            $dialog->contentText = $error['error']['error_message'];
            $dialog->expanded = true;
            $dialog->showAndWait();
        }
        
        public static function loadFile($file)
        {
            Logger::info('loadFile');
        }
        
        public static function checkServer()
        {
           // Logger::info('query '.Time::now()->toString('dd.MM.yyyy HH:mm:ss'));
            $reqe = new HttpClient;
            $reqe->userAgent = self::$useragent;
            Logger::info(self::$host."/method/status");
            $a  = $reqe->get(self::$host."/method/status");
             if ($a->body() == 'ok')
            {
                return true;
            }
            else
            {
                return false;
            }
        }
        
        public static function logout()
        {
            self::$token = '';
            unlink(self::$tokenFile);
            app()->shutdown();
        }
        
       public static function addServerMessage($chat_id,$text, $date, $paint=true) { 
            global $chat_info;
            if($chat_info['response']['chat_id']==$chat_id){
                $main = new UXHBox; 
                $main->alignment = "CENTER"; 
                $main->style = 
                "
                -fx-border-width: 1 0 1 0; 
                -fx-border-color: gray blue gray yellow;
                ";
                $label = new UXLabel; 
                $label->text = $text.' ('.$date.')'; 
                $label->textColor='white';
                $label->paddingTop=10;
                $label->paddingBottom=10; 
               // $label->wrapText = true;
               
                $main->add($label);
                $main->opacity=0;
            
                Animation::fadeIn($main, 300);
                if($paint==true){
                    app()->form('MainForm')->Messages->items->add($main); 
                }
                return $main;    
            }    
        }
        
        public static function lp_poll($continue=false, $ts=''){
            Logger::info('Longpoll connect');
            
          $thread = new Thread(function() use($continue){ 
                global $ts;
                if($continue==false){
                    $lp_get = json_decode(file_get_contents(self::$host.'/longpoll/get?'.http_build_query(['access_token'=>self::$token])),1);
                    $ts=$lp_get['response']['ts'];
                }
                while(true){
                    $data=json_decode(file_get_contents(self::$host.'/longpoll/poll?'.http_build_query(['access_token'=>self::$token, 'ts'=>$ts])),1);
                    if(isset($data['response']['updates']) and isset($data['response']['updates'][0])){
                        $ts=$data['response']['ts'];
                        break;
                    }
                    usleep(2000);
                }
                UXApplication::runLater(function () use ($data, $ts) {
                    //alert(json_encode($data));
                    global $user_info;
                    global $user_data_cache;
                    global $ts, $user_info, $chat_info;
                    $ts=$data['response']['ts'];
                    foreach ($data['response']['updates'] as $update)
                    {
                        
                        var_dump($update);
                        
                        if($update['type']=='message'){
                          /*  if(intval($userdata['uid'])==intval($user_info['response']['uid'])){
                                continue;
                            }*/
                            
                            if(isset($update['server_message']) and $update['server_message']==1){
                                
                                self::addServerMessage($update['chat_id'], $update['message'], $update['create_date']);
                                
                            }else{
                            
                                $update['message']=urldecode($update['message']);
                                
                                $chat=$update['chat_id'].'chat_d';
                                app()->form('MainForm')->{$chat}->text=$update['message'];
                                
                                if(isset($user_data_cache[$update['from_id']])){
                                    $userdata=$user_data_cache[$update['from_id']];
                                }else{
                                    $userdata = nextModule::query('users.get', ['uid'=>$update['from_id']])['response'];
                                    $user_data_cache[$update['from_id']]=$userdata;
                                }
                                
                                if($user_info['response']['uid']==$userdata['uid']){$out=1;}else{$out=0;}
                                global $chat_info;
                                if($update['chat_id']==$chat_info['response']['chat_id']){
                               
                                    self::addMessage($userdata, $update, $out);
                                    
                                    //Уведомление
                                    global $user_info;
                                    if(intval($userdata['uid'])!=intval($user_info['response']['uid'])){
                                        app()->module(tray)->systemTray->displayMessage('Новое сообщение', $userdata['first_name'].' '.$userdata['last_name'].': '.$update['message']);
                                    }
                                }
                            }
                        }elseif($update['type']=='create_chat'){
                            $main = new UXHBox;
                            $main->style = "-fx-padding: 5px;";
                            $photo = new UXImageView;
                            $photo->size = [50,50];
                            Element::loadContentAsync($photo, $update['photo'], function () use ($this, $photo) {
                                app()->form('MainForm')->setBorderRadius($photo, 255);
                            });
                            $body = new UXVBox;
                            $body->paddingLeft = 5;
                            $name = new UXLabel($update['title']);
                            $name->font->bold = true;
                            $name->textColor = '#FFFFFF';
                            $message = new UXLabel('');
                            $message->id=$update['chat_id'].'chat_d';
                            $message->textColor = '#707378';
                            $main->add($photo);
                            $main->id=$update['chat_id'].'_chat';
                            $body->add($name);
                            $body->add($message);
                            $main->add($body);
                            $main->on('click', function () use ($update) {
                                global $chat_info;
                                $chat_info = nextModule::query('messages.getChat', ['chat_id'=>$update['chat_id']]);
                                app()->form('MainForm')->chat_title->show();
                                app()->form('MainForm')->chat_title->text=$chat_info['response']['title'];
                                app()->form('MainForm')->count->show();
                                $users=explode(',',$chat_info['response']['users']);
                                app()->form('MainForm')->count->text= count($users).' members';
                                app()->form('MainForm')->getMessages($update['chat_id']);
                            });
                            app()->form('MainForm')->Dialogs->items->add($main);
                            
                        }elseif($update['type']=='edit_message'){
                            var_dump($chat_info);
                            if($update['chat_id']==$chat_info['response']['chat_id']){
                                $message_id=$update['id'];
                                app()->form('MainForm')->{$message_id}->text=$update['message'].' (edit)';
                            }    
                        }elseif ($update['type']=='message_delete'){
                            if($update['chat_id']==$chat_info['response']['chat_id']){
                                $message_id=$update['message_id'];
                                app()->form('MainForm')->{$message_id}->text='*Message deleted*';
                            }    
                            
                        }elseif($update['type']=='chat_invite_user'){
                            if($update['chat_id']==$chat_info['response']['chat_id']){
                              app()->form('MainForm')->count->text=app()->form('MainForm')->count->text+=1;
                            }
                            
                          $main = new UXHBox;
                            $main->style = "-fx-padding: 5px;";
                            $photo = new UXImageView;
                            $photo->size = [50,50];
                            Element::loadContentAsync($photo, $update['photo'], function () use ($this, $photo) {
                                app()->form('MainForm')->setBorderRadius($photo, 255);
                            });
                            $body = new UXVBox;
                            $body->paddingLeft = 5;
                            $name = new UXLabel($update['title']);
                            $name->font->bold = true;
                            $name->textColor = '#FFFFFF';
                            $message = new UXLabel('');
                            $message->id=$update['chat_id'].'chat_d';
                            $message->textColor = '#707378';
                            $main->add($photo);
                            $main->id=$update['chat_id'].'_chat';
                            $body->add($name);
                            $body->add($message);
                            $main->add($body);
                            $main->on('click', function () use ($update) {
                                global $chat_info;
                                $chat_info = nextModule::query('messages.getChat', ['chat_id'=>$update['chat_id']]);
                                app()->form('MainForm')->chat_title->show();
                                app()->form('MainForm')->chat_title->text=$chat_info['response']['title'];
                                app()->form('MainForm')->count->show();
                                $users=explode(',',$chat_info['response']['users']);
                                app()->form('MainForm')->count->text= count($users).' members';
                                app()->form('MainForm')->getMessages($update['chat_id']);
                            });
                            app()->form('MainForm')->Dialogs->items->add($main);
                        }elseif($update['type']=='user_typing'){
                            global $user_data_cache,$chat_info;
                            if(!isset($user_data_cache[$uid])){
                                $user_data_cache[$uid]=self::query('users.get', ['user_id'=>$uid])['response'];
                            }
                            
                            app()->form('MainForm')->count->text=$user_data_cache[$uid]['first_name'].' '.$text;
                            
                            Timer::after('5s', function () {
                                UXApplication::runLater(function () {
                                  //  var_dump('dsasdffd');
                                    global $old_count;
                                    app()->form('MainForm')->count->text=count(explode(',',$chat_info['users']));
                                });
                            });
                        }elseif($update['type']=='chat_photo_update'){
                            app()->form('MainForm')->Dialogs->items->clear();
                            app()->form('MainForm')->getChats();
                        }
                    }
                    self::lp_poll(true, $ts);
                    
                });
           });
           $thread->start();
        }
        
    }
if(!function_exists('http_build_query')){
    function http_build_query($a,$b='',$c=0)
     {
            if (!is_array($a)) return false;
            foreach ((array)$a as $k=>$v)
            {
                if ($c)
                {
                    if( is_numeric($k) )
                        $k=$b."[]";
                    else
                        $k=$b."[$k]";
                }
                else
                {   if (is_int($k))
                        $k=$b.$k;
                }

                if (is_array($v)||is_object($v))
                {
                    $r[]=http_build_query($v,$k,1);
                        continue;
                }
                $r[]=urlencode($k)."=".urlencode($v);
            }
            return implode("&",$r);
            }
}
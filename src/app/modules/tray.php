<?php
namespace app\modules;

use bundle\jurl\jURL;
use std, gui, framework, app;


class tray extends AbstractModule
{

    /**
     * @event timer.action 
     */
    function doTimerAction(ScriptEvent $e = null)
    {    
        //var_dump('timer');
        global $sec;
        $sec++;
        if($sec>5){$sec=0;}
    }

    /**
     * @event downloader.successAll 
     */
    function doDownloaderSuccessAll(ScriptEvent $e = null)
    {    
        global $open_file;
        open($open_file);
    }
    
    
    public function getExtension( $filename ) {
        $explode=explode( '.', $filename );
    return $explode[count($explode)-1];
    }

    /**
     * @event hotkey.action 
     */
    function doHotkeyAction(ScriptEvent $e = null)
    {    
        $image = UXClipboard::getImage();
        $dir=System::getProperty('user.home').'/NextMessenger/images/';
        if($image!=null){
            @mkdir($dir);
            var_dump($image);
            app()->form('MainForm')->image3->image = $image;
            app()->form('MainForm')->image3->image->save($dir.'image.png');
            $file=$dir.'image.jpg';
            $ch = new jURL(__UPLOAD__.'/upload/index.php');
            $ch->setRequestMethod('POST');
            $ch->addPostFile('file', $file);
            $ch->asyncExec(function($result){
            global $attachment;
            $result=json_decode($result,1);
            if(in_array($this->getExtension($result['response']['filename']), ['png', 'jpg'])){
                $attachment['photo']=$result['response'];
                var_dump($result);
                app()->form('MainForm')->toast("Файл прикреплён к сообщению");
                }    
            }); 
        }
    }


}

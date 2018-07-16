<?php
namespace app\forms;

use bundle\jurl\jURL;
use std, gui, framework, app;


class Profile extends AbstractForm
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
       $user_info=nextModule::query('users.get')['response'];
       //var_dump($user_info);
       $pic=$this->imageAlt;
        Element::loadContentAsync($pic, $user_info['photo_100'],function () use ($this,$pic) {
            app()->form('MainForm')->setBorderRadius($pic, 255);
        });
        $this->username->text=$user_info['first_name'].' '.$user_info['last_name'];
    }


    /**
     * @event image.click-Left 
     */
    function doImageClickLeft(UXMouseEvent $e = null)
    {    
        $this->form('MainForm')->show();
        $this->form('Profile')->hide();
    }

    /**
     * @event buttonAlt.click-Left 
     */
    function doButtonAltClickLeft(UXMouseEvent $e = null)
    {    
        $this->fileChooser->execute();
        $file=$this->fileChooser->file->getPath();
        if(in_array($this->getExtension($file), ['png', 'jpg'])){
            $ch = new jURL('https://s1.unfox.ru/upload/index.php');
            $ch->setRequestMethod('POST');
            $ch->addPostFile('file', $file);
            $ch->asyncExec(function($result){
                $result=json_decode($result,1)['response'];
                nextModule::query('profile.updatePhoto', ['photo'=>$result['url']]);
                $pic=$this->imageAlt;
                Element::loadContentAsync($pic, $result['url'],function () use ($this,$pic) {
                    app()->form('MainForm')->setBorderRadius($pic, 255);
                });
            });
        }
    }
    
    public function getExtension( $filename ) {
        $explode=explode( '.', $filename );
        return $explode[count($explode)-1];
    }

    

}

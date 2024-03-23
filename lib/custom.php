<?php
namespace skerbis\terminal;

use rex_path;

class CustomCommands {


    public static function basepath(): string 
    {
        return rex_path::base();
    }

    public static function console($args = ''): string 
    {
        if ($args!='') 
        {
            $arguments = ' '.implode(' ', $args);
        }
        $command = new TerminalPHP();
        return '<pre>'.$command->runCommand('php '.rex_path::base().'redaxo/bin/console'.$arguments).'</pre>';
    }


}

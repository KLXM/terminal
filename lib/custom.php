<?php
namespace skerbis\terminal;

use rex_path;

class CustomCommands {


    public static function basepath(): string 
    {
        return rex_path::base();
    }

    public static function console(): string 
    {
        return TerminalPHP::runCommand(rex_path::base().'/redaxo/bin/php console');
    }


}

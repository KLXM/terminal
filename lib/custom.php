<?php
namespace skerbis\terminal;

use rex_path;

class CustomCommands {

public static function showconfig() {
        $configPath = rex_path::coreData('config.yml');

        if (file_exists($configPath)) {
            $configContent = file_get_contents($configPath);
            return '<pre>' . htmlspecialchars($configContent) . '</pre>';
        } else {
            return '<pre>Config not found</pre>';
        }
    }

    public static function basepath(): string 
    {
        return rex_path::base();
    }

     public static function help(): string 
    {
        $out = '<h1>REDAXO Terminal</h1>
<pre>Welcome to the REDAXO Terminal.
The terminal provides a reduced set of commands and is mainly used to call the REDAXO console.

The console is accessed with the command console.
Parameters can also be passed directly, such as console package:list.

Default commands
-------
help, cd, chown, date, df, echo, ffmpeg, find, free, git, grep, hostname, ls, tail, php, ping, pwd, whoami

Special REDAXO commands
-------
- showconfig
- basepath
- console



</pre>';

        return $out;
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

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
    return '<h1>REDAXO Terminal</h1>
<pre>
Welcome to the REDAXO Terminal.
The terminal provides an extended set of commands, including standard Unix-like commands, 
REDAXO-specific commands, and access to the REDAXO console.

Default Commands
----------------
cd, chown, date, df, echo, ffmpeg, find, free, git, grep, hostname, ls, 
php, ping, pwd, tail, whoami

Special REDAXO Commands
-----------------------
- showconfig: Display the contents of the REDAXO config.yml file
- basepath: Show the base path of the REDAXO installation
- console [args]: Access the REDAXO console. You can pass arguments directly, e.g., console package:list

Using the Console
-----------------
To use the REDAXO console, type `console` followed by the desired command and arguments. For example:
- console package:list: List all packages
- console cache:clear: Clear the REDAXO cache

For more information on available console commands, use `console list` or `console help [command]`.

Examples
--------
1. Show REDAXO configuration:
   showconfig

2. Display REDAXO base path:
   basepath

3. List REDAXO packages:
   console package:list

4. Navigate to the REDAXO root directory:
   cd $(basepath)

5. List files in the current directory:
   ls

Type `help` anytime to see this information again.
</pre>';
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

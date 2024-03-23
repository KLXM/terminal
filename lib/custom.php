<?php
namespace skerbis\terminal;

class CustomCommands {

    /***************************************************************
     *                 Add Your Custom Command Here                *
     ***************************************************************
     *    note 1: Function Name is Command and return is Result    *
     *    note 2: $a is array of arguments                         *
     * *************************************************************/

    public static function hi($a): string 
    {
        return 'Hi '.implode(' ', $a);
    }

    public static function md5($a): string
    {
        $input = implode(' ', $a);
        if ($input)
            return md5($input);
        else
            return 'write something, example:<br>md5 test';
    }

    public static function developer(): string 
    {
        return 'SmartWF<br><a href="https://github.com/smartwf" target="_blank">github</a> &nbsp; &nbsp; <a href="mailto:hi@smartwf.ir" target="_blank">mail</a> &nbsp; &nbsp; <a href="http://twitter.com/smartwf" target="_blank">twitter</a>';
    }
}

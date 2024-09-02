<?php
namespace skerbis\terminal;

/**
 * Terminal.php - Terminal Emulator for PHP
 *
 * @package  Terminal.php
 * @author   SmartWF <hi@smartwf.ir>
 */

class TerminalPHP
{
    private array $allowed_commands = [
            'cd',
            'chown',
            'composer',
            'date',
            'df',
            'echo',
            'ffmpeg',
            'find',
            'free',
            'git',
            'grep',
            'hostname',
            'ls',
            'php',
            'pwd',
            'tail',
            'whoami',
    ];

    public function __construct(string $path = '')
    {
        $this->_cd($path);
    }

    private function shell(string $cmd): string
    {
        return trim(shell_exec($cmd) ?? '');
    }

    private function commandExists(string $command): bool
    {
        return !empty($this->shell('command -v ' . $command));
    }

    public function __call(string $cmd, array $arg): string
    {
        return $this->runCommand($cmd . (isset($arg[0]) ? ' ' . $arg[0] : ''));
    }

    public function runCommand(string $command): string
    {
        $cmd = explode(' ', $command)[0];
        $arg = count(explode(' ', $command)) > 1 ? implode(' ', array_slice(explode(' ', $command), 1)) : '';

        if (array_search($cmd, $this->getLocalCommands()) !== false) {
            $lcmd = '_' . $cmd;
            return $this->$lcmd($arg);
        }

        if (in_array($cmd, $this->allowed_commands)) {
            return trim(shell_exec($command) ?? '');
        } else {
            return 'terminal.php: Permission denied for command: ' . $cmd;
        }
    }

    public function normalizeHtml(string $input): string
    {
        return str_replace(['<', '>', "\n", "\t", ' '], ['&lt;', '&gt;', '<br>', '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', '&nbsp;'], $input);
    }
    /**
     * Array of Local Commands
     * @return array
     */
    private function getLocalCommands(): array
    {
        $commands = array_filter(get_class_methods($this), function ($i) {return ($i[0] == '_' && $i[1] != '_') ? true : false;});
        foreach ($commands as $i => $command) {
            $commands[$i] = substr($command, 1);
        }

        return $commands;
    }

    private function _cd(string $path): string
    {
        if ($path) {
            chdir($path);
        }
        return '';
    }

    private function _moin(): string
    {
        return 'Selber';
    }

    private function _pwd(): string
    {
        return getcwd() ?: '';
    }

    private function _ping(string $a): string
    {
        if (strpos($a, '-c ') !== false) {
            return trim(shell_exec('ping ' . $a) ?? '');
        }
        return trim(shell_exec('ping -c 4 ' . $a) ?? '');
    }
}

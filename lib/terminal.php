<?php
namespace skerbis\terminal;

/**
 * Terminal.php - Terminal Emulator for PHP
 *
 * @package  Terminal.php
 * @author   SmartWF <hi@smartwf.ir>
 * @modified by Claude
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
    
    private string $current_directory;
    private array $environment_variables = [];

    public function __construct(string $path = '')
    {
        $this->current_directory = $path ?: getcwd();
        $this->environment_variables['HOME'] = $this->current_directory;
    }

    private function shell(string $cmd): string
    {
        return trim(shell_exec($cmd) ?? '');
    }

    private function commandExists(string $command): bool
    {
        return !empty($this->shell('command -v ' . escapeshellarg($command)));
    }

    public function __call(string $cmd, array $arg): string
    {
        return $this->runCommand($cmd . (isset($arg[0]) ? ' ' . $arg[0] : ''));
    }

    public function runCommand(string $command): string
    {
        $parts = explode(' ', $command, 2);
        $cmd = $parts[0];
        $arg = $parts[1] ?? '';

        // Handle environment variable expansion
        $arg = preg_replace_callback('/\$(\w+)/', function($matches) {
            return $this->environment_variables[$matches[1]] ?? '';
        }, $arg);

        if (method_exists($this, '_' . $cmd)) {
            $method = '_' . $cmd;
            return $this->$method($arg);
        }

        if (in_array($cmd, $this->allowed_commands)) {
            return trim(shell_exec($command) ?? '');
        } else {
            return "terminal.php: Permission denied for command: $cmd";
        }
    }

    public function normalizeHtml(string $input): string
    {
        return str_replace(
            ['<', '>', "\n", "\t", ' '],
            ['&lt;', '&gt;', '<br>', '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', '&nbsp;'],
            $input
        );
    }

    private function _cd(string $path): string
    {
        $new_path = realpath($this->current_directory . '/' . $path);
        if ($new_path && is_dir($new_path)) {
            $this->current_directory = $new_path;
            return '';
        }
        return "cd: $path: No such directory";
    }

    private function _pwd(): string
    {
        return $this->current_directory;
    }

    private function _echo(string $arg): string
    {
        return $arg;
    }

    private function _ls(string $arg = ''): string
    {
        $path = $arg ?: $this->current_directory;
        $items = scandir($path);
        return implode("\n", array_diff($items, ['.', '..']));
    }

    private function _export(string $arg): string
    {
        $parts = explode('=', $arg, 2);
        if (count($parts) === 2) {
            $this->environment_variables[$parts[0]] = $parts[1];
            return '';
        }
        return "export: Invalid syntax";
    }

    private function _env(): string
    {
        $output = '';
        foreach ($this->environment_variables as $key => $value) {
            $output .= "$key=$value\n";
        }
        return rtrim($output);
    }

    private function _moin(): string
    {
        return 'Selber';
    }

    private function _ping(string $a): string
    {
        if (strpos($a, '-c ') !== false) {
            return $this->shell('ping ' . escapeshellarg($a));
        }
        return $this->shell('ping -c 4 ' . escapeshellarg($a));
    }
}

<?php
namespace skerbis\terminal;

// Überprüfen Sie, ob die Anfrage per AJAX gesendet wurde und ob der 'command'-Parameter gesetzt ist.
if (
    !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
    && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
    && isset($_POST['command'])
) {
    // Explodieren Sie den 'command'-String in ein Array, trennen Sie den Befehl von den Argumenten.
    $commandParts = explode(' ', $_REQUEST['command']);
    $command = $commandParts[0];
    $arguments = array_slice($commandParts, 1);

    // Überprüfen Sie, ob ein 'path'-Parameter übergeben wurde, ansonsten verwenden Sie einen leeren String.
    $path = $_REQUEST['path'] ?? '';

    // Instanziieren Sie die TerminalPHP-Klasse mit dem übergebenen Pfad.
    $terminal = new TerminalPHP($path);

    // Überprüfen Sie, ob der Befehl in den CustomCommands-Klassenmethoden existiert.
    if (in_array($command, get_class_methods('CustomCommands'), true)) {
        // Wenn ja, führen Sie den Befehl aus und geben Sie das Ergebnis im JSON-Format zurück.
        echo json_encode([
            'result' => CustomCommands::{$command}($arguments),
            'path' => $terminal->pwd(),
        ]);
    } else {
        // Andernfalls führen Sie den Befehl im Terminal aus und geben Sie das Ergebnis im JSON-Format zurück.
        echo json_encode([
            'result' => $terminal->normalizeHtml($terminal->runCommand($_REQUEST['command'])),
            'path' => $terminal->pwd(),
        ]);
    }
} else {
    $terminal = new TerminalPHP();
    ?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>Terminal.php</title>
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <link href="https://cdn.rawgit.com/rastikerdar/vazir-code-font/v1.1.2/dist/font-face.css" rel="stylesheet" type="text/css" />
    <style>
      :root{
          --font: 'Vazir Code', 'Vazir Code Hack';
          --font-size: 16px;
          --primary-color: #101010;
          --color-scheme-1: #55c2f9;
          --color-scheme-2: #ff5c57;
          --color-scheme-3: #5af68d;
          --scrollbar-color: #181818;
          --title-color: white;
          --blink-color: #979797;
          --blink: '|';
          --separator: '--->';
      }
      ::-webkit-scrollbar { width: 7px; }
      ::-webkit-scrollbar-track {  background: rgba(0,0,0,0); }
      ::-webkit-scrollbar-thumb { background: var(--scrollbar-color); border-radius: 5px; }
      *{ font-family: var(--font);}
      a{ color: #29a9ff; }
      terminal{ display: block; width: 80vw;  height: 80vh; position: relative; margin: 2vw auto; background: inherit; border-radius: 10px; max-width: 70rem; overflow: hidden; }
      terminal::before,
      terminal::after{ content: ''; position: absolute; left: 0; top: 0; height: 100%; width: 100%; border-radius: 10px; }
      terminal::before{ background: inherit; filter: blur(.5rem); }
      terminal::after{ background: var(--primary-color); opacity: .80; }
      terminal header{ position: absolute; width: 100%; height: 45px; background: var(--primary-color); z-index: 1; border-radius: 10px 10px 0 0; user-select: none; }
      terminal header title{ display: block; position: absolute; left: 0; top: 0; width: 100%; height: 100%; text-align: center; color: var(--title-color); line-height: 45px; opacity: .8; z-index: -1; }
      terminal content{ position: absolute; left: 1.5%; top: 60px; width: 98%; height: 92%; z-index: 1; overflow-x: hidden; overflow-y: auto; color: #ececec; font-size: var(--font-size); }
      terminal content line{ display: block; }
      terminal content path{ color: var(--color-scheme-1); }
      terminal content sp{ color: var(--color-scheme-2); letter-spacing: -6px; margin-right: 5px;}
      terminal content sp::before{ content: var(--separator);}
      terminal content cm{ color: var(--color-scheme-3); }
      terminal content code{ display: inline; margin: 0; white-space: unset;}
      terminal content bl{ margin-left: 0.5rem; color: var(--blink-color); display: inline-block; height: 1rem; width: 0.8rem; background: yellow; position: relative; top: 2px;  animation: blink 1s steps(3) infinite;}

      footer{ position: absolute; width: 100%; left: 0; bottom: 20px; color: white; text-align: center; font-size: 12px; }
      footer a{ text-decoration: none; color: #fdbc40; }
      @keyframes blink { 0% { opacity: 1} 50% { opacity: .75} 100% { opacity: 1} }
    </style>

    <script type="text/javascript">
        let commands_list = <?php print_r(json_encode($terminal->commandsList()));?>;
    </script>

    <script type="text/javascript">
        var path = '<?php echo $terminal->pwd(); ?>';
        var command = '';
        var command_history = [];
        var history_index = 0;
        var suggest = false;
        var blink_position = 0;
        var autocomplete_position = 0;
        var autocomplete_search_for = '';
        var autocomplete_temp_results = [];
        var autocomplete_current_result = '';

        $(document).keydown(function(e) {
            var keyCode = typeof e.which === "number" ? e.which : e.keyCode;

            /* Tab, Backspace and Delete key */
            if (keyCode === 8 || keyCode === 9 || keyCode === 46) {
                e.preventDefault();
                if (command !== ''){
                    if (keyCode === 8)
                        backSpace();
                    else if (keyCode === 46)
                        reverseBackSpace();
                    else if (keyCode === 9)
                        autoComplete();
                }
            }

            /* Ctrl + C */
            else if (e.ctrlKey && keyCode === 67){
                autocomplete_position = 0;
                endLine();
                newLine();
                reset();
            }

            /* Enter */
            else if (keyCode === 13){

                if (autocomplete_position !== 0){
                    autocomplete_position = 0;
                    command = autocomplete_current_result;
                }

                if (command.toLowerCase().split(' ')[0] in commands)
                    commands[command.toLowerCase().split(' ')[0]](command.split(' ').slice(1));
                else if(command.length !== 0)
                    $.ajax({
                        type: 'POST',
                        async: false,
                        data: {command: command, path: path},
                        cache: false,
                        success: function( response ){
                            response = $.parseJSON(response);
                            path = response.path;
                            $('terminal content').append('<line>'+response.result+'</line>');
                        }
                    });

                endLine();
                addToHistory(command);
                newLine();
                reset();
                $('terminal content').scrollTop($('terminal content').prop("scrollHeight"));
            }

            /* Home, End, Left and Right (change blink position) */
            else if ((keyCode === 35 || keyCode === 36 || keyCode === 37 || keyCode === 39) && command !== ''){
                e.preventDefault();
                $('line.current bl').remove();

                if (autocomplete_position !== 0){
                    autocomplete_position = 0;
                    command = autocomplete_current_result;
                }

                if (keyCode === 35)
                    blink_position = 0;

                if (keyCode === 36)
                    blink_position = command.length*-1;

                if (keyCode === 37 && command.length !== Math.abs(blink_position))
                    blink_position--;

                if (keyCode === 39 && blink_position !== 0)
                    blink_position++;

                printCommand();
                normalizeHtml();
            }

            /* Up and Down (suggest command from history)*/
            else if ((keyCode === 38 || keyCode === 40) && (command === '' || suggest)){
                e.preventDefault();
                if (keyCode === 38
                    && command_history.length
                    && command_history.length >= history_index*-1+1) {

                    history_index--;
                    command = command_history[command_history.length+history_index];
                    printCommand();
                    normalizeHtml();
                    suggest = true;
                }
                else if (keyCode === 40
                    && command_history.length
                    && command_history.length >= history_index*-1
                    && history_index !== 0) {

                    history_index++;
                    command = (history_index === 0) ? '' : command_history[command_history.length+history_index];
                    printCommand();
                    normalizeHtml();
                    suggest = (history_index === 0) ? false : true;
                }
            }

            /* type characters */
            else if (keyCode === 32
                || keyCode === 222
                || keyCode === 220
                || (
                    (keyCode >= 45 && keyCode <= 195)
                    && !(keyCode >= 112 && keyCode <= 123)
                    && keyCode != 46
                    && keyCode != 91
                    && keyCode != 93
                    && keyCode != 144
                    && keyCode != 145
                    && keyCode != 45
                )
            ){
                type(e.key);
                $('terminal content').scrollTop($('terminal content').prop("scrollHeight"));
            }
        });

        function reset() {
            command = '';
            history_index = 0;
            blink_position = 0;
            autocomplete_position = 0;
            autocomplete_current_result = '';
            suggest = false;
        }
        function endLine() {
            $('line.current bl').remove();
            $('line.current').removeClass('current');
        }
        function newLine() {
            $('terminal content').append('<line class="current"><path>'+path+'</path> <sp></sp> <t><bl></bl></t></line>');
        }
        function addToHistory(command) {
            if (command.length >= 2 &&  (command_history.length === 0 || command_history[command_history.length-1] !== command))
                command_history[command_history.length] = command;
        }
        function normalizeHtml() {
            let res = $('line.current t').html();
            let nres = res.split(' ').length == 1 ? '<cm>'+res+'</cm>' : '<cm>'+res.split(' ')[0]+'</cm> <code>'+res.split(' ').slice(1).join(' ').replace(/</g, '&lt;').replace(/>/g, '&gt;')+'</code>';

            $('line.current t').html(nres.replace('&lt;bl&gt;&lt;/bl&gt;', '<bl></bl>'));
        }
        function printCommand(cmd = '') {
            if (cmd === '')
                cmd = command;
            else
                blink_position = 0;

            let part1 = cmd.substr(0, cmd.length + blink_position);
            let part2 = cmd.substr(cmd.length + blink_position);

            $('line.current t').html(part1 + '<bl></bl>' + part2);
        }
        function type(t) {
            history_index = 0;
            suggest = false;

            if (autocomplete_position !== 0){
                autocomplete_position = 0;
                command = autocomplete_current_result;
            }
            if (command[command.length-1] === '/' && t === '/')
                return;

            let part1 = command.substr(0, command.length + blink_position);
            let part2 = command.substr(command.length + blink_position);
            command = part1+t+part2;

            printCommand();
            normalizeHtml();
        }
        function backSpace() {
            if (autocomplete_position !== 0){
                autocomplete_position = 0;
                command = autocomplete_current_result;
            }

            let part1 = command.substr(0, command.length + blink_position);
            let part2 = command.substr(command.length + blink_position);
            command = part1.substr(0, part1.length-1)+part2;

            printCommand();
            normalizeHtml();
        }
        function reverseBackSpace() {
            let part1 = command.substr(0, command.length + blink_position);
            let part2 = command.substr(command.length + blink_position);
            command = part1+part2.substr(1);

            if (blink_position !== 0)
                blink_position++;

            printCommand();
            normalizeHtml();
        }
        function autoComplete() {
            if(autocomplete_search_for !== command){
                autocomplete_search_for = command;
                autocomplete_temp_results = [];

                if (command.split(' ').length === 1) {
                    let cmdlist = commands_list.concat(Object.keys(commands));
                    autocomplete_temp_results = cmdlist
                        .filter(function (cm) {return (cm.length > command.length && cm.substr(0, command.length).toLowerCase() == command.toLowerCase()) ? true : false;})
                        .reverse().sort(function (a, b) {return b.length - a.length;});
                }

                else if (command.split(' ').length === 2){
                    let cmd = command.split(' ')[0];
                    let cmd_parameter = command.split(' ')[1];
                    var temp_cmd = '';

                    if (cmd === 'cd' || cmd === 'cp' || cmd === 'mv' || cmd === 'cat'){
                        switch (cmd) {
                            case "cd": temp_cmd = 'ls -d '+cmd_parameter+'*/'; break;
                            case "cp":case "mv": temp_cmd = 'ls -d '+cmd_parameter+'*/'; break;
                            case "cat": temp_cmd = 'ls -p | grep -v /'; break;
                            default: temp_cmd = '';
                        }

                        $.ajax({
                            type: 'POST',
                            async: false,
                            data: {command: temp_cmd, path: path},
                            cache: false,
                            success: function( response ){
                                response = $.parseJSON(response);
                                autocomplete_temp_results = response.result.split('<br>')
                                    .filter(function (cm) {return (cm.length !== 0) ? true : false;});
                            }
                        });
                    }
                }
            }

            if (autocomplete_temp_results.length && autocomplete_temp_results.length > Math.abs(autocomplete_position)){
                autocomplete_position--;
                autocomplete_current_result = ((command.split(' ').length === 2) ? command.split(' ')[0]+' ' : '')+autocomplete_temp_results[autocomplete_temp_results.length+autocomplete_position];
                printCommand(autocomplete_current_result);
                normalizeHtml();
            }
            else{
                autocomplete_position = 0;
                autocomplete_current_result = '';
                printCommand();
                normalizeHtml();
            }
        }


        /**********************************************************/
        /*                     Local Commands                     */
        /**********************************************************/

        var commands = {
            'clear' : clear,
            'history': history
        };

        function clear(){
            $('terminal content').html('');
        }

        function history(arg){
            var res = [];
            let start_from = arg.length ? Number.isInteger(Number(arg[0])) ? Number(arg[0]) : 0 : 0;

            if (start_from != 0 && start_from <= command_history.length)
                for (var i = command_history.length-start_from; i < command_history.length; i++) { res[res.length] = (i+1)+' &nbsp;'+command_history[i]; }
            else
                command_history.forEach(function(item, index) { res[res.length] = (index+1)+' &nbsp;'+item; });

            $('terminal content').append('<line>'+res.join('<br>')+'</line>');
      }

    </script>
  </head>
  <body>
    <terminal>
      <header>
        <title><?php echo '(' . ($terminal->whoami() ? $terminal->whoami() : '') . ($terminal->whoami() && $terminal->hostname() ? '@' . $terminal->hostname() : '') . ')'; ?></title>
      </header>
      <content>
        <line class="current"><path><?php echo $terminal->pwd(); ?></path> <sp></sp> <t><bl></bl></t></line>
      </content>
    </terminal>
    <footer></footer>
  </body>
</html>
<?php }?>

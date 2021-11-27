<?php
$includes = [
    "#include<stdio.h>"
];
$result = [];
$auto = 0;

function auto($reset=false)
{
    global $auto;
    if (!$reset)
        $auto+= 1;
    else
        $auto = 0;
    return $auto;
}

define("PHC_ECHO", auto());
define("PHC_STRING", auto());
define("PHC_INTEGER", auto());

$tokens = [
    ["[0-9]+", PHC_INTEGER],
    ["echo|print", PHC_ECHO],
    ["(\"|')([^\"']+)(\"|')", PHC_STRING]
];

function lex_file($name)
{
    $content = file_get_contents($name);
    $program = explode(";", $content);

    return $program;
}

function lex($statements)
{
    global $tokens;
    $tokenized_statements = [];
    foreach ($statements as $statement)
    {
        $tokenized = "[$statement";
        foreach ($tokens as [$expresion, $token])
        {
            $tokenized = preg_replace_callback("/$expresion/", function($matches) use($token) {
                if ($token == 2)
                    return "[{$token}, \"{$matches[2]}\"],";
                if ($token == 3)
                    return "[{$token}, {$matches[0]}],";
                return "[{$token}],";
            }, $tokenized);
        }
        array_push($tokenized_statements, json_decode(substr_replace($tokenized, "]", -1))); 
    }
    return $tokenized_statements;
}

function parse($statements)
{
    global $result;
    foreach ($statements as $statement)
    {
        foreach ($statement as $position => $token)
        {
            $token_ctx = array_slice($token, 1);
            $token = $token[0];
            switch ($token)
            {
                case PHC_ECHO:
                    if (!isset($statement[$position+1]))
                        break;
                    $next = $statement[$position+1];
                    if ($next[0] == PHC_STRING)
                        array_push($result, "printf(\"{$next[1]}\");");
                    if ($next[0] == PHC_INTEGER)
                        array_push($result, "printf(\"%i\", {$next[1]});");
                    break;
                case PHC_INTEGER:
                case PHC_STRING:
                    break;
            }
        }
    }
}

$program = lex(lex_file($argv[1]));
parse($program);
$output = join("\n", $includes);
$output .= "\n\nint main()\n{\n";

foreach ($result as $statement)
    $output .= "    " . $statement . "\n";

$output .= "}";

echo $output;

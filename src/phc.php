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

$program = [
    [
        [PHC_STRING, "foo"]
    ],
    [
        [PHC_ECHO]
    ],
    [
        [PHC_ECHO],
        [PHC_STRING, "bar"]
    ],
    [
        [PHC_ECHO],
        [PHC_INTEGER, 10]
    ]
];

function lex($statements)
{
    foreach ($statements as $statement)
    {
       // literally todo lol. Strings are bloat, TOKENS SUPERMACY 
    }
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

parse($program);
$output = join("\n", $includes);
$output .= "\n\nint main()\n{\n";

foreach ($result as $statement)
    $output .= "    " . $statement . "\n";

$output .= "}";

echo $output;

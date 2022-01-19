<?php

// Retorna a saída de um `var_export` com pré formatação
function var_export_format(&$data)
{
    $dump = var_export($data, true);
    $dump = preg_replace('#(?:\A|\n)([ ]*)array \(#i', '[', $dump); // Starts
    $dump = preg_replace('#\n([ ]*)\),#', "\n$1],", $dump); // Ends
    $dump = preg_replace('#=> \[\n\s+\],\n#', "=> [],\n", $dump); // Empties
    if (gettype($data) == 'object') { // Deal with object states
        $dump = str_replace('__set_state(array(', '__set_state([', $dump);
        $dump = preg_replace('#\)\)$#', "])", $dump);
    } else {
        $dump = preg_replace('#\)$#', "]", $dump);
    }
    return $dump;
}

// Imprime na tela os valores que foram passados nos parâmetros
function dumpl(...$array)
{
    // $array = func_get_args();
    echo "<style>*{background:#eee;}pre{font-weight:bolder;font-size:1.2em;}</style><pre>";
    foreach ($array as $key => $value) {
        echo var_export_format($value);
        echo "<hr>";
        unset($array[$key]);
    }
    unset($array);
    echo "</pre>";
}

// Imprime na tela os valores que foram passados nos parâmetros e encerra a execução do código php
function dumpd(...$array)
{
    dumpl(...$array);
    die();
}

// Retorna a conversão de um objeto em array
function object_to_array($object)
{
    $output = [];
    foreach ((array) $object as $key => $value) {
        $output[preg_replace('/\000(.*)\000/', '', $key)] = $value;
    }
    return $output;
}

// Retorna a conversão de um array de objetos em array
function parse_array_object_to_array($array)
{
    foreach ($array as $key => $value) {
        if (is_object($value)) {
            $value = object_to_array($value);
            $array[$key] = $value;
        }
        if (is_array($value)) {
            $value = parse_array_object_to_array($value);
            $array[$key] = $value;
        }
    }
    return $array;
}

// Retorna um texto de `CamelCase` para um todo em minúscilo separado por `Underline`
function decamelize(string $string)
{
    $string = preg_replace("/(?<=\\w)(?=[A-Z])/","_$1", $string);
    return strtolower($string);
}

// Retorna a conversão da string para número
function string_to_number($val)
{
    if (is_numeric($val)) {
        $int = (int) $val;
        $float = (float) $val;
        $val = ($int == $float) ? $int : $float;
    }
    return $val;
}

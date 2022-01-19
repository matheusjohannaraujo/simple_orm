<?php

/*
	GitHub: https://github.com/matheusjohannaraujo/makemvcss
	Country: Brasil
	State: Pernambuco
	Developer: Matheus Johann Araujo
*/

/**
 * 
 * **Function -> var_export_format**
 *
 * EN-US: Returns the output of a pre-formatted `var_export`.
 * 
 * PT-BR: Retorna a saída de um `var_export` pré-formatado.
 * 
 * @param mixed &$data [reference variable]
 * @return string
 */
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

/**
 * 
 * **Function -> dumpl**
 *
 * EN-US: Prints on the screen the values ​​that were passed in the parameters.
 * 
 * PT-BR: Imprime na tela os valores que foram passados ​​nos parâmetros.
 * 
 * @param mixed ...$params [optional]
 * @return null
 */
function dumpl(...$params)
{
    // $params = func_get_args();
    $style = "font-weight:bolder;font-size:1.2em;color:#ccc;background:#333;border-radius:3px;padding:15px;margin:0;display:inline-block;";
    if (!empty($params) > 0) {
        echo !defined('CLI') ? "\r\n<hr/>\r\n" : "";
    }
    foreach ($params as $key => $value) {
        echo !defined('CLI') ? "<pre style=\"${style}\">\r\n" : "";
        echo var_export_format($value);
        echo !defined('CLI') ? "\r\n</pre>\r\n<hr/>\r\n" : "";
        unset($params[$key]);
    }
    unset($params);
}

/**
 * 
 * **Function -> dumpd**
 *
 * EN-US: Print the values ​​that were passed in the parameters
 * on the screen and end the execution of the php code.
 * 
 * PT-BR: Imprime os valores que foram passados ​​nos parâmetros
 * na tela e finaliza a execução do código php.
 * 
 * @param mixed ...$params [optional]
 * @return null
 */
function dumpd(...$params)
{
    dumpl(...$params);
    die();
}

/**
 * 
 * **Function -> object_to_array**
 *
 * EN-US: Returns the conversion of an object to an array.
 * 
 * PT-BR: Retorna a conversão de um objeto em uma matriz.
 * 
 * @param object $object
 * @return array
 */
function object_to_array($object)
{
    $output = [];
    foreach ((array) $object as $key => $value) {
        $output[preg_replace('/\000(.*)\000/', '', $key)] = $value;
    }
    return $output;
}

/**
 * 
 * **Function -> parse_array_object_to_array**
 *
 * EN-US: Returns the conversion of an array of objects to an array.
 * 
 * PT-BR: Retorna a conversão de uma matriz de objetos em uma matriz.
 * 
 * @param array $array
 * @return array
 */
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

/**
 *
 * **Function -> decamelize**
 *
 * EN-US: Returns a text from `CamelCase` for a lowercase whole separated by `Underline`.
 *
 * PT-BR: Retorna um texto de `CamelCase` para um todo em minúsculas separado por `Underline`.
 *
 * @param string $text
 * @return string
 */
function decamelize(string $text)
{
    $text = preg_replace("/(?<=\\w)(?=[A-Z])/","_$1", $text);
    return strtolower($text);
}

/**
 *
 * **Function -> string_to_type**
 *
 * EN-US: Returns the conversion of the string to the type of the given value.
 *
 * PT-BR: Retorna a conversão da string para o tipo do valor fornecido.
 *
 * @param mixed $val
 * @return mixed
 */
function string_to_type($val)
{
    if (is_string($val)) {
        switch (strtolower($val)) {
            case "true":
                $val = true;
                break;
            case "false":
                $val = false;
                break;
            case "null":
                $val = null;
                break;
        }
    }
    if (is_numeric($val)) {
        $int = (int) $val;
        $float = (float) $val;
        $val = ($int == $float) ? $int : $float;
    }
    return $val;
}

/**
 *
 * **Function -> type_to_string**
 *
 * EN-US: Returns the conversion of the given value to string.
 *
 * PT-BR: Retorna a conversão do valor fornecido para string.
 *
 * @param mixed $val
 * @return mixed
 */
function type_to_string($val) :string
{
    if (is_bool($val)) {
        return $val ? "true" : "false";
    }
    if ($val === null) {
        //return "null";
        return "";
    }
    return $val;
}

/**
 * 
 * **Function -> is_type**
 *
 * EN-US: Returns true or false according to the type and value.
 * 
 * PT-BR: Retorna verdadeiro ou falso de acordo com o tipo e valor.
 * 
 * @param string $type
 * @param mixed $val
 * @return bool
 */
function is_type(string $type, $val)
{
    $result = false;
    if ($type == "string" && is_string($val)) {
        $result = true;
    }
    if (($type == "int" || $type == "float" || $type == "number") && is_numeric($val)) {
        $val = string_to_type($val);
    }
    if ($type == "int" && is_int($val)) {
        $result = true;
    }
    if ($type == "float" && is_numeric($val) && is_float((float) $val)) {
        $result = true;
    }
    if ($type == "number" && is_numeric($val)) {
        $result = true;
    }
    if ($type == "null" && is_null($val)) {
        $result = true;
    }
    if ($type == "bool" && is_string($val)) {
        $val = string_to_type($val);
        if ($val === 0) {
            $val = false;
        } else if ($val === 1) {
            $val = true;
        }
    }
    if ($type == "bool" && is_bool($val)) {
        $result = true;
    }
    if ($type == "object" && is_object($val)) {
        $result = true;
    }
    if ($type == "array" && is_array($val)) {
        $result = true;
    }
    if ($type == "callback" && is_callable($val)) {
        $result = true;
    }
    return $result;
}

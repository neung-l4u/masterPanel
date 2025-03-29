<?php
function to_sentence_case($text) {
    return ucfirst(mb_strtolower($text));
}

function to_lowercase($text) {
    return mb_strtolower($text);
}

function to_uppercase($text) {
    return mb_strtoupper($text);
}

function to_capitalized_case($text) {
    return mb_convert_case($text, MB_CASE_TITLE);
}

function to_alternating_case($text) {
    $output = '';
    $toggle = true;
    foreach (mb_str_split($text) as $char) {
        $output .= $toggle ? mb_strtoupper($char) : mb_strtolower($char);
        $toggle = !$toggle;
    }
    return $output;
}

function to_title_case($text) {
    return ucwords(mb_strtolower($text));
}

function to_inverse_case($text) {
    $output = '';
    foreach (mb_str_split($text) as $char) {
        if (mb_strtolower($char) === $char) {
            $output .= mb_strtoupper($char);
        } else {
            $output .= mb_strtolower($char);
        }
    }
    return $output;
}

$text = $_POST['text'] ?? '';
$case = $_POST['case'] ?? '';

switch ($case) {
    case 'sentence':
        $converted = to_sentence_case($text);
        break;
    case 'lower':
        $converted = to_lowercase($text);
        break;
    case 'upper':
        $converted = to_uppercase($text);
        break;
    case 'capitalized':
        $converted = to_capitalized_case($text);
        break;
    case 'alternating':
        $converted = to_alternating_case($text);
        break;
    case 'title':
        $converted = to_title_case($text);
        break;
    case 'inverse':
        $converted = to_inverse_case($text);
        break;
    case 'snake':
        $converted = to_snake_case($text);
        break;
    case 'kebab':
        $converted = to_kebab_case($text);
        break;
    case 'camel':
        $converted = to_camel_case($text);
        break;
    case 'pascal':
        $converted = to_pascal_case($text);
        break;
    default:
        $converted = $text;
        break;
}

echo $converted;

function to_snake_case($text) {
    return strtolower(preg_replace('/[\s\-]+/', '_', trim($text)));
}

function to_kebab_case($text) {
    return strtolower(preg_replace('/[\s_]+/', '-', trim($text)));
}

function to_camel_case($text) {
    $text = ucwords(str_replace(['-', '_'], ' ', strtolower($text)));
    $text = str_replace(' ', '', $text);
    return lcfirst($text);
}

function to_pascal_case($text) {
    return str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', strtolower($text))));
}
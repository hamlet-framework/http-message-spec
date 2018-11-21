<?php

$strings = [
    '0=',
    '&&&&a=example',
    'x=&y[]=2&y[xxx]=null&0=false',
    'x=&y[]=2&y[xxx]=null&0=false&[1]=23',
    'x=&y[][]=2&y[][1]=null&y[][][]=0&false=-1',
    '5=6'
];

function collapse_type($i) {
    if ($i === null) {
        return 'null';
    }
    if (is_int($i)) {
        return 'int';
    }
    if (is_float($i)) {
        return 'float';
    }
    if (is_bool($i)) {
        return 'bool';
    }
    if (is_string($i)) {
        return 'string';
    }
    assert(is_array($i));
    $k_types = [];
    $v_types = [];
    foreach ($i as $k => $v) {
        $k_types[] = collapse_type($k);
        $v_types[] = collapse_type($v);
    }
    sort($k_types);
    sort($v_types);
    return 'array<' . join('|', array_unique($k_types)) . ',' . join('|', array_unique($v_types)) . '>';

}

$params = null;
foreach ($strings as $string) {
    parse_str($string, $params);
    printf("%-50s %-50s\n", $string, collapse_type($params));
}

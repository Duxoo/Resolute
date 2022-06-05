<?php

return array(
    'params' => array(
        'mobile_on' => array(
            'name' => 'Включить редирект на мобильную версию',
            'type' => 'checkbox'
        ),
        'address' => array(
            'name' => _w('Your address'),
            'type' => 'input'
        ),
        'phone' => array(
            'name' => _w('Your additional phone number'),
            'type' => 'input'
        ),
        'title'         => array(
            'name'        => _w('Homepage <title>'),
            'type'        => 'radio_text',
            'description' => '',
            'items'       => array(
                array(
                    'name'        => wa()->accountName(),
                    'description' => _ws('Company name'),
                ),
                array(
                    'name' => _w('As specified'),
                ),
            ),
        ),
        'meta_keywords' => array(
            'name' => _w('Homepage META Keywords'),
            'type' => 'input'
        ),
        'meta_description' => array(
            'name' => _w('Homepage META Description'),
            'type' => 'textarea'
        ),
    ),
);
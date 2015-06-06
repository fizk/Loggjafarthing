<?php
/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 17/05/15
 * Time: 9:04 PM
 */

return [
    'invokables' => [
        'Althingi\Service\Assembly' => 'Althingi\Service\Assembly',
    ],

    'factories' => [
        'MessageStrategy' => 'Althingi\View\Strategy\MessageFactory',
        'HttpClient' => function ($sm) {
            return new \Zend\Http\Client();
        },
        //'Request' => function ($sm) {
        //    return new \Althingi\Lib\Http\PhpEnvironment\Request();
        //},
        'PDO' => function ($sm) {
            $config = $sm->get('config');
            return new PDO(
                $config['db']['dns'],
                $config['db']['user'],
                $config['db']['password'],
                [
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'",
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
                ]
            );
        },
    ],

    'initializers' => [
        'Althingi\Lib\DatabaseAwareInterface' => function ($instance, $sm) {
            if ($instance instanceof \Althingi\Lib\DatabaseAwareInterface) {
                $instance->setDriver($sm->get('PDO'));
            }
        }
    ],
];

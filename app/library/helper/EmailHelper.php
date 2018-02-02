<?php

namespace Helper;

class EmailHelper
{
    public static function send($to="", $subject="", $content="")
    {
        $config = [
            'driver'     => 'smtp',
            'host'       => 'smtp.gmail.com',
            'port'       => 465,
            'encryption' => 'ssl',
            'username'   => 'no-reply@bigdutyfree.com',
            'password'   => 'no-reply',
            'from'       => [
                'email' => 'tune@gmail.com',
                'name'  => 'Giant Catalog',
            ],
        ];
        $mailer  = new \Phalcon\Ext\Mailer\Manager($config);
        $message = $mailer->createMessage()
            ->to($to, 'You')
            ->subject("Giant Catalog - ".$subject)
            ->content($content);
        $message->send();
    }
}

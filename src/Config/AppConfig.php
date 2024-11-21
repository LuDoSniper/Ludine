<?php

namespace App\Config;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

class AppConfig
{
    public function __construct(
        #[Autowire(env: 'NO_REPLY_MAIL')]
        public string $noReplyMail,

        #[Autowire(env: 'VERIFY_MAIL_SUBJECT')]
        public string $verifyMailSubject,

        #[Autowire(env: 'FORGOTTEN_PASSWORD_MAIL_SUBJECT')]
        public string $forgottenPasswordMailSubject
    ){}
}
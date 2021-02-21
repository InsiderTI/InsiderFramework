<?php

namespace Modules\Insiderframework\Core\Loaders\ConfigLoader;

class Mail {
   /**
    * Define mail constants from coreData array
    *
    * @author Marcello Costa
    *
    * @package Modules\Insiderframework\Core\Loaders\ConfigLoader\Mail
    *
    * @param array $coreData Core data configuration loaded from files
    *
    * @return void
    */
    public static function load(array $coreData): void {
        if (!isset($coreData['MAILBOX'])) {
            \Modules\Insiderframework\Core\Error::primaryError(
                "The following information was not found in the configuration: 'MAILBOX'"
            );
        }

        /**
         * Email from default
         *
         * @package Modules\Insiderframework\Core\Loaders\ConfigLoader\Mail
         */
        define('MAILBOX', $coreData['MAILBOX']);

        if (!isset($coreData['MAILBOX_PASS'])) {
            \Modules\Insiderframework\Core\Error::primaryError(
                "The following information was not found in the configuration: 'MAILBOX_PASS'"
            );
        }

        /**
         * Default email password / admin
         *
         * @package Modules\Insiderframework\Core\Loaders\ConfigLoader\Mail
         */
        define('MAILBOX_PASS', $coreData['MAILBOX_PASS']);

        if (!isset($coreData['MAILBOX_SMTP'])) {
            \Modules\Insiderframework\Core\Error::primaryError(
                "The following information was not found in the configuration: 'MAILBOX_SMTP'"
            );
        }

        /**
         * Default email SMTP server
         *
         * @package Modules\Insiderframework\Core\Loaders\ConfigLoader\Mail
         */
        define('MAILBOX_SMTP', $coreData['MAILBOX_SMTP']);

        if (!isset($coreData['MAILBOX_SMTP_AUTH'])) {
            \Modules\Insiderframework\Core\Error::primaryError(
                "The following information was not found in the configuration: 'MAILBOX_SMTP_AUTH'"
            );
        }

        /**
         * Boolean value that defines whether SMTP has default email authentication or not
         *
         * @package Modules\Insiderframework\Core\Loaders\ConfigLoader\Mail
         */
        define('MAILBOX_SMTP_AUTH', $coreData['MAILBOX_SMTP_AUTH']);

        if (!isset($coreData['MAILBOX_SMTP_SECURE'])) {
            \Modules\Insiderframework\Core\Error::primaryError(
                "The following information was not found in the configuration: 'MAILBOX_SMTP_SECURE'"
            );
        }

        /**
         * Type of default email security (TLS or SSL)
         *
         * @package Modules\Insiderframework\Core\Loaders\ConfigLoader\Mail
         */
        define('MAILBOX_SMTP_SECURE', $coreData['MAILBOX_SMTP_SECURE']);

        if (!isset($coreData['MAILBOX_SMTP_PORT'])) {
            \Modules\Insiderframework\Core\Error::primaryError(
                "The following information was not found in the configuration: 'MAILBOX_SMTP_PORT'"
            );
        }

        /**
         * SMTP port of default
         *
         * @package Modules\Insiderframework\Core\Loaders\ConfigLoader\Mail
         */
        define('MAILBOX_SMTP_PORT', $coreData['MAILBOX_SMTP_PORT']);

        if (!isset($coreData['ERROR_MAIL_SENDING_POLICY'])) {
            \Modules\Insiderframework\Core\Error::primaryError(
                "The following information was not found in the configuration: 'ERROR_MAIL_SENDING_POLICY'"
            );
        }

        /**
         * Email sending policy for errors
         *
         * @package Modules\Insiderframework\Core\Loaders\ConfigLoader
         */
        define('ERROR_MAIL_SENDING_POLICY', $coreData['ERROR_MAIL_SENDING_POLICY']);
    }
}
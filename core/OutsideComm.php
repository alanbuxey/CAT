<?php

/*
 * ******************************************************************************
 * Copyright 2011-2017 DANTE Ltd. and GÉANT on behalf of the GN3, GN3+, GN4-1 
 * and GN4-2 consortia
 *
 * License: see the web/copyright.php file in the file structure
 * ******************************************************************************
 */

/**
 * This file contains a number of functions for talking to the outside world
 * @author Stefan Winter <stefan.winter@restena.lu>
 * @author Tomasz Wolniewicz <twoln@umk.pl>
 *
 * @package Developer
 */

namespace core;

class OutsideComm {

    public static function downloadFile($url) {
        $loggerInstance = new Logging();
        if (!preg_match("/:\/\//", $url)) {
            $loggerInstance->debug(3, "The specified string does not seem to be a URL!");
            return FALSE;
        }
        # we got a URL, download it
        $download = fopen($url, "rb");
        $data = stream_get_contents($download);
        if (!$data) {

            $loggerInstance->debug(2, "Failed to download the file from $url");
            return FALSE;
        }
        return $data;
    }

    public static function mailHandle() {
// use PHPMailer to send the mail
        $mail = new \PHPMailer\PHPMailer\PHPMailer();
        $mail->isSMTP();
        $mail->SMTPAuth = true;
        $mail->Port = 587;
        $mail->SMTPSecure = 'tls';
        $mail->Host = CONFIG['MAILSETTINGS']['host'];
        $mail->Username = CONFIG['MAILSETTINGS']['user'];
        $mail->Password = CONFIG['MAILSETTINGS']['pass'];
// formatting nitty-gritty
        $mail->WordWrap = 72;
        $mail->isHTML(FALSE);
        $mail->CharSet = 'UTF-8';
        $mail->From = CONFIG['APPEARANCE']['from-mail'];
// are we fancy? i.e. S/MIME signing?
        if (isset(CONFIG['CONSORTIUM']['certfilename'], CONFIG['CONSORTIUM']['keyfilename'], CONFIG['CONSORTIUM']['keypass'])) {
            $mail->sign(CONFIG['CONSORTIUM']['certfilename'], CONFIG['CONSORTIUM']['keyfilename'], CONFIG['CONSORTIUM']['keypass']);
        }
        return $mail;
    }

    const MAILDOMAIN_INVALID = -1000;
    const MAILDOMAIN_NO_MX = -1001;
    const MAILDOMAIN_NO_HOST = -1002;
    const MAILDOMAIN_NO_CONNECT = -1003;
    const MAILDOMAIN_NO_STARTTLS = 1;
    const MAILDOMAIN_STARTTLS = 2;

    public static function mailAddressValidSecure($address) {
        $loggerInstance = new Logging();
        if (!filter_var($address, FILTER_VALIDATE_EMAIL)) {
            $loggerInstance->debug(4, "OutsideComm::mailAddressValidSecure: invalid mail address.");
            return OutsideComm::MAILDOMAIN_INVALID;
        }
        $domain = substr($address, strpos($address, '@') + 1); // everything after the @ sign
        // does the domain have MX records?
        $mx = dns_get_record($domain, DNS_MX);
        if ($mx === FALSE) {
            $loggerInstance->debug(4, "OutsideComm::mailAddressValidSecure: no MX.");
            return OutsideComm::MAILDOMAIN_NO_MX;
        }
        $loggerInstance->debug(5, "Domain: $domain MX: " . print_r($mx, TRUE));
        // create a pool of A and AAAA records for all the MXes
        $ipAddrs = [];
        foreach ($mx as $onemx) {
            $v4list = dns_get_record($onemx['target'], DNS_A);
            $v6list = dns_get_record($onemx['target'], DNS_AAAA);
            foreach ($v4list as $oneipv4) {
                $ipAddrs[] = $oneipv4['ip'];
            }
            foreach ($v6list as $oneipv6) {
                $ipAddrs[] = "[" . $oneipv6['ipv6'] . "]";
            }
        }
        if (count($ipAddrs) == 0) {
            $loggerInstance->debug(4, "OutsideComm::mailAddressValidSecure: no mailserver hosts.");
            return OutsideComm::MAILDOMAIN_NO_HOST;
        }
        $loggerInstance->debug(5, "Domain: $domain Addrs: " . print_r($ipAddrs, TRUE));
        // connect to all hosts. If all can't connect, return MAILDOMAIN_NO_CONNECT. 
        // If at least one does not support STARTTLS or one of the hosts doesn't connect
        // , return MAILDOMAIN_NO_STARTTLS (one which we can't connect to we also
        // can't verify if it's doing STARTTLS, so better safe than sorry.
        $retval = OutsideComm::MAILDOMAIN_NO_CONNECT;
        $allWithStarttls = TRUE;
        foreach ($ipAddrs as $oneip) {
            $smtp = new \PHPMailer\PHPMailer\SMTP;
            if ($smtp->connect($oneip, 25)) {
                // host reached! so at least it's not a NO_CONNECT
                $loggerInstance->debug(4, "OutsideComm::mailAddressValidSecure: connected to $oneip.");
                $retval = OutsideComm::MAILDOMAIN_NO_STARTTLS;
                if ($smtp->hello('eduroam.org')) {
                    $e = $smtp->getServerExtList();
                    if (!is_array($e) || !array_key_exists('STARTTLS', $e)) {
                        $loggerInstance->debug(4, "OutsideComm::mailAddressValidSecure: no indication for STARTTLS.");
                        $allWithStarttls = FALSE;
                    }
                }
            } else {
                // no connect: then we can't claim all targets have STARTTLS
                $allWithStarttls = FALSE;
            }
        }
        // did the state $allWithStarttls survive? Then up the response to
        // appropriate level.
        if ($retval == OutsideComm::MAILDOMAIN_NO_STARTTLS && $allWithStarttls) {
            $retval = OutsideComm::MAILDOMAIN_STARTTLS;
        }
        return $retval;
    }

}
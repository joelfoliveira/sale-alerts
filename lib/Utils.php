<?php


namespace SaleAlerts;


class Utils
{
    public static function stringPriceToFloat($priceStr)
    {
        return floatval(str_replace(',', '.', str_replace('.', '', $priceStr)));
    }

    public static function sendEmail($emailHtml, $subject)
    {
        $mandrill = new \Mandrill(Config::$mandrillApiKey);

        curl_setopt($mandrill->ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($mandrill->ch, CURLOPT_SSL_VERIFYPEER, 0);

        $result = $mandrill->messages->send(array(
            'html' => $emailHtml,
            'subject' => $subject,
            'from_email' => Config::$emailSource,
            'from_name' => Config::$emailSourceName,
            'to' => array(
                array(
                    'email' => Config::$emailDestination,
                    'name' => Config::$emailDestinationName,
                    'type' => 'to'
                )
            ),
            'important' => false,
            'track_opens' => null,
            'track_clicks' => null,
            'auto_text' => null,
            'auto_html' => null
        ), false);

        return isset($result['status']) && $result['status'] == 'sent' ? true: false;
    }
}
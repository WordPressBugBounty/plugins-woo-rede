<?php

namespace Lkn\IntegrationRedeForWoocommerce\Includes;

abstract class LknIntegrationRedeForWoocommerceHelper
{
    final public static function getCartTotal()
    {
        global $woocommerce;
        if (empty($woocommerce)) {
            return 0;
        }
        if ($woocommerce->cart) {
            return (float) $woocommerce->cart->total;
        }
        return 0;
    }

    final public static function updateFixLoadScriptOption($id): void
    {
        $wpnonce = isset($_POST['_wpnonce']) ? sanitize_text_field(wp_unslash($_POST['_wpnonce'])) : '';
        $section = isset($_GET['section']) ? sanitize_text_field(wp_unslash($_GET['section'])) : '';

        if (! empty($wpnonce) && $section === $id) {
            $enabledFixLoadScript = isset($_POST["woocommerce_" . $id . "_enabled_fix_load_script"]) ? 'yes' : 'no';

            $optionsToUpdate = array(
                'maxipago_credit',
                'maxipago_debit',
                'rede_credit',
                'rede_debit',
            );

            foreach ($optionsToUpdate as $option) {
                $paymentOptions = get_option('woocommerce_' . $option . '_settings', array());
                $paymentOptions['enabled_fix_load_script'] = $enabledFixLoadScript;
                update_option('woocommerce_' . $option . '_settings', $paymentOptions);
            }
        }
    }

    final public static function getCardBrand($tid, $instace)
    {
        $auth = base64_encode($instace->pv . ':' . $instace->token);

        if ('production' === $instace->environment) {
            $apiUrl = 'https://api.userede.com.br/erede/v1/transactions';
        } else {
            $apiUrl = 'https://sandbox-erede.useredecloud.com.br/v1/transactions';
        }

        $response = wp_remote_get($apiUrl . '/' . $tid, array(
            'headers' => array(
                'Authorization' => 'Basic ' . $auth,
                'Content-Type' => 'application/json',
                'Transaction-Response' => 'brand-return-opened'
            ),
        ));

        $response_body = wp_remote_retrieve_body($response);
        $response_body = json_decode($response_body, true);

        return($response_body['authorization']['brand']['name']);
    }

    final public static function censorString($string, $censorLength) {
        $length = strlen($string);

        if ($censorLength >= $length) {
            // Se o número de caracteres a censurar for maior ou igual ao comprimento total, censura tudo
            return str_repeat('*', $length);
        }

        $startLength = floor(($length - $censorLength) / 2); // Dividir o restante igualmente entre início e fim
        $endLength = $length - $startLength - $censorLength; // O que sobra para o final

        $start = substr($string, 0, $startLength);
        $end = substr($string, -$endLength);

        $censored = str_repeat('*', $censorLength);
        return $start . $censored . $end;
    }
}

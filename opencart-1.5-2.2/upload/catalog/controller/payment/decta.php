<?php

require_once('./vendor/decta/lib/decta_api.php');
require_once('./vendor/decta/lib/decta_logger_opencart.php');

class ControllerPaymentDecta extends Controller
{
    public function __construct($arg)
    {
        parent::__construct($arg);

        $this->private_key = ($this->config->get('decta_private_key')) ?
            $this->config->get('decta_private_key') : $this->config->get('decta_privatekey');

        $this->public_key = ($this->config->get('decta_public_key')) ?
            $this->config->get('decta_public_key') : $this->config->get('decta_publickey');

        $this->ca_file_path = getcwd() . '/vendor/decta/root_ca.pem';
    }

    public function index()
    {
        $this->language->load('payment/decta');
        $this->load->model('checkout/order');

        $data['button_confirm'] = $this->language->get('button_confirm');
        $data['token'] = true;

        $data['action'] = $this->url->link('payment/decta/confirm_order', '', 'SSL');

        if ($this->oc_version() === '1') {
            $this->data = $data;

            if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/decta_v1.tpl')) {
                $this->template = $this->config->get('config_template') . '/template/payment/decta_v1.tpl';
                $this->render();
            } else {
                $this->template = 'default/template/payment/decta_v1.tpl';
            }
            $this->render();
        } elseif ($this->oc_version() === '2') {
            if (version_compare(VERSION, '2.2.0', '<')) {
                if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/decta_v2.tpl')) {
                    return $this->load->view($this->config->get('config_template') . '/template/payment/decta_v2.tpl', $data);
                } else {
                    return $this->load->view('default/template/payment/decta_v2.tpl', $data);
                }
            } else {
                return $this->load->view('payment/decta_v2', $data);
            }
        }
    }

    public function confirm_order()
    {
        $this->load->model('checkout/order');
        $this->language->load('payment/decta');

        $order_id = $this->session->data['order_id'];
        $order_info = $this->model_checkout_order->getOrder($order_id);

        $language = $this->_language($this->session->data['language']);
        $order_id = (string)$this->session->data['order_id'];

        $decta = new DectaApi(
            $this->private_key,
            $this->public_key,
            new DectaLoggerOpencart($this->log)
        );

        $params = array(
            'number' => $order_id,
            'referrer' => 'opencart module ' . DECTA_MODULE_VERSION,
            'language' => $language/*$this->_language('en')*/,
            'success_redirect' => $this->url->link('payment/decta/callback_success', '', 'SSL'),
            'failure_redirect' => $this->url->link('payment/decta/callback_failure&id='.$order_id, '', 'SSL'),
            'currency' => $order_info['currency_code']
        );

        $this->addUserData($decta, $order_info, $params);
        $total = $this->currency->format($order_info['total'], $this->session->data['currency'], '', false);

        $params['products'][] = array(
            'price' => round($total, 2),
            'title' => $this->language->get('payment_decta_invoice_for_payment') . $order_id,
            'quantity' => 1
        );

        $payment = $decta->create_payment($params);

        if ($payment) {
            $this->session->data['payment_id'] = $payment['id'];

            if ($this->oc_version() === '1') {
                $this->model_checkout_order->confirm(
                    $order_id,
                    $this->config->get('decta_pending_status_id'),
                    $this->language->get('decta_order_status_pending'),
                    true
                );
            } elseif ($this->oc_version() === '2') {
                $this->model_checkout_order->addOrderHistory(
                    $order_id,
                    $this->config->get('decta_pending_status_id'),
                    $this->language->get('decta_order_status_pending'),
                    true
                );
            }
            $decta->log_info('Got checkout url, redirecting');
            $this->oc_redirect($payment['full_page_checkout']);
        } else {
            $decta->log_error('Error getting checkout url, redirecting');
            $this->oc_redirect($this->url->link('checkout/checkout', '', 'SSL'));
        }
    }

    protected function addUserData($decta, $order_info, &$params)
    {
        $user_data = [
            'email' => $order_info['email'],
            'phone' => $order_info['telephone'],
            'first_name' => $order_info['payment_firstname'],
            'last_name' => $order_info['payment_lastname'],
            'send_to_email' => true
        ];

        $findUser = $decta->getUser($user_data['email'], $user_data['phone']);
        if (!$findUser) {
            if ($decta->createUser($user_data)) {
                $findUser = $decta->getUser($user_data['email'], $user_data['phone']);
            }
        }
        $user_data['original_client'] = $findUser['id'];
        $params['client'] = $user_data;
    }

    public function callback_failure()
    {
        $decta = new DectaApi(
            $this->private_key,
            $this->public_key,
            new DectaLoggerOpencart($this->log)
        );

        $decta->log_info('Failure callback');
        $this->oc_redirect($this->url->link('checkout/checkout', '', 'SSL'));
    }

    public function callback_success()
    {
        $this->language->load('payment/decta');
        $this->load->model('checkout/order');

        $decta = new DectaApi(
            $this->private_key,
            $this->public_key,
            new DectaLoggerOpencart($this->log)
        );

        $decta->log_info('Success callback');

        $order_id = $this->session->data['order_id'];
        $payment_id = $this->session->data['payment_id'];

        if ($decta->was_payment_successful($order_id, $payment_id)) {
            if ($this->oc_version() === '1') {
                $this->model_checkout_order->update(
                    $order_id,
                    $this->config->get('decta_completed_status_id'),
                    $this->language->get('decta_order_status_success'),
                    true
                );
            } elseif ($this->oc_version() === '2') {
                $this->model_checkout_order->addOrderHistory(
                    $order_id,
                    $this->config->get('decta_completed_status_id'),
                    $this->language->get('decta_order_status_success'),
                    true
                );
            }
        } else {
            if ($this->oc_version() === "1") {
                $this->model_checkout_order->update(
                    $order_id,
                    $this->config->get('decta_failed_status_id'),
                    $this->language->get('decta_order_status_verification_failed'),
                    true
                );
            } elseif ($this->oc_version() === "2") {
                $this->model_checkout_order->addOrderHistory(
                    $order_id,
                    $this->config->get('decta_failed_status_id'),
                    $this->language->get('decta_order_status_verification_failed'),
                    true
                );
            }
        }

        $decta->log_info('Success callback processed, redirecting');
        $this->oc_redirect($this->url->link('checkout/success', '', 'SSL'));
    }

    private function _language($lang_id)
    {
        $languages = array('en', 'ru', 'lv', 'lt');
        $lang_id = strtolower(substr($lang_id, 0, 2));
        if (in_array($lang_id, $languages)) {
            return $lang_id;
        } else {
            return 'en';
        }
    }

    private function oc_version()
    {
        return substr(VERSION, 0, 1);
    }

    private function oc_redirect($path)
    {
        if ($this->oc_version() === '1') {
            $this->redirect($path);
        } elseif ($this->oc_version() === '2') {
            $this->response->redirect($path);
        }
    }
}

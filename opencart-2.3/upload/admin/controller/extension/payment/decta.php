<?php
class ControllerExtensionPaymentDecta extends Controller {
    private $error = array();

    public function index() {
        $this->load->language('extension/payment/decta');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('setting/setting');

        // ------------------------------------------------------------
        
        if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validate()) {    
            $this->model_setting_setting->editSetting('decta', $this->request->post);
            $this->session->data['success'] = $this->language->get('text_success');
            
            $this->response->redirect($this->url->link('extension/payment/decta', 'token=' . $this->session->data['token'], 'SSL'));
        }

        $setting_edited = false;
        if (!$this->config->get('decta_public_key') && $this->config->get('decta_publickey')) {
            $this->model_setting_setting->editSettingValue('decta', 'decta_public_key', $this->config->get('decta_publickey'));
            $setting_edited = true;
        }

        if (!$this->config->get('decta_private_key') && $this->config->get('decta_privatekey')) {
            $this->model_setting_setting->editSettingValue('decta', 'decta_private_key', $this->config->get('decta_privatekey'));
            $setting_edited = true;
        }

        if ($setting_edited) {
            $this->response->redirect($this->url->link('extension/payment/decta', 'token=' . $this->session->data['token'], 'SSL'));
        }

        $data = array();
        $arr = array(
            "heading_title",
            "text_success",
            "text_pay",
            "text_card",
            "text_edit",
            "entry_public_key",
            "entry_private_key",
            "entry_order_status_completed_text",
            "entry_order_status_pending_text",
            "entry_order_status_failed_text",
            "entry_currency",
            "entry_backref",
            "entry_server_back",
            "entry_language",
            "entry_status",
            "entry_sort_order",
            "error_public_key",
            "error_private_key",
                  "entry_public_key_help",
            "decta_sort_order",
            "entry_private_key",
            "entry_private_key_help",
            "button_save",
            "button_cancel",
            "text_enabled",
            "text_disabled"
        );
        foreach($arr as $v) $data[$v] = $this->language->get($v);

        // ------------------------------------------------------------

        $arr = array(
            "warning",
            "public_key",
            "private_key",
            "type"
        );
        foreach($arr as $v) $data['error_' . $v] = (isset($this->error[$v])) ? $this->error[$v] : "";

        // ------------------------------------------------------------

        $data['breadcrumbs'] = array();
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home') ,
            'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL') ,
            'separator' => false
        );
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_payment') ,
            'href' => $this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=payment', 'SSL') ,
            'separator' => ' :: '
        );
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title') ,
            'href' => $this->url->link('extension/payment/decta', 'token=' . $this->session->data['token'], 'SSL') ,
            'separator' => ' :: '
        );
        $data['action'] = $this->url->link('extension/payment/decta', 'token=' . $this->session->data['token'], 'SSL');
        $data['cancel'] = $this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=payment', 'SSL');

        // ------------------------------------------------------------

        $this->load->model('localisation/order_status');
        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
        $arr = array(
            "decta_public_key",
            "decta_private_key",
            "decta_status",
            "decta_pending_status_id",
            "decta_completed_status_id",
            "decta_failed_status_id",
            "decta_sort_order"
        );
        foreach($arr as $v) {
            $data[$v] = (isset($this->request->post[$v])) ? $this->request->post[$v] : $this->config->get($v);
        }

        // ------------------------------------------------------------

        $data['token'] = $this->session->data['token'];

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        $this->response->setOutput($this->load->view('extension/payment/decta_v2.tpl', $data));

    }

    private function validate() {
        if (!$this->user->hasPermission('modify', 'extension/payment/decta')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (!$this->request->post['decta_public_key']) {
            $this->error['public_key'] = $this->language->get('error_public_key');
        }

        if (!$this->request->post['decta_private_key']) {
            $this->error['private_key'] = $this->language->get('error_private_key');
        }

        return !$this->error;
    }
}

?>
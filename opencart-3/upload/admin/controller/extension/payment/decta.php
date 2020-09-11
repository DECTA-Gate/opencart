<?php
class ControllerExtensionPaymentDecta extends Controller {
  private $error = array();

  public function index() {
    $this->load->language('extension/payment/decta');
    $this->document->setTitle($this->language->get('heading_title'));
    $this->load->model('setting/setting');

    // ------------------------------------------------------------

    if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
      $this->model_setting_setting->editSetting('payment_decta', $this->request->post);
      $this->session->data['success'] = $this->language->get('text_success');

      $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true));
    }

    $setting_edited = false;
    if (!$this->config->get('payment_decta_public_key') && $this->config->get('payment_decta_public_key')) {
      $this->model_setting_setting->editSettingValue('payment_decta', 'payment_decta_public_key', $this->config->get('payment_decta_public_key'));
      $setting_edited = true;
    }

    if (!$this->config->get('payment_decta_private_key') && $this->config->get('payment_decta_private_key')) {
      $this->model_setting_setting->editSettingValue('payment_decta', 'payment_decta_private_key', $this->config->get('payment_decta_private_key'));
      $setting_edited = true;
    }

    if ($setting_edited) {
      $this->response->redirect($this->url->link('extension/payment/decta', 'user_token=' . $this->session->data['user_token'], true));
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
      "payment_decta_sort_order",
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
      'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true) ,
      'separator' => false
    );
    $data['breadcrumbs'][] = array(
      'text' => $this->language->get('text_payment') ,
      'href' => $this->url->link('extension/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true) ,
      'separator' => ' :: '
    );
    $data['breadcrumbs'][] = array(
      'text' => $this->language->get('heading_title') ,
      'href' => $this->url->link('extension/payment/decta', 'user_token=' . $this->session->data['user_token'], true) ,
      'separator' => ' :: '
    );
    $data['action'] = $this->url->link('extension/payment/decta', 'user_token=' . $this->session->data['user_token'], true);
    $data['cancel'] = $this->url->link('extension/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true);

    // ------------------------------------------------------------

    $this->load->model('localisation/order_status');
    $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

    $arr = array(
      "payment_decta_public_key",
      "payment_decta_private_key",
      "payment_decta_status",
      "payment_decta_pending_status_id",
      "payment_decta_completed_status_id",
      "payment_decta_failed_status_id",
      "payment_decta_sort_order",
    );
    foreach($arr as $v) {
      $data[$v] = (isset($this->request->post[$v])) ? $this->request->post[$v] : $this->config->get($v);
    }

    // ------------------------------------------------------------

    $data['user_token'] = $this->session->data['user_token'];

    $data['header'] = $this->load->controller('common/header');
    $data['column_left'] = $this->load->controller('common/column_left');
    $data['footer'] = $this->load->controller('common/footer');

    $this->response->setOutput($this->load->view('extension/payment/decta', $data));
  }

  private function validate() {
    if (!$this->user->hasPermission('modify', 'extension/payment/decta')) {
      $this->error['warning'] = $this->language->get('error_permission');
    }

    if (!$this->request->post['payment_decta_public_key']) {
      $this->error['public_key'] = $this->language->get('error_public_key');
    }

    if (!$this->request->post['payment_decta_private_key']) {
      $this->error['private_key'] = $this->language->get('error_private_key');
    }

    return !$this->error;
  }
}

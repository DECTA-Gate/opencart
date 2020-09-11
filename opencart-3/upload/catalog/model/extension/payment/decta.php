<?php
class ModelExtensionPaymentDecta extends Model {
  public function getMethod($address, $total) {
    $this->load->language('extension/payment/decta');

    $method_data = array(
      'code'       => 'decta',
      'title'      => $this->language->get('text_title'),
      'sort_order' => $this->config->get('payment_decta_sort_order'),
      'terms'      => ''
    );

    return $method_data;
  }
}

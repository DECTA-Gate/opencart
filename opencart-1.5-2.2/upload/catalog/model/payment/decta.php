<?php

class ModelPaymentDecta extends Model {
  public function getMethod($address, $total) {
    $this->load->language('payment/decta');
     
    return array(
      'code'       => 'decta',
      'title'      => $this->language->get('text_title'),
      'sort_order' => $this->config->get('decta_sort_order'),
      'terms'      => ''
    );
  }
}

?>

<?php echo $header; ?>
<div id="content">
  <div class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
    <?php } ?>
  </div>
  <?php if ($error_warning) { ?>
    <div class="warning"><?php echo $error_warning; ?></div>
  <?php } ?>
  <?php if ($error_public_key) { ?>
  <div class="warning"><?php echo $error_public_key; ?></div>
  <?php } ?>
  <?php if ($error_private_key) { ?>
  <div class="warning"><?php echo $error_private_key; ?></div>
  <?php } ?>   
  <div class="box">
    <div class="heading">
      <h1><img src="view/image/payment/logo.png" style="height:25px; margin-top:-5px;" /> <?php echo $text_edit; ?></h1>
      <div class="buttons"><a onclick="$('#form').submit();" class="button"><?php echo $button_save; ?></a><a onclick="location = '<?php echo $cancel; ?>';" class="button"><?php echo $button_cancel; ?></a></div>
    </div>
    <div class="content">
      <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
        <table class="form">
          <tr>
            <td><?php echo $entry_status; ?></td>
            <td><select name="decta_status">
                <?php $st0 = $st1 = ""; 
                 if ( $decta_status == 0 ) $st0 = 'selected="selected"';
                  else $st1 = 'selected="selected"';
                ?>

                <option value="0" <?php echo $st0; ?>><?php echo $text_disabled; ?></option>
                <option value="1" <?php echo $st1; ?>> <?php echo $text_enabled; ?></option>

              </select></td>
          </tr>          
          <tr>
            <td><span class="required">*</span> <?php echo $entry_public_key; ?></td>
            <td><input type="text" name="decta_public_key" value="<?php echo $decta_public_key; ?>" />
          </tr>
          <tr>  
            <td><span class="required">*</span> <?php echo $entry_private_key; ?></td>
            <td><input type="text" name="decta_private_key" value="<?php echo $decta_private_key; ?>" />
          </tr>
          <tr>
            <td><?php echo $entry_order_status_pending_text; ?></td>
            <td><select name="decta_pending_status_id">
                <?php 
                foreach ($order_statuses as $order_status) { 

                $st = ($order_status['order_status_id'] == $decta_pending_status_id) ? ' selected="selected" ' : "";
                ?>
                <option value="<?php echo $order_status['order_status_id']; ?>" <?= $st ?> ><?php echo $order_status['name']; ?></option>
                <?php } ?>
              </select></td>
          </tr>

          <tr>
            <td><?php echo $entry_order_status_completed_text; ?></td>
            <td><select name="decta_completed_status_id">
                <?php 
                foreach ($order_statuses as $order_status) { 

                $st = ($order_status['order_status_id'] == $decta_completed_status_id) ? ' selected="selected" ' : "";
                ?>
                <option value="<?php echo $order_status['order_status_id']; ?>" <?= $st ?> ><?php echo $order_status['name']; ?></option>
                <?php } ?>
              </select></td>
          </tr>

          <tr>
            <td><?php echo $entry_order_status_failed_text; ?></td>
            <td><select name="decta_failed_status_id">
                <?php 
                foreach ($order_statuses as $order_status) { 

                $st = ($order_status['order_status_id'] == $decta_failed_status_id) ? ' selected="selected" ' : "";
                ?>
                <option value="<?php echo $order_status['order_status_id']; ?>" <?= $st ?> ><?php echo $order_status['name']; ?></option>
                <?php } ?>
              </select></td>
          </tr>          

          <tr>
            <td><?php echo $entry_sort_order; ?></td>
            <td><input type="text" name="decta_sort_order" value="<?php echo $decta_sort_order; ?>" size="1" /></td>
          </tr>

        </table>
      </form>
    </div>
  </div>
</div>
<?php echo $footer; ?>
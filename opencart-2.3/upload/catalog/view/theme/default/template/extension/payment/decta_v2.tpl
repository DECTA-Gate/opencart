<form action="<?php echo $action; ?>" method="get">
  <div class="buttons">
    <div class="pull-right">
      <input type="submit" id="payment" onclick="window.location='<?php echo $action; ?>';return false;" value="<?php echo $button_confirm; ?>" class="btn btn-primary button" />
    </div>
  </div>
</form>
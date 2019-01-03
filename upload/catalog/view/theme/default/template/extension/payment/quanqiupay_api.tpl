<form id="payment" class="form-horizontal">
  <fieldset>
    <legend><?php echo $text_credit_card; ?></legend>
    <div class="form-group required">
      <label class="col-sm-2 control-label" for="input-cc-owner"><?php echo $entry_cc_owner; ?></label>
      <div class="col-sm-10">
        <input type="text" name="cc_owner" value="" placeholder="<?php echo $entry_cc_owner; ?>" id="input-cc-owner" class="form-control" />
      </div>
    </div>
    <div class="form-group required">
      <label class="col-sm-2 control-label" for="input-cc-number"><?php echo $entry_cc_number; ?></label>
      <div class="col-sm-10">
        <input type="text" name="cc_number" value="" maxlength="16" placeholder="<?php echo $entry_cc_number; ?>" id="input-cc-number" class="form-control" />
      </div>
    </div>
    <div class="form-group required">
      <label class="col-sm-2 control-label" for="input-cc-expire-date"><?php echo $entry_cc_expire_date; ?></label>
      <div class="col-sm-3">
        <select name="cc_expire_date_month" id="input-cc-expire-month" class="form-control">
          <?php foreach ($months as $month) { ?>
          <option value="<?php echo $month['value']; ?>"><?php echo $month['text']; ?></option>
          <?php } ?>
        </select>
       </div>
       <div class="col-sm-3">
        <select name="cc_expire_date_year" id="input-cc-expire-year" class="form-control">
          <?php foreach ($year_expire as $year) { ?>
          <option value="<?php echo $year['value']; ?>"><?php echo $year['text']; ?></option>
          <?php } ?>
        </select>
      </div>
    </div>
    <div class="form-group required">
      <label class="col-sm-2 control-label" for="input-cc-cvv2"><?php echo $entry_cc_cvv2; ?></label>
      <div class="col-sm-10">
        <input type="text" name="cc_cvv2" value="" maxlength="3" placeholder="<?php echo $entry_cc_cvv2; ?>" id="input-cc-cvv2" class="form-control" />
      </div>
    </div>
  </fieldset>
</form>
<div class="buttons">
  <div class="pull-right">
    <input type="button" value="<?php echo $button_confirm; ?>" id="button-confirm" class="btn btn-primary" />
  </div>
</div>
<script type="text/javascript"><!--
$('#button-confirm').on('click', function() {
  var myDate = new Date(); 
  if(document.getElementById("input-cc-owner").value == '' || document.getElementById("input-cc-owner").value == null){alert('Credit Card Owner Required Or Error!');}
  else if(document.getElementById("input-cc-number").value == '' || document.getElementById("input-cc-number").value == null){alert('Credit Card Number Required Or Error!');}
  else if(document.getElementById("input-cc-expire-year").value == myDate.getFullYear() && document.getElementById("input-cc-expire-month").value <= myDate.getMonth()+1){alert('Credit Card Expire Error !');}
  else if(document.getElementById("input-cc-cvv2").value == '' || document.getElementById("input-cc-cvv2").value == null){alert('Credit Card Ccv2 Error!');}
  else{ 
    $.ajax({
      url: 'index.php?route=extension/payment/quanqiupay/send',
      type: 'post',
      data: $('#payment :input'),
      dataType: 'json',
      cache: false,
      beforeSend: function() {
        $('#button-confirm').button('loading');
      },
      complete: function() {
        $('#button-confirm').button('reset');
      },
  		success: function(json) {
  			if (json['error']) {
  				alert(json['error']);
  			}
  			else{
          if (json['success']) {
            location.href = json['success'];
          }
          else if(json['3d']){
            if(json['url']){
              location.href = json['url'];
            }
            else{
              document.write(json['content']);
              document.close();
            }
          }
        }
  		}
    });
  }
});
//--></script>
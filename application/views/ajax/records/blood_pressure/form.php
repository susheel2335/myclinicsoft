<?php echo form_open('records/save/-1/'.$type,array('id'=>'record_form', 'class'=>'form-horizontal', 'role'=>'form')); ?>
<fieldset>
            <input type="hidden" name="user_id" value="<?php echo $user_id;?>"/>
			<div class="form-body">
				<div class="form-group">
					<label class="col-md-3 control-label"><?php echo $this->lang->line('records_date');?></label>
					<div class="col-md-9">
						<input name="blood_pressure_date" id="blood_pressure_date" class="form-control input-medium datepicker" type="text" value="<?php echo date('Y-m-d');?>" data-dateformat="yy-mm-dd" aria-invalid="false"/>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label"><?php echo $this->lang->line('records_systolic');?> <!--(larger number)--></label>
					<div class="col-md-9">
						<div class="input-group">
							<?php echo form_input(array(
								'name'=>'systolic',
								'id'=>'systolic',
								'class'=>'form-control')
							);?>
							<span class="input-group-addon">
								mmHg
							</span>
						</div>
						
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label"><?php echo $this->lang->line('records_diastolic');?> <!--(smaller number)--></label>
					<div class="col-md-9">
						<div class="input-group">
							<?php echo form_input(array(
							'name'=>'diastolic',
							'id'=>'diastolic',
							'class'=>'form-control')
						);?>
							<span class="input-group-addon">
								mmHg
							</span>
						</div>
						
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label"><?php echo $this->lang->line('records_heart_rate');?></label>
					<div class="col-md-9">
						<?php echo form_input(array(
				'name'=>'heart_rate',
				'id'=>'heart_rate',
				'class'=>'form-control')
			);?>
					</div>
				</div>
			</div>

        <hr>
	<button type="submit" id="submit" class="btn btn-primary btn-sm">Submit</button>
</fieldset>
</form>				
<script type='text/javascript'>

$(document).ready(function()
{
	
	runAllForms();
	var validatefunction = function() {
	
		$('#record_form').validate({
			rules: 
			{
				blood_pressure_date: {
					  required: true
					},
				systolic: {
					  required: true,
					  maxlength: 5
					},
				diastolic: {
					  required: true,
					  maxlength: 5
					},
				heart_rate: {
					  required: true,
					  maxlength: 5
					}
			},
			highlight: function(element) {
				$(element).closest('.form-group').addClass('has-error');
			},
			unhighlight: function(element) {
				$(element).closest('.form-group').removeClass('has-error');
			},
			errorElement: 'span',
			errorClass: 'help-block',
			errorPlacement: function(error, element) {
				if(element.parent('.input-group').length) {
					error.insertAfter(element.parent());
				}else{
					error.insertAfter(element);
				}
			},
			submitHandler:function(form)
			{
				$(form).ajaxSubmit({
					beforeSend: function () {
						$('#submit').html('Please wait...');
						$('#submit').attr("disabled", "disabled");
					},
					success:function(response)
					{
						if(response.success)
						{
							$('.close').trigger('click');
							$.smallBox({
								title : "Success",
								content : response.message,
								color : "#739E73",
								iconSmall : "fa fa-check",
								timeout : 3000
							});
							checkURL();
						}
						else
						{
							$.smallBox({
								title : "Error",
								content : response.message,
								color : "#C46A69",
								iconSmall : "fa fa-warning shake animated",
								timeout : 3000
							});
						}  
						$('#submit').html('Submit');
						$('#submit').removeAttr("disabled");
					},
					dataType:'json'
				});
		
			}
			
		});
	}

	loadScript(BASE_URL+"js/plugin/jquery-validate/jquery.validate.min.js", function(){
		loadScript(BASE_URL+"js/plugin/jquery-form/jquery-form.min.js", validatefunction);
	});
});
</script>

<?php
defined('C5_EXECUTE') or die('Access Denied');

$form = Loader::helper('form'); 

if (is_object($configuration)) {
	$accessKey = $configuration->getAccessKey();
	$secretKey = $configuration->getSecretKey();
	$bucketName = $configuration->getBucketName();
	$endpoint = $configuration->getEndpoint();
	$endpointPathStyle = $configuration->getEndpointPathStyle();
	$region = $configuration->getRegion();
	$subfolder = $configuration->getSubfolder();
	$enablePublic = $configuration->getEnablePublic();
	$publicURLOverride = $configuration->getPublicURLOverride();
	$enableRewrite = $configuration->getEnableRewrite();
	$rewritePath = $configuration->getRewritePath();

	$regions = \Concrete\Package\AmazonS3Filemanager\Controller::getRegions();
}

?>
<fieldset>
	<div class="form-group">
		<label for="accessKey"><?php echo t('Access Key')?></label>
		<div class="input-group">
			<?php echo $form->text('fslType[accessKey]', $accessKey, array('placeholder' => t('Access Key')))?>
			<span class="input-group-addon"><i class="fa fa-asterisk"></i></span>
		</div>
	</div>
	<div class="form-group">
		<label for="secretKey"><?php echo t('Secret Key')?></label>
		<div class="input-group">
			<?php echo $form->text('fslType[secretKey]', $secretKey, array('placeholder' => t('Secret Key')))?>
			<span class="input-group-addon"><i class="fa fa-asterisk"></i></span>
		</div>
	</div>
	<div class="form-group">
		<label for="bucketName"><?php echo t('Bucket Name')?></label>
		<div class="input-group">
			<?php echo $form->text('fslType[bucketName]', $bucketName, array('placeholder' => t('Bucket Name')))?>
			<span class="input-group-addon"><i class="fa fa-asterisk"></i></span>
		</div>
	</div>
</fieldset>
<fieldset>
	<legend><?php echo t('Optional') ?></legend>
	<div class="form-group">
		<label for="endpoint"><?php echo t('S3 Endpoint')?></label>
		<?php echo $form->text('fslType[endpoint]', $endpoint, array('placeholder' => t('http(s)://s3.yourdomain.com')))?>
	</div>

	<div class="form-group">
		<label>
			<input id="endpointPathStyle" type="checkbox" name="fslType[endpointPathStyle]" value="true" <?php echo $endpointPathStyle ? 'checked' : ''?>>
			<?php echo t('Use Path Style'); ?>
		</label>
	</div>

	<div class="form-group">
		<label for="region"><?php echo t('Amazon S3 Region')?></label>
		<?php echo $form->select('fslType[region]', $regions, $region); ?>
	</div>

	<div class="form-group">
		<label for="subfolder"><?php echo t('Store files in subfolder')?></label>
		<?php echo $form->text('fslType[subfolder]', $subfolder, array('placeholder' => t('your_folder_name')))?>
	</div>

	<div class="form-group">
		<label>
			<input id="enablePublic" type="checkbox" name="fslType[enablePublic]" value="true" <?php echo $enablePublic ? 'checked' : ''?>>
			<?php echo t('Use S3 Website Mode'); ?><br />
		</label>
	</div> 
	<div class="form-group" id="divPublicURLOverride" style="display:none">
		<label for="publicURLOverride"><?php echo t('Override Amazon S3 website URL')?></label>
		<?php echo $form->text('fslType[publicURLOverride]', $publicURLOverride, array('placeholder' => t('http://<bucket>.s3-website.<region>.amazonaws.com')))?>
	</div>

	<div class="form-group" id="divEnableRewrite" style="display:none">
		<label>
			<input id="enableRewrite" type="checkbox" name="fslType[enableRewrite]" value="true" <?php echo $enableRewrite ? 'checked' : ''?>>
			<?php echo t('Rewrite the S3 Website URL'); ?>
		</label>
	</div> 
	<div class="form-group" id="divRewritePath" style="display:none">
		<label for="rewritePath"><?php echo t('Path to be displayed on your website')?></label>
		<div class="input-group">
			<?php echo $form->text('fslType[rewritePath]', $rewritePath, array('placeholder' => t('files/s3/')))?>
			<span class="input-group-addon"><i class="fa fa-asterisk"></i></span>
		</div>
	</div>
	<div class="form-group" id="divUseRewriteAsPrimaryURL" style="display:none">
		<label>
			<input id="enableRewrite" type="checkbox" name="fslType[useRewriteAsPrimaryURL]" value="true" <?php echo $useRewriteAsPrimaryURL ? 'checked' : ''?>>
			<?php echo t('Use rewrite path as primary URL to files'); ?>
		</label>
	</div>

</fieldset>

<script type="text/javascript">

	var _publicPath = function() {
		if($('#enablePublic').is(':checked')) {
			$('#divEnableRewrite').show();
			$('#divPublicURLOverride').show();

			if($('#enableRewrite').is(':checked')) {
				$('#divRewritePath').show();
				$('#divUseRewriteAsPrimaryURL').show();
			} else {
				$('#divRewritePath').hide();
				$('#divUseRewriteAsPrimaryURL').hide();
			}
		} else {
			$('#enableRewrite').prop("checked", false);
			$('#divEnableRewrite').hide();
			$('#divRewritePath').hide();
			$('#divPublicURLOverride').hide();
			$('#divUseRewriteAsPrimaryURL').hide();
		}
	}

	$('#enablePublic').on('change',function(){
		_publicPath();
	});

	$('#enableRewrite').on('change',function(){
		_publicPath();
	});

	_publicPath();

</script>
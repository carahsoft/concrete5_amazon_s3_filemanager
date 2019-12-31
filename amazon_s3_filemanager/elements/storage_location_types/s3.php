<?php
defined('C5_EXECUTE') or die('Access Denied');
?>
<?php $form = Loader::helper('form'); 

if (is_object($configuration)) {
	$accesskey = $configuration->getAccesskey();
	$secretkey = $configuration->getSecretkey();
	$bucketname = $configuration->getBucketname();
	$subfolder = $configuration->getSubfolder();
	$publicPath = $configuration->getPublicPath();
	$enablePublicPath = $configuration->getEnablePublicPath();
	$region = $configuration->getRegion();

	$regions = \Concrete\Package\AmazonS3Filemanager\Controller::getRegions();
}

?>
<fieldset>
	<div class="form-group">
		<label for="accesskey"><?php echo t('Accesskey')?></label>
		<div class="input-group">
			<?php echo $form->text('fslType[accesskey]', $accesskey, array('placeholder' => t('Accesskey')))?>
			<span class="input-group-addon"><i class="fa fa-asterisk"></i></span>
		</div>
	</div>
	<div class="form-group">
		<label for="secretkey"><?php echo t('Secretkey')?></label>
		<div class="input-group">
			<?php echo $form->text('fslType[secretkey]', $secretkey, array('placeholder' => t('Secretkey')))?>
			<span class="input-group-addon"><i class="fa fa-asterisk"></i></span>
		</div>
	</div>
	<div class="form-group">
		<label for="bucketname"><?php echo t('Bucketname')?></label>
		<div class="input-group">
			<?php echo $form->text('fslType[bucketname]', $bucketname, array('placeholder' => t('Bucketname')))?>
			<span class="input-group-addon"><i class="fa fa-asterisk"></i></span>
		</div>
	</div>
</fieldset>
<fieldset>
	<legend><?php echo t('Optional Information') ?></legend>
	<div class="form-group">
		<label for="subfolder"><?php echo t('Subfolder in your bucket - folder is created if it does not exist!')?></label>
		<?php echo $form->text('fslType[subfolder]', $subfolder, array('placeholder' => t('Subfolder in Ihrem Bucket')))?>
	</div>


	<div class="form-group">
		<label for="region"><?php echo t('Choose an Amazon S3 Region')?></label>
		<?php echo $form->select('fslType[region]', $regions, $region); ?>
	</div>


	<div class="form-group">
		<label>
			<input id="enablePublicPath" type="checkbox" name="fslType[enablePublicPath]" value="true" <?php echo $enablePublicPath ? 'checked' : ''?>>
			<?php echo t('Enable Rewrite'); ?>
		</label>
	</div> 
	
	<div class="form-group" id="publicPath" style="display:none">
		<label for="publicPath"><?php echo t('Path to be displayed on your website')?></label>
		<div class="input-group">
			<?php echo $form->text('fslType[publicPath]', $publicPath, array('placeholder' => t('Path to be displayed on your site as e.g. /files/s3/')))?>
			<span class="input-group-addon"><i class="fa fa-asterisk"></i></span>
		</div>
	</div>

</fieldset>


<script type="text/javascript">


	var _publicPath = function(){
		if($('#enablePublicPath').is(':checked'))
			$('#publicPath').show();
		else
			$('#publicPath').hide();
	}

	$('#enablePublicPath').on('change',function(){
		_publicPath();
	});

	_publicPath();

</script>
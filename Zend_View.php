<?php
	$tabAccordionHelper = $this->getHelper('TabsAccordion');
	$this->itemsStart = $this->isMobile == true ? $tabAccordionHelper->startAccordion('business-tabs') : $tabAccordionHelper->startTab('business-tabs');
	$this->itemsEnd = $this->isMobile == true ? $tabAccordionHelper->endAccordion() : $tabAccordionHelper->endTab();

	echo $this->itemsStart;
	echo $this->render('business-page/tabs/ratings-reputation.phtml');
	echo $this->render('business-page/tabs/profile.phtml');
	echo $this->render('business-page/tabs/media.phtml');
	echo $this->render('business-page/tabs/rate-business.phtml');
	echo $this->itemsEnd;
?>

<script>
<?php
	if ($this->showMap)
	{
		echo 'var mapLat = '.$this->mapCoordinates->Lat.';';
		echo 'var mapLong = '.$this->mapCoordinates->Lang.';';
		echo 'var companyName = "'.$this->escape($this->businessObject->CompanyName).'";';
		echo 'var mapZoom = '.Zend_Registry::get('config')->google->maps->idealZoom.';';
		echo 'var useMarker = '.($this->useMapMarker == true ? 1 : 0).';';
		echo 'var useMap = 1;';
	}

	echo 'var businessId = '.$this->businessObject->ID.';';

	if (isset($this->mostRecentRating))
	{
		echo 'var mostRecentRating = true';
	}

	if (isset($this->ratingJustAuthenticated))
	{
		echo 'var ratingJustAuthenticated = true';
	}
?>
</script>
<!-- Facebook Pixel code - Page view events Code Start here -->
<script>
	fbq('track', 'ViewContent');
</script>
<!-- Facebook Pixel code - Page view events Code End here -->
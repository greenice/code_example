<?php

class BusinessesController extends Nocowboys_Controller_Action_V3_Default
{

    const COUNT_REVIEWS_WITH_MICRODATA = 80;

    /**
     * Manages showing a business page
     * @return boolean
     */
    public function indexAction()
    {
        $config = $this->getConfig();

        $businessUri = $this->_request->getParam('businessUri');

        // Load the business if it exists
        $businessTable = new Model_DbTable_Business();
        $business = $businessTable->findBusinessByUri($businessUri);

        $allowRating = $this->_request->getParam('allow-rating');

        // If there's no business, then throw a 404
        if (is_null($business)) {
            $this->_throw404();
        }

        if ((!$business->isOnline()) and ( is_null($allowRating))) {
            // Throw a 410 - page gone. The business exists but has been
            //  removed from the system. We can throw a 404, but with a 410
            //  we can choose not to report it as an error
            throw new Zend_Controller_Action_Exception('This business has been removed from NoCowboys', 410);
        }

        $business->profilePageViewed();
        $this->_normaliseUrl($business, true);

        $businessRegistered = $business->isRegistered();
        $this->view->registered = $businessRegistered;

        // Load the images
        $images = $business->getImages();
        $this->view->images = $images;
        $this->view->countImages = count($images);
        $this->view->associations = $business->getAssociations();

        $publicMapCoordinates = $business->getPublicMapCoordinates();
        $mapCoordinates = $business->getMapCoordinates(true);
        $businessHiddenMap = $business->mapHidden();

        $this->view->businessObject = $business;
        $this->view->businessLocation = $business->getLocationArea();
        $this->view->mapCoordinates = $publicMapCoordinates;
        $this->view->showMap = (($businessRegistered) and (!is_null($publicMapCoordinates)));
        $this->view->useMapMarker = (($businessHiddenMap == false) and (!is_null($mapCoordinates)));

        // Load the ratings
        $this->view->ratings = $business->getAuthenticatedRatings(false, false);
        $this->view->nonCommentRatings = $business->getAuthenticatedRatings(false);
        $this->view->unauthenticatedRatings = $business->getUnauthenticatedRatings(false, mktime(0, 0, 0, 8, 1, 2011));
        $this->view->video = $business->getVideoKey();
        $this->view->mapKey = $config->google->maps->key;

        count($this->view->ratings) > self::COUNT_REVIEWS_WITH_MICRODATA ? $showMicrodata = false : $showMicrodata = true;
        $this->view->showMicrodata = $showMicrodata;

        $video = $business->getVideoKey();
        $this->view->video = $video;
        $this->view->countVideo = !is_null($video) ? 1 : 0;

        // Media
        $this->view->countMedia = $this->view->countImages + $this->view->countVideo;

        // Tell Google not to index this page if we don't have enough
        //  ratings
        $totalRatings = count($this->view->ratings) + count($this->view->nonCommentRatings) + count($this->view->unauthenticatedRatings);
        if (($totalRatings < $config->seo->business_minimum_rating_count) and!$businessRegistered) {
            $this->_setNoIndex();
        } elseif (!$businessRegistered and $business->isHideFromSearchEngines()) {
            $this->_setNoIndex();
        }

        $pageTitle = $business->CompanyName;
        $this->view->title = $pageTitle;
        $this->view->headerTitle = $business->getTitle();

        if (!is_null($this->view->businessLocation)) {
            $areaUrl = $this->view->businessLocation->URLName;
        } else {
            $areaUrl = 'new-zealand';
        }

        $breadcrumbArray = $business->getBreadcrumbArray();
        $this->view->breadcrumb = $breadcrumbArray;

        // Set up the meta tags
        $metaDescription = $business->getMetaDescription();
        $url = $business->getURI();

        // Show the meta tags for the page
        $this->view->headMeta()->setName('description', $metaDescription);
        $this->view->headMeta()->setName('publisher', 'https://plus.google.com/+nocowboys');

        // Opengraph tags
        $this->view->HeadMetaProperty()->appendProperty('og:title', $pageTitle);
        $this->view->HeadMetaProperty()->appendProperty('og:type', 'website');
        $this->view->HeadMetaProperty()->appendProperty('og:url', $config->domain . $url);
        $this->view->HeadMetaProperty()->appendProperty('og:description', $metaDescription);
        $this->view->HeadMetaProperty()->appendProperty('fb:admins', $config->facebook->admins);
        $this->view->HeadMetaProperty()->appendProperty('fb:app_id', $config->facebook->appId);

        $logo = $business->getLogo();

        if ($logo !== false) {
            $logoUrl = $this->view->escape($config->domain . '/images/business-logos/300x300/100/' . $logo);
            $logoMicrodata = $logoUrl;
        } else {
            $logoUrl = $config->domain . '/images/v3/logo-social.png';
            $logoMicrodata = $config->domain . '/images/v3/no-image-available.png';
        }

        $this->view->logoMicrodataUrl = $logoMicrodata;

        $this->view->HeadMetaProperty()->appendProperty('og:image', $logoUrl);

        // Twitter cards
        $this->view->headMeta()->setName('twitter:card', 'summary');
        $this->view->headMeta()->setName('twitter:title', $pageTitle);
        $this->view->headMeta()->setName('twitter:url', $url);
        $this->view->headMeta()->setName('twitter:description', $metaDescription);
        $this->view->headMeta()->setName('twitter:image', $logoUrl);

        // URL hardcoded because the authoritive source is the live website
        $this->view->headMeta()->setName('canonical', $config->domain . $url);

        // Switch off auto-detect for phone links
        $this->view->headMeta()->setName('format-detection', 'telephone=no');

        // Get competitors if the business is not registered
        if (!$businessRegistered) {
            $this->view->competitors = $business->getCompetitors();
        }

        // Check if we're restricting the robots
        if ((isset($this->metaRobots)) and ( trim($this->metaRobots))) {
            $this->view->headMeta()->setName('robots', $this->metaRobots);
        }

        $form = new Form_Business_Rate($business);

        if (($this->_request->isPost()) and ( $this->checkFormAction(Form_Business_Rate::FORM_ACTION))) {
            $formData = $this->_request->getPost();

            if ($form->isValid($formData)) {
                $formData = $form->getValues();
                $this->_rateBusiness($business, $formData, $allowRating);
            } else {
                $this->showError('Looks like there was a problem with your rating. Look below for the issues, and try again.');
                $form->populate($formData);
            }
        } else {
            if (!$business->isVerified()) {
                $this->showWarning('This business has not yet been checked and 
						verified for name and contact details. You may not be able to find it 
						in any of the search results until we validate and confirm it.');
            }

            // If there's a recent rating, set it up
            $mostRecentRatingId = $this->_request->getParam('most-recent-rating');
            $ratingJustAuthenticated = $this->_request->getParam('rating-authenticated');

            if (!is_null($mostRecentRatingId)) {
                $ratingTable = new Model_DbTable_Rating();
                $this->view->mostRecentRating = $ratingTable->fetchRowByValue('ID', $mostRecentRatingId);
            } else
            if (!is_null($ratingJustAuthenticated)) {
                $ratingTable = new Model_DbTable_Rating();
                $this->view->ratingJustAuthenticated = $ratingTable->fetchRowByValue('ID', $ratingJustAuthenticated);
            }
        }

        $this->view->form = $form;

        $this->_helper->BusinessSendEmail($business);
        $this->_helper->BusinessSendTextMessage($business);
        $this->_helper->layout->setLayout('v3/business-profile');

        // Set up the headers
        $this->setHeaderLastModified(strtotime($business->lastUpdated));
        $this->_enableGoogleMaps();
        $this->_enableBusinessPageJs();

        $this->view->canonical = $config->domain . $url;

        if (!$business->isTrading()) {
            $contactLink = $this->view->url([], 'contact-send-message');
            $this->showWarning($business->CompanyName . ' have notified NoCowboys they are no longer trading. If you do suspect they continue to trade (naughty, naughty!), <a href="' . $contactLink . '">let us know</a>!');
        }

        // Add to analytics
        $analytics = Model_DbTable_Analytic::createRowStatic();
        $analytics->uri = $url;
        $analytics->businessId = $business->ID;
        $analytics->event = Model_DbTable_Analytic::PROFILE_VIEW;
        $analytics->save();
    }

    /**
     * Figures out if the URL is an older one for the business and redirects if so
     */
    private function _normaliseUrl($businessObject, $isBusinessPage = false)
    {
        if ($isBusinessPage) {
            $businessUri = $this->_request->getParam('businessUri');

            if (strcasecmp(trim($businessUri), trim($businessObject->URLName)) != 0) {
                $params = array('business' => $businessObject->URLName);
                $this->_helper->redirector->setCode(301);
                $this->_helper->redirector->gotoUrl($businessObject->getURI());
            }
        } else {
            $businessUri = $this->_request->getParam('business');

            if (strcasecmp(trim($businessUri), trim($businessObject->URLName)) != 0) {
                $params = array('business' => $businessObject->URLName);
                $this->_helper->redirector->setCode(301);
                $this->_helper->redirector->gotoSimple($this->getRequest()->getActionName(), $this->getRequest()->getControllerName(), 'default', $params);
            }
        }
    }

    /**
     * Handles any weird legacy business links that may pop up from time to time
     * 
     */
    public function legacyUrlAction()
    {
        $slug = $this->getRequest()->getParam('slug');
        $redirectType = $this->getRequest()->getParam('type');

        // e.g. viewtradesman/Mechanics/CarTune-Service-Centre-61941
        if ($redirectType == 1) {
            // Split the string to get the business ID and redirect
            $uriArray = explode('-', $slug);

            // The last item is the ID
            $businessId = $uriArray[count($uriArray) - 1];
        } else
        // e.g.	pages/viewtradesman.php?tradesman=65804
        if ($redirectType == 2) {
            // The slug is the business ID. Load and permanently redirect
            //  to that page
            $businessId = $slug;
        }

        $businessTable = new Model_DbTable_Business();
        $businessToLoad = $businessTable->fetchRowByValue('ID', $businessId);

        if (is_null($businessToLoad)) {
            // Send them to this phantom page so they get a 404
            header('location: /businesses/' . $slug);
        } else {
            header('location: ' . $businessToLoad->getUri());
        }

        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
    }

    /**
     * Confirms the given email ID is not spam, and sends it to the business
     *  it was intended for
     * 
     */
    public function confirmSpamEmailAction()
    {
        $correspondenceId = $this->getRequest()->getParam('correspondence');

        if (is_null($correspondenceId)) {
            throw new Exception('No email ID given');
        }

        $correspondence = Model_DbTable_BusinessCorrespondence::fetchRowByValueStatic('ID', $correspondenceId);

        if (is_null($correspondence)) {
            throw new Exception('No email available with that ID');
        }

        $this->view->correspondence = $correspondence;
        $this->view->isSpam = $correspondence->isSpam; // Set this explicitly - the correspondence object will change this shortly

        if ($correspondence->isSpam == 1) {
            $this->view->title = 'Message sent successfully';
            $this->view->breadcrumb = 'Message sent successfully';

            // Mark the correspondence as not spam. This will send it again
            //  also
            $correspondence->markNotSpam();
        } else {
            $this->view->title = 'Email has already been sent';
            $this->view->breadcrumb = 'Email has already been sent';
        }
    }

    /**
     * This is the landing page for businesses
     * 
     */
    public function salesAction()
    {
        $config = Zend_Registry::get('config');

        $defaultProductId = $config->website->defaultProductId;
        $defaultMonthlyProductId = $config->website->defaultMonthlyProductId;

        $this->view->defaultProduct = Model_DbTable_Product::fetchRowByValueStatic('ID', $defaultProductId);
        $this->view->defaultMonthlyProduct = Model_DbTable_Product::fetchRowByValueStatic('ID', $defaultMonthlyProductId);

        $newBusinessSession = new Zend_Session_Namespace(Model_DbTableRow_Business::SESSION_NAMESPACE_NEW_BUSINESS);
        $newBusinessSession->isFacebookLead = false;

        $this->view->title = 'Kiwi businesses use NoCowboys to track our customers, '
            . 'build reputations, and find new work.';
        $this->view->headMeta()->setName('description', 'NoCowboys is the trusted place to safeguard your '
            . 'business\'s reputation. See what we can do for your company');
        $this->view->headMeta()->setName('keywords', 'Why (use NC), Reputation, Work, Business, Management, '
            . 'Marketing, Registration, Growth, Benefits, Advantages, Profile, Customers, Networking, Exposure');
    }

    /**
     * Action for sending an email to a business, mainly used by mobile
     * 
     */
    public function emailAction()
    {
        $business = $this->_getBusinessFromUri();
        $this->_redirectToBusinessHomePage($business);

        $this->view->title = 'Send an email to ' . $business->CompanyName;
        $this->_helper->BusinessSendEmail($business);
        $this->_helper->layout->setLayout('v3/layout');

        $breadcrumbArray = $business->getBreadcrumbArray('Email');
        $this->view->breadcrumb = $breadcrumbArray;

        $this->view->headMeta()->setName('description', 'Need to get in touch with ' . $business->CompanyName . '? Send them an email using our easy email sender');
    }

    /**
     * Action for sending an SMS to a business, mainly used by mobile
     * 
     */
    public function smsAction()
    {
        $business = $this->_getBusinessFromUri();
        $this->_redirectToBusinessHomePage($business);

        $this->view->title = 'Send an SMS to ' . $business->CompanyName;
        $this->_helper->BusinessSendTextMessage($business);
        $this->_helper->layout->setLayout('v3/layout');

        $breadcrumbArray = $business->getBreadcrumbArray('SMS');
        $this->view->breadcrumb = $breadcrumbArray;

        $this->_enableBusinessPageJs();

        $this->view->headMeta()->setName('description', 'Need to get in touch with ' . $business->CompanyName . '? Send them an SMS message using our easy SMS sender');
    }

    /**
     * Action for rating a business, mainly used by mobile
     * 
     */
    public function rateAction()
    {
        // Don't index this page
        $this->_setNoIndex();
        $business = $this->_getBusinessFromUri();

        $breadcrumbArray = $business->getBreadcrumbArray('Rate');
        $this->view->breadcrumb = $breadcrumbArray;

        $this->view->title = 'Rate ' . $business->CompanyName;

        $form = new Form_Business_Rate();

        if ($this->getRequest()->isPost()) {
            $formData = $this->getRequest()->getPost();
            if ($form->isValid($formData)) {
                $formData = $form->getValues();
                $this->_rateBusiness($business, $formData);
            } else {
                $form->populate($formData);
            }
        }

        $this->view->form = $form;
        $this->_helper->layout->setLayout('v3/layout');

        $this->_enableBusinessPageJs();
    }

    /**
     * Redirects to a business' website. This is legacy, but some things
     *  still link to it
     * 
     * @deprecated since version 3.0
     */
    public function websiteAction()
    {
        $business = $this->_getBusinessFromUri();

        if ((is_null($business)) or ( trim($business->Website) == '')) {
            $this->_throw404();
        }

        $this->redirect($business->Website);
        exit();
    }

    /**
     * 
     * @param type $business
     * @param type $formData
     * @param type $allowRating
     */
    protected function _rateBusiness($business, $formData, $allowRating = NULL)
    {
        // Save the user to the newsletter
        Model_DbTable_MailingList::addUser($formData['emailAddress'], $formData['firstName'], isset($formData['newsletter']) and ( $formData['newsletter'] == 1));

        // Rating is good to go - create it and save it
        $ratingTable = new Model_DbTable_Rating();
        /** @var Model_DbTableRow_Rating $newRating */
        $newRating = $ratingTable->createRow($formData);
        $newRating->BusinessID = $business->ID;
        $newRating->need_approve = Model_DbTable_Rating::RATING_STATUS_UNAPPROVED;
        $newRating->save([
            'history' => true
        ]);

        // Check the rating isn't spam and get any similar ratings
        $isSpam = $newRating->isSpam();
        $isBlacklisted = $newRating->isSenderBlacklisted();
        $isWhitelisted = $newRating->isSenderWhitelisted();
        $similarRatings = $newRating->getSimilarRatings();

        $success = true;
        if (!$isWhitelisted) {
            $success = false;
            if ($isBlacklisted) {
                $this->showError(Model_DbTable_Blacklist::MESSAGE_BLACKLISTED);
            } elseif ($isSpam) {
                $newRating->sendSpamEmail();

                $this->showError('Looks like there may be a problem with your rating. We\'ve sent you an email 
                        explaining the problem, and it also contains instructions for how to help us fix it for you. Check your
                        inbox and follow the instructions and we\'ll put it right as soon as possible.');
            } elseif (count($similarRatings) > 0) {
                $newRating->sendDuplicateEmail();

                $newRating->Locked = 1;
                $newRating->Hidden = 1;
                $newRating->save();

                $this->showWarning('It looks like you may have rated this business before. This sometimes happens
                            when you use the same company more than once, or post a rating on the same network as someone
                            else using NoCowboys. Don\'t worry though, we\'ve sent you an email now with instructions for 
                            how to activate your rating. Check that, and we\'ll put it right as soon as possible.');
            } else {
                $success = true;
            }
        }

        if ($success) {
            Nocowboys_Tools::setDetectionData(Model_DbTableRow_DetectionData::TYPE_RATING, $formData + [
                'Rate url' => $this->getCurrentUrl(),
                'Business name' => $business->CompanyName,
            ]);

            $newRating->sendActivationEmail();

            $businessUrl = $this->view->url(['businessUri' => $business->URLName], 'view-business') . '?most-recent-rating=' . $newRating->ID . '&allow-rating=1';
            $this->redirect($businessUrl);
        }

        // Redirect back to the business page
        if (!is_null($allowRating)) {
            header('location: /businesses/' . $business->URLName . '?allow-rating=1');
        } else {
            header('location: /businesses/' . $business->URLName);
        }
        exit();
    }

    /**
     * Gets the business from the URL parameter
     * 
     * @return Model_DbTableRow_Business
     */
    protected function _getBusinessFromUri()
    {
        $businessUri = $this->_request->getParam('businessUri');

        // Load the business if it exists
        $businessTable = new Model_DbTable_Business();
        $business = $businessTable->findBusinessByUri($businessUri);

        // If there's no business, then throw a 404
        if (is_null($business)) {
            $this->_throw404();
        }

        return $business;
    }

    /**
     * Redirect customer to business homepage if business is unregistered.
     * @param Model_DbTableRow_Business $business
     */
    protected function _redirectToBusinessHomePage(Model_DbTableRow_Business $business)
    {
        if (!$business->isRegistered()) {
            $this->_helper->redirector->gotoRouteAndExit(['businessUri' => $business->URLName], 'view-business');
        }
    }

}

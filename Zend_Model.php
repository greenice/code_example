<?php

use \Elastica\Document as ElasticDocument;

/**
 * Represents a single business object
 */
class Model_DbTableRow_Business extends Nocowboys_Db_Table_Row_Abstract
{

    use Nocowboys_Rotate_Image_EXIF;

    public $categories = NULL;
    public $areas = NULL;
    public $locationArea = NULL;
    public $postalLocationArea = NULL;
    public $headOfficeBusinesses = NULL;
    public $headOfficeBusinessIDs = NULL;
    public $headOffices = NULL;
    public $headOfficeRatings = NULL;
    public $headOfficeJobsInterestedIn = NULL;
    public $colour = NULL;
    public $images = NULL;
    public $userType = 'business';
    protected $_mapHidden = NULL;
    protected $_accessibleFields = [
        'AboutUs', 'AccountType', 'ContactPhone1', 'ContactPhone2', 'Facebook', 'Twitter', 'Instagram', 'Youtube', 'Linkedin',
        'EmailAddress', 'Fax', 'LocationAreaID', 'MapOption',
        'URLName', 'Username', 'Website', 'WhatWeDo', 'DontCallAgain', 'IsSendText',
        'Mobile', 'MobilePrefix', 'MobileNumber', 'Phone1Prefix', 'Phone1Number',
        'Phone2Prefix', 'Phone2Number', 'AddressStreetNumber', 'AddressUnitNumber',
        'AddressStreetName', 'addressPostcode', 'hideAddress', 'SuburbID',
        'Suburb', 'businessSuburbId', 'PostalStreetNumber', 'PostalUnitNumber',
        'PostalStreetName', 'PostalSuburbID', 'postalPostcode', 'hidePostalAddress',
        'TradeMeMemberID', 'Notes', 'Name', 'lastName', 'ExpiryDate', 'Mobile',
        'Availability', 'primaryCategoryId', 'salesStatus', 'SalesEmail'
    ];
    protected $_metaKeyMeta = NULL;

    const DEFAULT_DATE = '1970-01-01 00:00:00';
    const SESSION_NAMESPACE_NEW_BUSINESS = 'new_business';
    const METAKEYAUTOMATEDEMAILSACTIVE = 'automatedEmailsActive';
    const METAKEYAUTOMATEDEMAILSCONTENT = 'automatedEmailsContent';
    const METAKEYAUTOMATEDEMAILSSUBJECT = 'automatedEmailsSubject';
    const METAKEYAUTOMATEDEMAILSCUTOFF = 'automatedEmailsCutoff';
    const METAKEYPOSITIVERATINGCUTOFF = 'ratingsPositiveCutoff';
    const METAKEYNEGATIVERATINGCUTOFF = 'ratingsNegativeCutoff';
    const METAKEYRATINGNOTIFICATIONLEVEL = 'ratingNotificationLevel';
    const METAKEYJOBAREAS = 'jobAreas';
    const METAKEYJOBCATEGORIES = 'jobCategories';
    const METAKEYVIDEO = 'video';
    const METAKEYHIDEMAP = 'hideMap';
    const METAKEYMETA = 'meta';
    const META_KEY_SOCIAL_LINK_FACEBOOK = 'facebook';
    const META_KEY_SOCIAL_LINK_TWITTER = 'twitter';
    const META_KEY_SOCIAL_LINK_INSTAGRAM = 'instagram';
    const META_KEY_SOCIAL_LINK_YOUTUBE = 'youtube';
    const META_KEY_SOCIAL_LINK_LINKEDIN = 'linkedin'; // it seems not used in any meta keys so changed and by changing to - meta value not assigning to meta key.
    const META_KEY_SOCIAL_LINK_GOOGLEPLUS = 'googleplus';
    const META_KEY_SOCIAL_LINK_TRADEME = 'trademe';
    const CACHE_KEY_PREFIX_AREAS = 'business_areas_';
    const CACHE_KEY_PREFIX_CATEGORIES = 'business_categories_';
    const CACHE_KEY_BADGES = 'business_badges_';
    const BADGE_REGISTERED = 'registered';
    const BADGE_CUSTOMER_PREFERRED = 'customer_preferred';
    const BADGE_BRONZE_BUSINESS = 'bronze_business';
    const BADGE_SILVER_BUSINESS = 'silver_business';
    const BADGE_GOLD_BUSINESS = 'gold_business';
    const BADGE_PLATINUM_BUSINESS = 'platinum_business';
    const BADGE_DEFENDER = 'defender';
    const BADGE_APPRECIATOR = 'appreciator';
    const BADGE_SOCAL_STAR = 'social_star';
    const BADGE_BARON = 'baron';
    const BADGE_DUKE = 'duke';
    const BADGE_COLLECTOR = 'collector';
    const BADGE_HOARDER = 'hoarder';
    const BADGE_SKEPTIC = 'skeptic';
    const BADGE_REGISTERED_NZ_COMPANY = 'registered_nz_company';
    const HIDE_FROM_SEARCH_ENGINES = 1;
    const HIDE_BUSINESS_INFORMATION = 1;
    const AVAILABILITY_JOBS_ON = 1;
    const AVAILABILITY_JOBS_OFF = 0;
    const FACEBOOK_LEAD_TYPE_1 = 1;
    const FACEBOOK_LEAD_TYPE_2 = 2;
    const FACEBOOK_LEAD_TYPE_3 = 3;
    const FACEBOOK_LEAD_TYPE_4 = 4;
    const FACEBOOK_LEAD_TYPE_5 = 5;
    const FACEBOOK_LEAD_TYPE_6 = 6;
    const AVAILABLE_FOR_RECENT_RATING = 1;

    public static $badges = [
        self::BADGE_REGISTERED => [
            'title' => 'Registered',
            'description' => 'serious about their reputation on NC'
        ],
        self::BADGE_CUSTOMER_PREFERRED => [
            'title' => 'Customer preferred',
            'description' => 'well liked in the NC community'
        ],
        self::BADGE_BRONZE_BUSINESS => [
            'title' => 'Bronze business',
            'description' => 'over 50 positive ratings'
        ],
        self::BADGE_SILVER_BUSINESS => [
            'title' => 'Silver business',
            'description' => 'over 100 positive ratings'
        ],
        self::BADGE_GOLD_BUSINESS => [
            'title' => 'Gold business',
            'description' => 'over 500 positive ratings'
        ],
        self::BADGE_PLATINUM_BUSINESS => [
            'title' => 'Platinum business',
            'description' => 'over 1,000 positive ratings'
        ],
        self::BADGE_DEFENDER => [
            'title' => 'Defender',
            'description' => 'has responded to over 50% of their negative reviews'
        ],
        self::BADGE_APPRECIATOR => [
            'title' => 'Appreciator',
            'description' => 'Has responded to over 50% of their positive reviews'
        ],
        self::BADGE_SOCAL_STAR => [
            'title' => 'Social star',
            'description' => 'has at least five Facebook Likes or Google +1s'
        ],
        self::BADGE_BARON => [
            'title' => 'NoCowboys Baron',
            'description' => 'in the top 50 businesses on NoCowboys'
        ],
        self::BADGE_DUKE => [
            'title' => 'NoCowboys Duke',
            'description' => 'in the top 10 businesses on NoCowboys'
        ],
        self::BADGE_COLLECTOR => [
            'title' => 'Rating collector',
            'description' => 'has won NoCowboy\'s Business of the Month award'
        ],
        self::BADGE_HOARDER => [
            'title' => 'Rating hoarder',
            'description' => 'has won NoCowboy\'s Business of the Year award'
        ],
        self::BADGE_SKEPTIC => [
            'title' => 'Skeptic business',
            'description' => 'Challenged at least five negative reviews'
        ],
        self::BADGE_REGISTERED_NZ_COMPANY => [
            'title' => 'Registered NZ company',
            'description' => 'Registered with the NZ Companies Office',
            'size' => 'double'
        ]
    ];

    const REGISTRATION_GRACE_PERIOD_DAYS = 5;

    /**
     * @var array utm-params
     */
    public static $utmParams = [
        'utm_source' => 'nocowboys-site',
        'utm_medium' => 'business-profile',
        'utm_campaign' => 'website'
    ];

    /**
     * Gets utm query string.
     *
     * @return string
     *
     */
    static public function getUtmQueryString()
    {
        return http_build_query(self::$utmParams);
    }

    /**
     * Loads the ratings for this business
     * @param boolean $getCached Set to false to get non-cached ratings
     * @param boolean $getHidden Set to true to get hidden ratings
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getRatings($getCached = true, $getHidden = false)
    {
        $ratingTable = new Model_DbTable_Rating();
        $select = $ratingTable->select()
            ->where('businessID = ?', $this->ID)
            ->where('Locked = 0')
            ->where('need_approve = 0')
            ->where('ISNULL(deleted)')
            ->order('RankDate DESC');

        if (!$getHidden) {
            $select->where('Rating.Hidden = 0');
        } else {
            // Hide the ratings that are hidden and aren't querying authenticity
            //  as they're hidden by us
            $select->where('(Rating.Hidden = 0 OR (Rating.Hidden = 1 AND Rating.IsQueryAuthenticity = 1)) AND NOT (Rating.need_approve = ?)', Model_DbTable_Rating::RATING_STATUS_BLOCKED);
        }

        if ($getCached) {
            return $ratingTable->fetchAllCache($select);
        } else {
            return $ratingTable->fetchAll($select);
        }
    }

    /**
     * Gets all the replies for ratings for this business
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getRatingReplies()
    {
        $replyModel = new Model_DbTable_RatingReply();
        $replySelect = $replyModel->select()
            ->setIntegrityCheck(false)
            ->from('RatingReply')
            ->joinInner('Rating', 'RatingReply.RatingID = Rating.ID')
            ->order('ReplyDate DESC');

        if ($this->isHeadOffice()) {
            $businessIds = $this->getHeadOfficeBusinessIDs();
            $replySelect->where('Rating.BusinessID IN (' . implode(',', $businessIds) . ')');
        } else {
            $replySelect->where('Rating.BusinessID = ' . $this->ID);
        }

        return $replyModel->fetchAll($replySelect);
    }

    /**
     * Loads the authenticated ratings for this business
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getAuthenticatedRatings($getCached = true, $getComments = false, $fromDate = NULL, $toDate = NULL)
    {
        $ratingTable = new Model_DbTable_Rating();
        $select = $ratingTable->select()
            ->where('businessID = ?', $this->ID)
            ->where('Rating.Locked <> 1')
            ->where('Rating.need_approve = ?', Model_DbTable_Rating::RATING_STATUS_APPROVED)
            ->where('ISNULL(Rating.deleted)')
            ->where('Rating.Hidden = 0')
            ->where('Rating.isSpam = 0')
            ->order('RankDate DESC');

        if (!$getComments) {
            $select->where('NotUsedBefore = 0');
        }

        if (!is_null($fromDate)) {
            $select->where('RankDate >= "' . Nocowboys_Tools::databaseSafeDateTime($fromDate) . '"');
        }

        if (!is_null($toDate)) {
            $select->where('RankDate < "' . Nocowboys_Tools::databaseSafeDateTime($toDate) . '"');
        }

        if ($getCached) {
            $results = $ratingTable->fetchAllCache($select);
        } else {
            $results = $ratingTable->fetchAll($select);
        }

        unset($ratingTable);
        return $results;
    }

    /**
     * Gets comments for this business
     * @param boolean $getCached Set to false to force-load comments without
     *  caching
     * @return Zend_Db_Table_Rowset_Abstract A list of ratings
     */
    public function getComments($getCached = true)
    {
        $ratingTable = new Model_DbTable_Rating();
        $select = $ratingTable->select()
            ->where('businessID = ?', $this->ID)
            ->where('Rating.Locked <> 1')
            ->where('Rating.Hidden = 0')
            ->where('Rating.isSpam = 0')
            ->where('NotUsedBefore = 1')
            ->order('RankDate DESC');

        if ($getCached) {
            return $ratingTable->fetchAllCache($select);
        } else {
            return $ratingTable->fetchAll($select);
        }
    }

    /**
     *
     * @return integer
     */
    public function isNotPaying()
    {
        $notPayingTable = new Model_DbTable_NotPayingBusiness();
        $select = $notPayingTable->select()
            ->where('BusinessID = ?', $this->ID);
        return count($notPayingTable->fetchAll($select)) > 0;
    }

    /**
     * Loads the unauthenticated ratings for this business
     * @return Zend_Db_Table_Rowset_Abstract A list of unauthenticated ratings
     */
    public function getUnauthenticatedRatings($getCached = true, $beforeDate = NULL)
    {
        $beforeDateText = '';

        $ratingTable = new Model_DbTable_Rating();
        $select = $ratingTable->select()
            ->where('BusinessID=?', $this->ID)
            ->where('Rating.Locked = 1 AND Rating.Hidden = 0 AND NotUsedBefore = 0 ' . $beforeDateText)
            ->order('RankDate DESC');

        if (!is_null($beforeDate)) {
            $select->where('RankDate < "' . Nocowboys_Tools::databaseSafeDateTime($beforeDate) . '"');
        }

        if ($getCached) {
            return $ratingTable->fetchAllCache($select);
        } else {
            return $ratingTable->fetchAll($select);
        }
    }

    /**
     * Calculates the overall rating percentage for this business by counting
     *  the ratings and their values. The total is saved to the business, along
     *  with the positive, negative and netural counts (not used in the new version
     *  but is used with the legacy code)
     * @return int The overall rating percentage for this business
     */
    public function calculateOverallRating()
    {
        $ratings = $this->getAuthenticatedRatings(false);
        $ratingCount = count($ratings);
        $overall = 0;
        $negativeRatingCount = 0;
        $positiveRatingCount = 0;

        foreach ($ratings as $rating) {
            $ratingValue = $rating->calculateRating();
            $overall += $ratingValue;

            if ($ratingValue >= Nocowboys_Tools::$ratingPositiveCutoff) {
                $positiveRatingCount++;
            }

            if ($ratingValue <= Nocowboys_Tools::$ratingNegativeCutoff) {
                $negativeRatingCount++;
            }
        }

        if ($ratingCount > 0) {
            $overall = $overall / $ratingCount;
        }

        $this->TempOverall = $overall;
        $this->TempNegative = $negativeRatingCount;
        $this->TempPositive = $positiveRatingCount;
        $this->TempNeutral = $ratingCount - $negativeRatingCount - $positiveRatingCount;
        $this->save();

        $this->updateToSolr();

        return $this->TempOverall;
    }

    /**
     * Checks if the business is customer preferred or not
     * @return Boolean True if the business is Customer Preferred
     */
    public function isCustomerPreferred()
    {
        // Customer preferred is the same requirements as a job access
        if ($this->OverrideCustomerPreferred == 1) {
            return false;
        } else {
            return $this->checkBusinessPreferred();
        }
    }

    /**
     * Checks if the business has access to jobs
     * @return Boolean True if the business has access to jobs
     */
    public function checkBusinessPreferred()
    {
        return ($this->_checkBusinessByOverallRating()
            and $this->_checkBusinessByRatingCount()
            and $this->_checkBusinessByRecentRating());
    }

    /**
     * Checks if the business meet the requirements by overall rating.
     *
     *
     * @return bool
     */
    private function _checkBusinessByOverallRating()
    {

        $config = Zend_Registry::get('config');

        return (int) $this->TempOverall >= (int) $config->jobs->access->business->overallRating;
    }

    /**
     * Checks if the business meet the requirements by rating count.
     *
     *
     * @return bool
     */
    private function _checkBusinessByRatingCount()
    {
        $config = Zend_Registry::get('config');
        $ratingCount = $this->getAuthenticatedRatings(false)->count();

        return (int) $ratingCount >= (int) $config->jobs->access->business->ratingCount;
    }

    /**
     * Checks if the business meet the requirements by recent rating.
     *
     *
     * @return bool
     */
    private function _checkBusinessByRecentRating()
    {
        $config = Zend_Registry::get('config');
        $recentRating = $this->getRecentRating();

        return $recentRating ? ((int) strtotime('+' . $config->jobs->access->business->recentRating->days
                . ' days', strtotime($recentRating->RankDate)) >= time()) : false;
    }

    /**
     * Gets the tasks for this business
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getTasks()
    {
        $taskObject = new Model_Task();
        $select = $taskObject->select()->where('businessId=?', $this->ID)->where('task.completed = 0');

        return $taskObject->fetchAll($select);
    }

    /**
     * Gets the ratings for which more info was requested in the past for this business
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getRatingRequestMoreInfo()
    {
        $ratingRequestMoreInfoObject = new Model_DbTable_RatingRequestMoreInfo();
        $select = $ratingRequestMoreInfoObject->select()->where('BusinessID=?', $this->ID);

        return $ratingRequestMoreInfoObject->fetchAll($select);
    }

    /**
     * Gets the history for the business
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getHistory()
    {
        $historyObject = new Model_DbTable_BusinessCorrespondence();

        // History should not contain
        $select = $historyObject->select()
            ->where('BusinessID=?', $this->ID)
            ->where('type <> 2')
            ->where('type <> 0')
            ->order(array('DateSent DESC'));

        return $historyObject->fetchAll($select);
    }

    /**
     * Gets the current categories this business has selected
     * @return Zend_Db_Table_Rowset_Abstract
     * @deprecated use getCategoriesCorrect() instead
     */
    public function getCategories()
    {
        $categoryObject = new Model_Category();
        $select = $categoryObject->select()
            ->setIntegrityCheck(false)
            ->from(array('category' => 'Category'))
            ->join(array('businessCategory' => 'BusinessCategory'), 'category.ID = businessCategory.CategoryID', array())
            ->where('BusinessID=?', $this->ID)
            ->order(array('category.Name DESC'));

        return $categoryObject->fetchAll($select);
    }

    /**
     * Gets the current categories this business has selected. This is the correct
     *  up-to-date method to use. Don't use getCategories()
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getCategoriesCorrect($getCached = true)
    {
        if ((is_null($this->categories)) or (!$getCached)) {
            $categoryTable = new Model_DbTable_Category();
            $select = $categoryTable->select()
                ->setIntegrityCheck(false)
                ->from(array('category' => 'Category'))
                ->join(array('businessCategory' => 'BusinessCategory'), 'category.ID = businessCategory.CategoryID', array())
                ->where('BusinessID=?', $this->ID)
                ->order(array('category.Name DESC'));

            if ($getCached) {
                $this->categories = $categoryTable->fetchAllCache($select, NULL, NULL, NULL, array(), false, 8, self::CACHE_KEY_PREFIX_CATEGORIES . $this->ID);
            } else {
                $this->categories = $categoryTable->fetchAll($select);
            }

            unset($categoryTable);
        }

        return $this->categories;
    }

    /**
     * Gets the current areas this business has selected
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getAreas($getCached = true)
    {
        if ((is_null($this->areas)) or (!$getCached)) {
            $areaObject = new Model_DbTable_Area();
            $select = $areaObject->select()
                ->setIntegrityCheck(false)
                ->from(array('area' => 'Area'))
                ->join(array('businessArea' => 'BusinessArea'), 'area.ID = businessArea.AreaID', array())
                ->where('BusinessID=?', $this->ID)
                ->order(array('area.Name DESC'));

            if ($getCached) {
                $this->areas = $areaObject->fetchAllCache($select, NULL, NULL, NULL, array(), false, 8, self::CACHE_KEY_PREFIX_AREAS . $this->ID);
            } else {
                $this->areas = $areaObject->fetchAll($select);
            }

            unset($areaObject);
        }

        return $this->areas;
    }

    /**
     * Get the relavent jobs for this business
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getJobs()
    {
        $jobObject = new Model_DbTable_Job();

        $select = $jobObject->select()
            ->distinct()
            ->setIntegrityCheck(false)
            ->from(array('job' => 'Job'))
            ->joinLeft(array('businessInterestedinJob' => 'BusinessInterestedinJob'), 'businessInterestedinJob.JobID = job.ID And (businessInterestedinJob.IsQuestionAsked = 1 or businessInterestedinJob.IsQuoteSent = 1) and businessInterestedinJob.BusinessID <> 0', array('COUNT(distinct(businessInterestedinJob.ID)) AS InterestedBusinessCount'))
            ->joinLeft(array('businessCategory' => 'BusinessCategory'), '(CategoryID = job.ParentCategoryID or CategoryID = job.SubCategoryID or CategoryID = job.SubSubCategoryID) and CategoryID <> 0', array())
            ->joinLeft(array('businessArea' => 'BusinessArea'), '(businessArea.AreaID = job.RegionID or businessArea.AreaID = job.AreaID or businessArea.AreaID = job.SuburbAreaID) and businessArea.AreaID <> 0', array())
            ->joinLeft(array('business' => 'Business'), 'business.ID = businessCategory.BusinessID and business.ID = businessArea.BusinessID', array())
            ->where('job.IsOnline = 1')
            ->where('businessCategory.BusinessID = ?', $this->ID)
            ->where('businessArea.BusinessID = ?', $this->ID)
            ->group('job.ID')
            ->order(array('job.DateAdded DESC'));

        return $jobObject->fetchAll($select);
    }

    /**
     * Gets the categories the business prefers for their jobs. If none are set up
     *  then it defaults to their usual categories
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getJobCategories()
    {
        $jobCategories = $this->getMetaData(self::METAKEYJOBCATEGORIES);

        if (!is_null($jobCategories)) {
            // $jobCategories should be a comma-separated list of category IDs
            $categoryTable = new Model_DbTable_Category();
            return $categoryTable->fetchAll('ID IN (' . $jobCategories . ')');
        } else {
            return $this->getCategories();
        }
    }

    /**
     * Gets the areas the business prefers for their jobs. If none are set up
     *  then it defaults to their usual areas
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getJobAreas()
    {
        $jobAreas = $this->getMetaData(self::METAKEYJOBAREAS);

        if (!is_null($jobAreas)) {
            // $jobAreas should be a comma-separated list of area IDs
            $areaTable = new Model_DbTable_Area();
            return $areaTable->fetchAll('ID IN (' . $jobAreas . ')');
        } else {
            return $this->getAreas();
        }
    }

    /**
     * Gets the jobs this business has shown interest in
     * @return Zend_Db_Table_Rowset_Abstract Rowet of jobs the business has
     *  shown interest in
     */
    public function getQuotedJobs()
    {
        $jobTable = new Model_DbTable_Job();
        $jobSelect = $jobTable->select()
            ->setIntegrityCheck(false)
            ->from('Job')
            ->joinInner('BusinessInterestedinJob', 'BusinessInterestedinJob.JobID = Job.ID', array('quoteDate' => 'DateAdded'))
            ->where('BusinessID = ' . $this->ID)
            ->where('IsQuoteSent = 1 OR IsQuestionAsked = 1')
            ->order('DateAdded DESC');

        return $jobTable->fetchAll($jobSelect);
    }

    /**
     * Gets the TM listings for this business
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getTMListings()
    {
        $tradeMeCategoryListingObject = new Model_DbTable_TradeMeCategoryListing();
        $select = $tradeMeCategoryListingObject->select()
            ->where('NoCowboysID=?', $this->ID)
            ->where('IsOnline = 1')
            ->order('DateAdded DESC');
        return $tradeMeCategoryListingObject->fetchAll($select);
    }

    /**
     * Gets the date this registration is supposed to expire (if the business is registered)
     * @return Integer
     */
    public function getExpiryDate()
    {
        // Get the paymentlist of the business
        $PaymentList = $this->getAllPaymentsByDatePaidDesc();

        $todays_date = date("Y-m-d");
        $today = strtotime($todays_date) / (3600 * 24);

        // Check if the busines is registered or not
        // If it's registered then get the current expiry date of the business
        if ($this->isRegistered() == 1 or (!empty($this->ExpiryDate) and date(Nocowboys_Tools::DATABASE_DATE_FORMAT, strtotime('+' . self::REGISTRATION_GRACE_PERIOD_DAYS . ' days', strtotime($this->ExpiryDate))) >= $todays_date)) {
            $CurrentExpiryDate = strtotime('-0 days', strtotime($this->ExpiryDate));
        } else { // if not registered then just the registration date (today's date)
            // Set the Current Expiry date as Register date + Free days
            $CurrentExpiryDate = strtotime('+0 days', strtotime($todays_date));
        }

        $ExpiryDate = $CurrentExpiryDate;

        $expiry_date = $CurrentExpiryDate / (3600 * 24);

        if (count($PaymentList) > 0) {
            // Loop through all the payments
            for ($i = 0; $i < 1; $i++) {
                //if(date('d M Y', $Today) < date('d M Y', strtotime($PaymentList[$i]->DatePaid)))
                if (($expiry_date + self::REGISTRATION_GRACE_PERIOD_DAYS - $today) <= 0) {
                    // Set the expiry date as payments date + days paid
                    $ExpiryDate = strtotime('+' . $PaymentList[$i]->DaysPaid . ' days', strtotime($PaymentList[$i]->DatePaid));
                } else {
                    // Set the expiry date as current expiry date + paid days
                    $ExpiryDate = strtotime('+' . $PaymentList[$i]->DaysPaid . ' days', $CurrentExpiryDate);
                }

                $expiry_date = $ExpiryDate / 3600 * 24;
            }
        }

        if (($expiry_date - $today) <= 0) {
            if (($this->IsNotPaying == 1) || ($this->IsOnNCJobsTrial == 1)) {
                $ExpiryDate = strtotime($this->ExpiryDate);
            }
        }

        return $ExpiryDate;
    }

    /**
     * Gets the date this registration is supposed to expire (if the business is registered)
     * @return Integer
     */
    public function getRegistrationExpiryDate()
    {
        // Get the paymentlist of the business
        $PaymentList = $this->getAllPayments();

        // Set the Current Expiry date as Register date + Free days
        //$CurrentExpiryDate = strtotime('+0 days', strtotime($this->RegisterDate));
        $CurrentExpiryDate = strtotime('+0 days', strtotime($this->ExpiryDate));

        $ExpiryDate = $CurrentExpiryDate;

        // $ExpiryDate = $CurrentExpiryDate;
        $todays_date = date("Y-m-d");
        $today = strtotime($todays_date) / 3600 * 24;
        $expiry_date = $CurrentExpiryDate / 3600 * 24;

        if (count($PaymentList) > 0) {

            // Loop through all the payments
            for ($i = 0; $i < count($PaymentList); $i++) {
                // If this is the first payment object then add it into current expiry date
                if ($i == 0) {
                    //if(date('d M Y', $Today) < date('d M Y', strtotime($PaymentList[$i]->DatePaid)))
                    if (($expiry_date - $today) <= 0) {
                        // Set the expiry date as payments date + days paid
                        $ExpiryDate = strtotime('+' . $PaymentList[$i]->DaysPaid . ' days', strtotime($PaymentList[$i]->DatePaid));
                    } else {
                        // Set the expiry date as current expiry date + paid days
                        $ExpiryDate = strtotime('+' . $PaymentList[$i]->DaysPaid . ' days', $CurrentExpiryDate);
                    }


                    $expiry_date = $ExpiryDate / 3600 * 24;
                }

                // Else add it into Expiry date
                if ($i > 0) {
                    $today = strtotime($PaymentList[$i]->DatePaid) / 3600 * 24;

                    //if(date('d M Y', strtotime($PaymentList[$i]->DatePaid)) > date('d M Y', $ExpiryDate))
                    if (($expiry_date - $today) <= 0) {
                        // Set the expiry date as payments date + days paid
                        $ExpiryDate = strtotime('+' . $PaymentList[$i]->DaysPaid . ' days', strtotime($PaymentList[$i]->DatePaid));
                    } else {
                        $ExpiryDate = strtotime('+' . $PaymentList[$i]->DaysPaid . ' days', $ExpiryDate);
                    }

                    $expiry_date = $ExpiryDate / 3600 * 24;
                }
            }

            $today = strtotime($todays_date) / 3600 * 24;
            $expiry_date = $ExpiryDate / 3600 * 24;
        }

        // added by Nik to return expiry date extended manually for the business.
        // may be becoz of some sort of promotion etc, team have to extend the rego expiry date
        $actual_expiry_date = strtotime($this->ExpiryDate) / 3600 * 24;

        if (($actual_expiry_date - $expiry_date) > 0) {
            $ExpiryDate = strtotime($this->ExpiryDate);
        }

        if (($expiry_date - $today) <= 0) {
            if (($this->IsNotPaying == 1) || ($this->IsOnNCJobsTrial == 1)) {
            //if ($this->IsNotPaying == 1)
                $ExpiryDate = strtotime($this->ExpiryDate);
            }
        }

        return $ExpiryDate;
    }

    /**
     * Gets the date this registration is supposed to expire (if the business is registered) from business table.
     * @return integer Unix timestamp
     */
    public function getRegistrationExpiryDateFromTable()
    {
        $ExpiryDate = strtotime($this->ExpiryDate);

        return $ExpiryDate;
    }

    /**
     * Loads all the payments for this business
     * @param boolean $orderAsc Set to false to order descending
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getAllPayments($orderAsc = true)
    {

        $paymentObject = new Model_Payment();
        $select = $paymentObject->select()
            ->where('BusinessID=?', $this->ID)
            ->where('DaysPaid > 0');

        if ($orderAsc) {
            $select->order(array('DatePaid ASC'));
        } else {
            $select->order(array('DatePaid DESC'));
        }

        return $paymentObject->fetchAll($select);
    }

    /**
     * Loads all the payments for this business in datepaid desc order.
     * @return Array
     */
    public function getAllPaymentsByDatePaidDesc()
    {

        $paymentObject = new Model_Payment();
        $select = $paymentObject->select()
            ->where('BusinessID=?', $this->ID)
            ->where('DaysPaid > 0')
            ->order(array('DatePaid DESC'));

        return $paymentObject->fetchAll($select);
    }

    /**
     * Set the user assigned to this business
     * @param integer $userId ID of the user that is assigned to the business
     * @return Boolean
     */
    public function setUser($userId)
    {
        // Delete any users already assigned
        $userBusinessObject = new Model_UserBusiness();
        $select = $userBusinessObject->select()
            ->where('BusinessID=?', $this->ID);

        $assignedUsers = $userBusinessObject->fetchAll($select);
        foreach ($assignedUsers as $user) {
            $user->delete();
        }

        // Assign the new user
        $newUserBusiness = $userBusinessObject->createRow();

        $newUserBusiness->userId = $userId;
        $newUserBusiness->businessId = $this->ID;

        $newUserBusiness->save();
    }

    /**
     * Gets the user assigned to this business
     * @return Integer
     */
    public function getUser()
    {
        $userBusinessObject = new Model_UserBusiness();
        $select = $userBusinessObject->select()
            ->where('BusinessID=?', $this->ID);

        $assignedUser = $userBusinessObject->fetchRow($select);
        return $assignedUser;
    }

    /**
     * Gets the different package options for this business.  If they're on an older package,
     *  they get to choose that too (god knows why, but yeah.)
     * @return array
     */
    public function getAvailablePackages()
    {
        $productTable = new Model_DbTable_Product();

        $productSelect = $productTable->select()
            ->setIntegrityCheck(false)
            //->where($this->quoteInto('ID = ?', $this->SubscribedProductId).' OR (isPackage = 1 AND Active = 1)')
            ->where('isPackage = 1 AND Active = 1')
            ->order('displayOrder DESC');

        return $productTable->fetchAll($productSelect);
    }

    /**
     * Gets the current package if there's one set
     * @param string|null $class Product specific class
     * @return Model_DbTableRow_Product
     * @return mixed
     */
    public function getCurrentPackage($class = null)
    {
        $productTable = new Model_DbTable_Product();
        if ($class) {
            $productTable->setRowClass($class);
        }

        $productSelect = $productTable->select()
            ->where($this->quoteInto('ID = ?', $this->SubscribedProductId));

        return $productTable->fetchRow($productSelect);
    }

    /**
     * Gets the current default package if there's one set or return default package if not
     * @param string|null $class Product specific class
     * @return Model_DbTableRow_Product
     * @return mixed
     */
    public function getCurrentDefaultPackage($class = null)
    {
        $productTable = new Model_DbTable_Product();
        if ($class) {
            $productTable->setRowClass($class);
        }

        if (!empty($this->default_package_id)) {
            $productSelect = $productTable->select()
                ->where($this->quoteInto('ID = ?', $this->default_package_id));
            return $productTable->fetchRow($productSelect);
        } else {
            return $this->getDefaultProduct();
        }
    }

    /**
     * Handles renewing the current package. Call this if we suspect the
     *  package has expired, or is near expiring. The package itself will
     *  take care of payments and reminders
     *
     */
    public function renewCurrentPackage()
    {
        $currentPackage = $this->getCurrentPackage();

        // Send a reminder for the package
        $currentPackage->sendReminder($this);

        // Attempt a payment if required
        if ($currentPackage->shouldAttemptPaymentToday($this)) {
            // Create a new payment and attempt to take it
            $newPurchase = new Model_DbTableRow_Purchase();
            $newPurchase->BusinessID = $this->ID;
            $newPurchase->addProduct($currentPackage);
            $newPurchase->save();
        }
    }

    /**
     * Suggests a unique URL that can be used for displaying on the website
     * @return string URL to use on the website
     */
    public function suggestUrl()
    {
        $businessTable = new Model_DbTable_Business();

        $firstUrl = $businessTable->cleanUrl($this->CompanyName);
        $additionalCounter = 2;

        while (!is_null($businessTable->fetchRowByValue('URLName', $firstUrl))) {
            $firstUrl = $businessTable->cleanUrl($this->CompanyName . '-' . $additionalCounter);
            $additionalCounter++;
        }

        return strtolower($firstUrl);
    }

    /**
     * Returns the full URI for this business.  Even though this is simple, use this so we
     *  only need to make a change in one place
     * @param boolean $leadingSlash Set to false to remove the leading slash
     * @return string
     */
    public function getURI($leadingSlash = true)
    {
        $url = 'businesses/' . $this->URLName;

        if ($leadingSlash) {
            return '/' . $url;
        } else {
            return $url;
        }
    }

    /**
     * Generate a random password. The letter l (lowercase L) and the
     *  number 1 have been removed, as they can be mistaken for each other,
     *  as has the zero (0) and the oh (o) for the same reason
     * @return string Randomly generated password
     */
    public function createRandomPassword()
    {
        $chars = "abcdefghijkmnpqrstuvwxyz23456789";
        srand((double) microtime() * 1000000);
        $i = 0;
        $pass = '';

        while ($i <= 8) {
            $num = rand() % 33;
            $tmp = substr($chars, $num, 1);
            $pass = $pass . $tmp;
            $i++;
        }

        return $pass;
    }

    /**
     * sends welcome email to business for the registration.
     * @return true
     */
    public function sendWelcomeEmail()
    {
        // this code below sends them a welcome email with login details.
        // checks if the username and password are set
        // if not then set it
        if ($this->Username == '') {
            // set Emailaddress as username
            $this->Username = $this->EmailAddress;
        }

        $passwordToSend = '(the password you set)';

        if (trim($this->Password) == '') {
            // Call the function to generate random password for the new businesses
            $password = $this->createRandomPassword();

            $passwordToSend = $password;

            // Set password for new business.
            $this->setPassword($password);
        }

        $this->save();

        $emailArray2['companyName'] = $this->CompanyName;
        $emailArray2['username'] = $this->Username;
        $emailArray2['password'] = $passwordToSend;
        $emailArray2['businessId'] = $this->ID;

        $config = Zend_Registry::get('config');
        $emailArray2['baseUrl'] = str_replace(array('http://', 'https://'), array(''), $config->domain);
        $emailArray2['baseLink'] = $config->domain;
        $emailArray2['loginLink'] = $config->domain . '/login';
        $emailArray2['fillingOutProfileLink'] = $config->email->template->fillingOutProfileLink;
        $emailArray2['registrationEmail'] = $config->email->template->registrationEmail;

        Nocowboys_Email::sendEmail($this->EmailAddress, 'Welcome to NoCowboys', 'businessLogins', $emailArray2);

        return true;
    }

    /**
     * Generic method to send emails to this business. This is just a wrapper, but
     *  means you don't need to worry about the email address to send it to
     * @param string $emailAddress
     * @param string $subject
     * @param string $emailView
     * @param array $emailParameters
     * @param type $layout
     * @param type $overrideFromAddress
     * @param type $overrideFromName
     */
    public function sendEmail($subject, $emailView, $emailParameters, $layout = 'email', $overrideFromAddress = NULL, $overrideFromName = NULL, $options = [])
    {
        $emailAddress = $this->getEmailAddress($options);

        // Add the business ID so it gets stored against their correspondence
        $emailParameters['businessId'] = $this->ID;

        return empty($emailAddress) ? false : Nocowboys_Email::sendEmail($emailAddress, $subject, $emailView, $emailParameters,
                $layout, $overrideFromAddress, $overrideFromName
        );
    }

    /**
     * Returns the email address for this business to send emails to. There are a
     *  couple of email address fields in the database, so this returns the one
     *  best suited
     */
    public function getEmailAddress($options = [])
    {
        if (!empty($options['destination']) and stripos($this->$options['destination'], '@') !== false) {
            return $this->$options['destination'];
        } elseif (stripos($this->EmailAddress, '@') !== false) {
            return $this->EmailAddress;
        } elseif (stripos($this->Username, '@') !== false) {
            return $this->Username;
        } else {
            $logger = Zend_Registry::get('logger');
            $logger->info("No detected email address for business [BusinessID = $this->ID]",
                array('program' => 'business:get_email_address'));
            return false;
        }
    }

    /**
     * Gets the location area for this business.  Returns the area or NULL
     *  if no area is set
     * @return Model_DbTableRow_Area | NULL
     */
    public function getLocationArea()
    {
        if (is_null($this->locationArea)) {
            if ((isset($this->LocationAreaID)) and ( is_numeric($this->LocationAreaID)) and ( $this->LocationAreaID > 0)) {
                $areaTable = new Model_DbTable_Area();
                $this->locationArea = $areaTable->fetchRowByValueCache('ID', $this->LocationAreaID);
            }
        }

        return $this->locationArea;
    }

    /**
     * Gets the postal location area for this business. Returns the area or NULL if no area is set
     * @return Model_DbTableRow_Area | NULL
     */
    public function getPostalLocationArea()
    {
        if (is_null($this->postalLocationArea)) {
            if ((isset($this->PostalSuburbID)) and ( is_numeric($this->PostalSuburbID)) and ( $this->PostalSuburbID > 0)) {
                $areaTable = new Model_DbTable_Area();
                $this->postalLocationArea = $areaTable->fetchRowByValue('ID', $this->PostalSuburbID);
            }
        }

        return $this->postalLocationArea;
    }

    /**
     * Encrypts the password for a user using MD5 and the system salt
     * @param string $password Password to encrypt
     * @return string
     */
    static function encryptPassword($password)
    {
        $passwordSalt = Zend_Registry::get('config')->passwordSalt;

        return md5($passwordSalt . $password);
    }

    /**
     * Gets the meta data for a business.  Returns NULL if no value is found
     * @param string $metaKey The key of the value for this business you want to load
     * @return mixed
     */
    public function getMetaData($metaKey)
    {
        $existingMetaDataObject = $this->getMetaDataObject();

        if (is_null($existingMetaDataObject)) {
            return NULL;
        }

        $metaData = json_decode($existingMetaDataObject->metaValue, true);

        if (isset($metaData[$metaKey])) {
            return $metaData[$metaKey];
        } else {
            return NULL;
        }
    }

    /**
     * Gets the meta data for a business.  Returns NULL if no value is found
     * @param string $metaKey The key of the value for this business you want to load
     * @return mixed
     */
    public function getMetaDataObject()
    {
        if (is_null($this->_metaKeyMeta)) {
            $metaTable = new Model_DbTable_BusinessMeta();

            $select = $metaTable->select()
                ->where('businessId=?', $this->ID)
                ->where('metaKey = ?', self::METAKEYMETA);

            $this->_metaKeyMeta = $metaTable->fetchRow($select);
        }

        return $this->_metaKeyMeta;
    }

    /**
     * Sets meta data for a business.  IMPORTANT: This will overwrite the value for this key
     *  if the key already exists
     * @param string $metaKey The key of the value for this business you want to load
     * @param string $metaValue The value for the key
     * @return boolean
     */
    public function setMetaData($metaKey, $metaValue)
    {
        $metaTable = new Model_DbTable_BusinessMeta();

        // Check if the key exists already
        $existingMetaObject = $this->getMetaDataObject($metaKey);

        if (is_null($existingMetaObject)) {
            // Otherwise create a new meta value
            $newMetaValue = $metaTable->createRow();
            $newMetaValue->metaKey = self::METAKEYMETA;
            $newMetaValue->metaValue = json_encode(array($metaKey => $metaValue));
            $newMetaValue->businessId = $this->ID;

            return $newMetaValue->save();
        } else {
            $metaData = json_decode($existingMetaObject->metaValue, true);
            $metaData[$metaKey] = $metaValue;

            $existingMetaObject->metaValue = json_encode($metaData);
            return $existingMetaObject->save();
        }
    }

    /**
     * Builds an array suitable to be sent to Elastica
     *
     * @return array
     */
    public function buildElasticSearchArray()
    {
        $businessId = $this->ID;

        $ratingTable = new Model_DbTable_Rating();
        $select = $ratingTable->select()
            ->where('businessID = ?', $this->ID)
            ->where('Rating.Locked <> 1')
            ->where('Rating.Comment <> ""')
            ->where('Rating.Hidden = 0')
            ->where('Rating.isSpam = 0')
            ->where('Rating.NotUsedBefore = 0')
            ->order('RankDate DESC')
            ->limit(5);

        $selectMostRecentDate = $ratingTable->select()
            ->where('businessID = ?', $this->ID)
            ->where('Rating.Locked <> 1')
            ->where('Rating.Hidden = 0')
            ->where('Rating.isSpam = 0')
            ->where('Rating.NotUsedBefore = 0')
            ->order('RankDate DESC')
            ->limit(1);

        $countSelect = $ratingTable->select()
            ->from('Rating', array('ratingCount' => 'COUNT(*)'))
            ->where('businessID = ?', $this->ID)
            ->where('Rating.Locked <> 1')
            ->where('Rating.Hidden = 0')
            ->where('Rating.isSpam = 0')
            ->where('Rating.NotUsedBefore = 0')
            ->limit(1);

        $mostRecentRatingDate = $ratingTable->fetchRow($selectMostRecentDate);
        $mostRecentRatings = $ratingTable->fetchAll($select);
        $ratingCount = $ratingTable->fetchRow($countSelect);

        $mostRecentRatingsArray = [];

        if ($this->isRegistered()) {
            foreach ($mostRecentRatings as $recentRating) {
                $mostRecentRatingsArray[] = $recentRating->Comment;
            }
        }

        unset($ratingTable);
        unset($mostRecentRatings);

        // Build an array of categories
        $categories = $this->getCategoriesCorrect(false);
        $categoryArray = [];

        foreach ($categories as $category) {
            $categoryArray[] = $category->ID;
        }

        unset($categories);

        // Build an array of areas
        $areas = $this->getAreas(false);
        $areasArray = array();

        foreach ($areas as $area) {
            $areasArray[] = $area->ID;
        }

        unset($areas);

        // Get the badges for this business
        $badges = $this->getBadges(false);

        // Get the area for the business
        $locationArea = $this->getLocationArea();

        // Check if this area is in the list of areas for the business.  If not, add it.
        //  This means we can search by the area name
        $locationAreaName = !is_null($locationArea) ? $locationArea->Name : '';
        $locationAreaId = !is_null($locationArea) ? $locationArea->ID : NULL;

        // Get the primary category
        $primaryCategory = $this->getPrimaryCategory(true);
        $primaryCategoryId = !is_null($primaryCategory) ? $primaryCategory->ID : NULL;
        $primaryCategoryName = !is_null($primaryCategory) ? $primaryCategory->Name : NULL;
        $categoryArray[] = $primaryCategoryId;

        // Add the business' location area ID if it's not already added
        if ((!is_null($locationAreaId)) and (!in_array($locationAreaId, $areasArray))) {
            $areasArray[] = $locationAreaId;
        }

        // Get the associations
        $associations = $this->getAssociations();
        $associationsArray = [];

        foreach ($associations as $association) {
            $associationsArray[] = ['id' => $association->ID, 'name' => $association->name, 'logoLocation' => $association->logoLocation];
        }

        unset($associations);

        // Update the denomalised categories
        $this->denormaliseCategoryIds();

//			echo $this->isRegistered() . "\n";
        // Create and index the business data
        $elasticArray = [
            'id' => $businessId,
            'uri' => $this->getURI(),
            'businessName' => $this->CompanyName,
            'overallRating' => round($this->TempOverall),
            'ratingCount' => $ratingCount->ratingCount,
            'about' => $this->AboutUs,
            'mostRecentRating' => $mostRecentRatingsArray,
            'mostRecentRatingDate' => is_null($mostRecentRatingDate) ? NULL : date('Y-m-d', strtotime($mostRecentRatingDate->RankDate)),
            'isOnline' => $this->IsOnline == 1 ? true : false,
            'areas' => $areasArray,
            'categories' => $categoryArray,
            'locationArea' => $locationAreaName,
            'locationAreaId' => (integer) $locationAreaId,
            'primaryCategoryId' => $primaryCategoryId,
            'primaryCategoryName' => $primaryCategoryName,
            'customerPreferred' => $this->isCustomerPreferred(),
            'registered' => $this->isRegistered() ? true : false,
            'denormalisedCategories' => explode(',', $this->categoryIds),
            'badges' => $badges,
            'associations' => $associationsArray,
            'phoneNumber' => $this->ContactPhone1 . ' ' . $this->ContactPhone2 . ' ' . $this->Mobile . ' ' . $this->Fax . ' ' .
            $this->Phone1Prefix . $this->Phone1Number . ' ' . $this->Phone2Prefix . $this->Phone2Number,
            'emailAddress' => $this->EmailAddress,
            'contactName' => $this->Name . ' ' . $this->lastName,
            'physicalAreaId' => $this->LocationAreaID,
            'physicalAddress' => $this->AddressStreetName . ' ' . $this->AddressStreetNumber,
            'addressPostCode' => $this->addressPostcode,
            'postAreaId' => $this->PostalSuburbID,
            'postAddress' => $this->PostalStreetName . ' ' . $this->PostalStreetNumber,
            'postCode' => $this->postalPostcode,
            'postBox' => $this->PostalUnitNumber,
            'expiryDate' => $this->ExpiryDate == '0000-00-00 00:00:00' ? self::DEFAULT_DATE : date(Nocowboys_Tools::DATABASE_DATE_TIME_FORMAT, strtotime($this->ExpiryDate)),
            'dateAdded' => $this->DateAdded == '0000-00-00 00:00:00' ? self::DEFAULT_DATE : $this->DateAdded,
            'salesStatus' => $this->salesStatus,
//				'tag_suggest' => [
//					'input' => [$this->CompanyName] + explode(' ', $this->CompanyName),
//					'output' => $this->CompanyName,
//					'payload' => [
//						'overallRating' => round($this->TempOverall),
//						'ratingCount' => $ratingCount->ratingCount,
//						'locationArea' => $locationAreaName,
//						'businessId' => $this->ID,
//						'uri' => $this->getURI(),
//						'badges' => $badges,
//						'associations' => $associationsArray
//					]
//				]
        ];

        if ($inputForTagSuggest = $this->_getInputForTagSuggest($this->CompanyName)) {
            $elasticArray['tag_suggest'] = $inputForTagSuggest;
        }

        unset($mostRecentRatingDate);

        // If there's a location, add it
        $location = $this->getMapCoordinates(true);
        if (!is_null($location)) {
            $elasticArray['location'] = $location->Lat . ',' . $location->Lang;
        }
//			if (count($mostRecentRatingsArray) > 0)
//			{
//				var_dump($elasticArray);
//				exit();
//			}
        return $elasticArray;
    }

    /**
     * Builds an array for CRM search suitable to be sent to Elastica
     *
     * @return array
     */
    public function buildCrmElasticSearchArray()
    {
        $businessId = $this->ID;

        $ratingTable = new Model_DbTable_Rating();
        $select = $ratingTable->select()
            ->where('businessID = ?', $this->ID)
            ->where('Rating.Locked <> 1')
            ->where('Rating.Comment <> ""')
            ->where('Rating.Hidden = 0')
            ->where('Rating.isSpam = 0')
            ->where('Rating.NotUsedBefore = 0')
            ->order('RankDate DESC')
            ->limit(5);

        $selectMostRecentDate = $ratingTable->select()
            ->where('businessID = ?', $this->ID)
            ->where('Rating.Locked <> 1')
            ->where('Rating.Hidden = 0')
            ->where('Rating.isSpam = 0')
            ->where('Rating.need_approve = 0')
            ->where('Rating.NotUsedBefore = 0')
            ->order('RankDate DESC')
            ->limit(1);

        $countSelect = $ratingTable->select()
            ->from('Rating', array('ratingCount' => 'COUNT(*)'))
            ->where('businessID = ?', $this->ID)
            ->where('Rating.Locked <> 1')
            ->where('Rating.Hidden = 0')
            ->where('Rating.need_approve = 0')
            ->where('Rating.isSpam = 0')
            ->where('Rating.NotUsedBefore = 0')
            ->limit(1);

        $tasks = NULL;
        $countTasks = NULL;

        $taskTable = new Model_DbTable_Task();
        $taskSelect = $taskTable->select()
            ->from('task')
            ->where('businessId = ?', $this->ID)
            ->where('completed = ?', 0)
            ->order('dateTime ASC');
        $result = $taskTable->fetchAll($taskSelect)->toArray();
        if (!empty($result)) {
            $tasks = $result;
            $countTasks = count($tasks);
        }


        $mostRecentRatingDate = $ratingTable->fetchRow($selectMostRecentDate);
        $mostRecentRatings = $ratingTable->fetchAll($select);
        $ratingCount = $ratingTable->fetchRow($countSelect);

        $mostRecentRatingsArray = [];

        if ($this->isRegistered()) {
            foreach ($mostRecentRatings as $recentRating) {
                $mostRecentRatingsArray[] = $recentRating->Comment;
            }
        }

        unset($ratingTable);
        unset($mostRecentRatings);

        // Build an array of categories
        $categories = $this->getCategoriesCorrect(false);
        $categoryArray = [];

        foreach ($categories as $category) {
            $categoryArray[] = $category->ID;
        }

        unset($categories);

        // Build an array of areas
        $areas = $this->getAreas(false);
        $areasArray = array();

        foreach ($areas as $area) {
            $areasArray[] = $area->ID;
        }

        unset($areas);

        // Get the badges for this business
        $badges = $this->getBadges(false);

        // Get the area for the business
        $locationArea = $this->getLocationArea();

        // Check if this area is in the list of areas for the business.  If not, add it.
        //  This means we can search by the area name
        $locationAreaName = !is_null($locationArea) ? $locationArea->Name : '';
        $locationAreaId = !is_null($locationArea) ? $locationArea->ID : NULL;

        // Get the primary category
        $primaryCategory = $this->getPrimaryCategory(true);
        $primaryCategoryId = !is_null($primaryCategory) ? $primaryCategory->ID : NULL;
        $primaryCategoryName = !is_null($primaryCategory) ? $primaryCategory->Name : NULL;
        $categoryArray[] = $primaryCategoryId;

        // Add the business' location area ID if it's not already added
        if ((!is_null($locationAreaId)) and (!in_array($locationAreaId, $areasArray))) {
            $areasArray[] = $locationAreaId;
        }

        // Get the associations
        $associations = $this->getAssociations();
        $associationsArray = [];

        foreach ($associations as $association) {
            $associationsArray[] = ['id' => $association->ID, 'name' => $association->name, 'logoLocation' => $association->logoLocation];
        }

        unset($associations);

        // Update the denomalised categories
        $this->denormaliseCategoryIds();

        $assignedUser = $this->getUser();

        $lastPayment = $this->getLastPayment();

        $currentPackage = $this->getCurrentPackage();

        // Create and index the business data
        $elasticArray = [
            'id' => $businessId,
            'uri' => $this->getURI(),
            'businessName' => $this->CompanyName,
            'overallRating' => round($this->TempOverall),
            'ratingCount' => $ratingCount->ratingCount,
            'about' => $this->AboutUs,
            'mostRecentRating' => $mostRecentRatingsArray,
            'mostRecentRatingDate' => is_null($mostRecentRatingDate) ? NULL : date('Y-m-d', strtotime($mostRecentRatingDate->RankDate)),
            'isOnline' => $this->IsOnline == 1 ? true : false,
            'areas' => $areasArray,
            'categories' => $categoryArray,
            'locationArea' => $locationAreaName,
            'locationAreaId' => (integer) $locationAreaId,
            'primaryCategoryId' => $primaryCategoryId,
            'primaryCategoryName' => $primaryCategoryName,
            'customerPreferred' => $this->isCustomerPreferred(),
            'registered' => $this->isRegistered() ? true : false,
            'denormalisedCategories' => explode(',', $this->categoryIds),
            'badges' => $badges,
            'associations' => $associationsArray,
            'phoneNumber' => $this->ContactPhone1 . ' ' . $this->ContactPhone2 . ' ' . $this->Mobile . ' ' . $this->Fax . ' ' .
            $this->Phone1Prefix . $this->Phone1Number . ' ' . $this->Phone2Prefix . $this->Phone2Number,
            'emailAddress' => $this->EmailAddress,
            'contactName' => $this->Name . ' ' . $this->lastName,
            'physicalAreaId' => $this->LocationAreaID,
            'physicalAddress' => $this->AddressStreetName . ' ' . $this->AddressStreetNumber,
            'addressPostCode' => $this->addressPostcode,
            'postAreaId' => $this->PostalSuburbID,
            'postAddress' => $this->PostalStreetName . ' ' . $this->PostalStreetNumber,
            'postCode' => $this->postalPostcode,
            'postBox' => $this->PostalUnitNumber,
            'expiryDate' => $this->ExpiryDate == '0000-00-00 00:00:00' ? self::DEFAULT_DATE : date(Nocowboys_Tools::DATABASE_DATE_TIME_FORMAT, strtotime($this->ExpiryDate)),
            'dateAdded' => $this->DateAdded == '0000-00-00 00:00:00' ? self::DEFAULT_DATE : $this->DateAdded,
            'salesStatus' => $this->salesStatus,
            'task' => $tasks,
            'countTask' => $countTasks,
            'subscribedProductId' => $this->SubscribedProductId,
            'productPrice' => !empty($currentPackage) ? $currentPackage->PricePerUnit : '',
            'lastPaymentAmount' => !empty($lastPayment) ? $lastPayment->Amount : NULL,
            'assignedTo' => !empty($assignedUser) ? $assignedUser->userId : '',
            'closestTaskDate' => (is_array($tasks) and $tasks[0]['dateTime'] !== '0000-00-00 00:00:00') ? $tasks[0]['dateTime'] : NULL,
//				'tag_suggest' => [
//					'input' => [$this->CompanyName] + explode(' ', $this->CompanyName),
//					'output' => $this->CompanyName,
//					'payload' => [
//						'overallRating' => round($this->TempOverall),
//						'ratingCount' => $ratingCount->ratingCount,
//						'locationArea' => $locationAreaName,
//						'businessId' => $this->ID,
//						'uri' => $this->getURI(),
//						'badges' => $badges,
//						'associations' => $associationsArray
//					]
//				]
        ];

        if ($inputForTagSuggest = $this->_getInputForTagSuggest($this->CompanyName)) {
            $elasticArray['tag_suggest'] = $inputForTagSuggest;
        }

        unset($mostRecentRatingDate);

        // If there's a location, add it
        $location = $this->getMapCoordinates(true);
        if (!is_null($location)) {
            $elasticArray['location'] = $location->Lat . ',' . $location->Lang;
        }
//			if (count($mostRecentRatingsArray) > 0)
//			{
//				var_dump($elasticArray);
//				exit();
//			}
        return $elasticArray;
    }

    protected function _getInputForTagSuggest($companyName = '')
    {
        $companyName = trim(preg_replace('/\s{2,}/', ' ', $companyName));

        if ($companyName) {

            return [
                'input' => [$companyName] + explode(' ', $companyName),
            ];
        }

        return false;
    }

    /**
     * Updates the business to the SOLR search system
     *
     * @return boolean
     */
    public function updateToSolr()
    {

        $businessId = $this->ID;

        if ($this->showInSearchResult()) {
            $elasticArray = $this->buildElasticSearchArray();

            // Save the document
            $businessDocument = new ElasticDocument($businessId, $elasticArray);
            $elasticaIndex = Nocowboys_Elastic::getIndex();
            $businessType = $elasticaIndex->getType(Model_DbTable_Business::elasticTypeName);
            $businessType->addDocument($businessDocument);
            $businessType->getIndex()->refresh();
        }

        $elasticArrayCRM = $this->buildCrmElasticSearchArray();

        // Save the document
        $businessDocumentCRM = new ElasticDocument($businessId, $elasticArrayCRM);
        $elasticaIndexCRM = Nocowboys_Elastic::getCRMIndex();
        $businessTypeCRM = $elasticaIndexCRM->getType(Model_DbTable_Business::elasticTypeName);
        $businessTypeCRM->addDocument($businessDocumentCRM);
        $businessTypeCRM->getIndex()->refresh();



        return true;
    }

    /**
     * Sets the password for this user in the databaes after encrypting it
     * @param string $password Password to set
     * @return boolean
     */
    public function setPassword($password)
    {
        // Can't set an empty password
        if (trim($password) != '') {
            $this->Password = Nocowboys_User_Business::encryptPassword($password);
            return $this->save();
        }
    }

    /**
     * Returns the map coordinates for the business
     * @return array | NULL
     */
    public function getMapCoordinates($includeGeneratedCoordinates = false)
    {
        $markerTable = new Model_DbTable_Marker();
        $select = $markerTable->select()
            ->where('businessId=?', $this->ID);

        if (!$includeGeneratedCoordinates) {
            $select->where('generatedFromAddress = 0');
        }

        $id = $this->getCacheKeyForMarker();
        $results = $markerTable->fetchRowCache($select, NULL, [], false, 8, $id);

        unset($markerTable);
        return $results;
    }

    /**
     * Sometimes businesses don't want their address or location shown,
     *  so we can fall back on the coordinates of the suburb if this is
     *  the case
     * @return Model_DbTableRow_Marker|NULL Array of coordinates or NULL if we can't find
     *  anything
     */
    public function getPublicMapCoordinates()
    {
        $coordinates = NULL;

        // If the user doesn't mind us using their location details
        if (!$this->mapHidden()) {
            $coordinates = $this->getMapCoordinates(true);
        }

        // If there aren't any coordinates, then let's just use their
        //  suburb
        if (is_null($coordinates)) {
            $area = $this->getLocationArea();

            if (!is_null($area)) {
                $coordinates = new stdClass();
                $coordinates->Lat = $area->lat;
                $coordinates->Lang = $area->long;
            }
        }

        return $coordinates;
    }

    /**
     * Checks if the map has been hidden explicitly by the business
     *
     * @return boolean True if the user has explictly hidden their map
     */
    public function mapHidden()
    {
        if (is_null($this->_mapHidden)) {
            $hideMapMeta = $this->getMetaData(Model_DbTableRow_Business::METAKEYHIDEMAP);
            $this->_mapHidden = ((!is_null($hideMapMeta)) and ( $hideMapMeta == 1)) ? true : false;
        }

        return $this->_mapHidden;
    }

    /**
     * Generates map coordintates for a business using their
     *  physical location data (if set)
     *
     * @param boolean $force Set to true to override existing location
     *  data if there are any
     */
    public function generateMapCoordinates($force = false)
    {
        // Get the user-set coordinates (using no parameter)
        $existingCoordinates = $this->getMapCoordinates();

        if ((is_null($existingCoordinates)) or ( $force)) {
            // If the business has a suburb set up, let's try with their
            //  company name and suburb and see where that gets us
            $suburb = $this->getLocationArea();

            if (!is_null($suburb)) {
                $addressToFind = $this->CompanyName . ', ' . $suburb->Name . ', New Zealand';

                if ($this->_findAndSaveMapCoordinates($addressToFind)) {
                    return true;
                }
            }

            // Next, try with their physical address
            $physicalAddress = trim($this->getPhysicalAddress());

            if (!empty($physicalAddress)) {
                $addressToFind = $physicalAddress . ', New Zealand';

                if ($this->_findAndSaveMapCoordinates($addressToFind)) {
                    // Hide the map - assume the user doesn't want
                    //  their exact location shown
                    if ((isset($this->hideAddress)) and ( $this->hideAddress == 1)) {
                        $this->setMetaData(Model_DbTableRow_Business::METAKEYHIDEMAP, 1);
                    }
                    return true;
                }
            }

            // And finally, just their suburb information should do the
            //  trick
            if (!is_null($suburb)) {
                $this->setMapCoordinates($suburb->lat, $suburb->long);
                return true;
            }
        }

        return false;
    }

    /**
     * Finds the coordinates using the default geolocator
     *
     * @param string $addressToFind The address to find
     * @return boolean True if an address was found and saved, false if not
     */
    protected function _findAndSaveMapCoordinates($addressToFind)
    {
        $geoCoder = Nocowboys_Geocode_Abstract::getDefaultGeocoder();

        try {
            $coordinates = $geoCoder->findCoordinatesByAddress($addressToFind);

            if (is_null($coordinates)) {
                return false;
            }

            $lat = $coordinates['lat'];
            $long = $coordinates['long'];

            // Check the coordinates are in NZ
            if (Model_DbTable_Marker::coordinatesInNewZealand($lat, $long)) {
                $this->setMapCoordinates($lat, $long, true);
                return true;
            } else {
                return false;
            }
        } catch (Exception $ex) {
            return false;
            // Do nothing
        }
    }

    /**
     * Sets the given map coordinates for this business
     *
     * @param float $lat Latitude
     * @param float $long Longitude
     */
    public function setMapCoordinates($lat, $long)
    {
        // If there's no user-set coordinates, or we're forcing the
        //  overwrite, get any coordinate
        $existingCoordinates = $this->getMapCoordinates(true);

        if (is_null($existingCoordinates)) {
            $markerTable = new Model_DbTable_Marker();
            $existingCoordinates = $markerTable->createRow();
            $existingCoordinates->BusinessID = $this->ID;
        } else {
            $existingCoordinates = Model_DbTable_Marker::fetchRowByValueStatic('ID', $existingCoordinates->ID);
        }

        $existingCoordinates->Lat = $lat;
        $existingCoordinates->Lang = $long;
        $existingCoordinates->generatedFromAddress = 1;

        $existingCoordinates->save();
        $this->updateLastUpdatedTime();
    }

    /**
     * Gets the logo for the business.  Returns false if
     *  no logo is available, or otherwise returns the image
     *  to display
     * @return string|false
     */
    public function getLogo()
    {
        $location = Zend_Registry::get('config')->images->businessLogo->originalLocation;
        if ((isset($this->LogoFileName)) and ( trim($this->LogoFileName) != '') and ( file_exists($location . '/' . $this->LogoFileName))) {
            return $this->LogoFileName;
        }

        return false;
    }

    /**
     * Gets the images assigned to this business
     * @return Zend_Db_Table_Rowset_Abstract Set of images
     */
    public function getImages()
    {
        if (is_null($this->images)) {
            $imageTable = new Model_DbTable_BusinessPic();
            $this->images = $imageTable->fetchAll(
                $imageTable->select()
                    ->where('BusinessID = ?', $this->ID)
                    ->order('display_order DESC')
            );
        }

        return $this->images;
    }

    /**
     * Gets the competitors for this business
     * @param int $count Number of competitors to get
     * @param boolean $onlyRegistered Set to true to only gets registered competitors
     */
    public function getCompetitors($count = 4, $onlyRegistered = true)
    {
        $businessTable = new Model_DbTable_Business();
        $totalCompetitorArray = array();
        $totalCompetitorIdArray = array();

        $categories = $this->getCategoriesCorrect();
        $categoryIdArray = array();

        foreach ($categories as $category) {
            $categoryIdArray[] = $category->ID;
        }

        // Start by trying to get categories in the direct area
        $businessQuery = $this->getBusinessSearchSelect(array(), $categoryIdArray);
        $businessQuery->where('Business.ID != ?', $this->ID);

        // Order randomly
        $businessQuery->reset(Zend_Db_Select::ORDER);
        $businessQuery->order('RAND()');

        $competitors = $businessTable->fetchAll($businessQuery);

        foreach ($competitors as $competitor) {
            $totalCompetitorArray[] = $competitor;
            $totalCompetitorIdArray[] = $competitor->ID;
        }

        // Check if there is X competitors to show
        if (count($totalCompetitorArray) >= $count) {
            return array_slice($totalCompetitorArray, 0, $count);
        }

        // Start by seeing if there are competitors in the immediate areas
        //  and categories
        $areas = $this->getAreas();
        $areaIdArray = array();

        foreach ($areas as $area) {
            $areaIdArray[] = $area->ID;
        }

        $businessQuery = $this->getBusinessSearchSelect($areaIdArray, $categoryIdArray);
        $businessQuery->where('Business.ID != ?', $this->ID);

        // Don't include competitors previously added
        if (count($totalCompetitorIdArray) > 0) {
            $businessQuery->where('Business.ID NOT IN (' . implode(',', $totalCompetitorIdArray) . ')');
        }

        // Order randomly
        $businessQuery->reset(Zend_Db_Select::ORDER);
        $businessQuery->order('RAND()');

        $competitors = $businessTable->fetchAll($businessQuery);

        foreach ($competitors as $competitor) {
            $totalCompetitorArray[] = $competitor;
            $totalCompetitorIdArray[] = $competitor->ID;
        }

        // Check if there is X competitors to show
        if (count($totalCompetitorArray) >= $count) {
            return array_slice($totalCompetitorArray, 0, $count);
        }

        // If there isn't enough, go through all the child categories and areas
        foreach ($areas as $area) {
            $areaIdArray = array_merge($areaIdArray, $area->getChildAreaIDsForSearch());
        }

        foreach ($categories as $category) {
            $categoryIdArray = array_merge($categoryIdArray, $category->getChildCategoryIDsForSearch());
        }

        $businessQuery = $this->getBusinessSearchSelect($areaIdArray, $categoryIdArray);
        $businessQuery->where('Business.ID != ?', $this->ID);

        // Don't include competitors previously added
        if (count($totalCompetitorIdArray) > 0) {
            $businessQuery->where('Business.ID NOT IN (' . implode(',', $totalCompetitorIdArray) . ')');
        }

        // Order randomly
        $businessQuery->reset(Zend_Db_Select::ORDER);
        $businessQuery->order('RAND()');

        $competitors = $businessTable->fetchAll($businessQuery);

        foreach ($competitors as $competitor) {
            $totalCompetitorArray[] = $competitor;
        }

        return array_slice($totalCompetitorArray, 0, $count);
    }

    /**
     * Determines if the business can receive text messages
     * @return boolean
     */
    public function canReceiveSms()
    {
        $wantsSms = $this->getMetaData('IsSendText');

        if (((!is_null($wantsSms)) and ( $wantsSms == false)) or (!isset($this->Mobile)) or ( trim($this->Mobile) == '')) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Sends a message via. SMS to this business
     * @param string $message Message to send
     * @return int Greater than 0 for success, les than 0 for failure
     * @throws Exception
     */
    public function sendSms($message, $text = '')
    {
        $messageRegex = '/[^' . Nocowboys_Tools::$smsRegexCharacterset . ']/';

        if (!$this->canReceiveSms()) {
            throw new Exception('Trying to send an SMS to a business that has no contact number or doesn\'t want SMS');
        }

        // Remove the profanities
        $message = str_ireplace(Nocowboys_Tools::$profanities, '****', $message);
        $message = preg_replace($messageRegex, '', $message);

        $smsResponse = Nocowboys_Sms::send($this->Mobile, $message);

        if ($smsResponse > 0) {
            // Create a new text message history object
            $SMSTable = new Model_DbTable_Nctext();
            $newMessage = $SMSTable->createRow();

            $newMessage->BusinessID = $this->ID;
            $newMessage->Message = $message;
            $newMessage->DateSent = Nocowboys_Tools::databaseSafeDateTime();
            $newMessage->message_text = $text;
            $newMessage->ip_address = Nocowboys_Tools::getUserIp();
            $newMessage->save();

            // Increase the number of texts sent to this business
            $this->TextCount = $this->TextCount + 1;
            $this->save();
        }

        return $smsResponse;
    }

    /**
     * Returns a Zend select object for selecting businesses with all their
     *  additional details
     * @param array $areaIds Array of area IDs to include
     * @param array $categoryIds Array of category IDs to include
     * @param boolean $includeThisBusinessArea Set to false to not automatically
     *  include the business' area ID in the query
     * @param boolean $onlyRegistered Set to false to include both registered
     *  and non-registered businesses
     * @param integer $count Number of results to request
     * @return Zend_Db_Select A Zend select object representing the query
     */
    public function getBusinessSearchSelect($areaIds, $categoryIds, $includeThisBusinessArea = true, $onlyRegistered = true, $count = 3)
    {
        $businessTable = new Model_DbTable_Business();

        $areaText = implode('" OR  BusinessArea.AreaID = "', $areaIds);
        $categoryText = implode('" OR  BusinessCategory.CategoryID = "', $categoryIds);

        $select = $businessTable->select()
            ->from('Business', array('DISTINCT(Business.ID)', 'AccountType', 'CompanyName', 'OverrideCustomerPreferred',
                'ContactPhone1', 'ContactPhone2', 'EmailAddress', 'Fax', 'LocationAreaID', 'Registered', 'SearchCount',
                'TempNegative', 'TempNeutral', 'TempOverall', 'TempPositive', 'URLName', 'businessCount' => '0'))
            ->setIntegrityCheck(false)
            ->joinInner('BusinessCategory', 'BusinessCategory.BusinessID = Business.ID', array())
            ->joinInner('BusinessArea', 'BusinessArea.BusinessID = Business.ID', array())
            ->where('Business.IsOnline = 1')
            ->where('Business.NoLongerTrade = 0')
            ->where('(BusinessCategory.CategoryID = "' . $categoryText . '")')
            ->where('isHeadOffice = 0')
            ->order('TempOverall DESC')
            ->limit($count);

        if (($includeThisBusinessArea) and (!is_null($this->LocationAreaID)) and ( $this->LocationAreaID > 0)) {
            $select->where('((BusinessArea.AreaID = "' . $areaText . '") OR (Business.LocationAreaID = ' . $this->LocationAreaID . '))');
        } else {
            $select->where('BusinessArea.AreaID = "' . $areaText . '"');
        }

        if ($onlyRegistered) {
            $select->where('Business.Registered = 1');
        }

        return $select;
    }

    public function isRegistered()
    {
        return ($this->Registered == 1) and ( $this->isTrading() );
    }

    public function isTrading()
    {
        return $this->NoLongerTrade != 1;
    }

    public function isHeadOffice()
    {
        return $this->isHeadOffice == 1;
    }

    public function isOnline()
    {
        return $this->IsOnline != 0;
    }

    public function isVerified()
    {
        return $this->Verified == 1;
    }

    /**
     * Checks if business page is hidden for search engines.
     *
     *
     * @return bool
     */
    public function isHideFromSearchEngines()
    {
        return $this->isHideFromSearchEngines == Model_DbTableRow_Business::HIDE_FROM_SEARCH_ENGINES;
    }

    /**
     * Checks if business contacts is hidden.
     *
     *
     * @return bool
     */
    public function isHideBusinessInformation()
    {
        return $this->isHideBusinessInformation == Model_DbTableRow_Business::HIDE_BUSINESS_INFORMATION;
    }

    /**
     * Checks if business rating is available as recent rating.
     *
     *
     * @return bool
     */
    public function availableForRecentRating()
    {
        return $this->available_for_recent_rating == Model_DbTableRow_Business::AVAILABLE_FOR_RECENT_RATING;
    }

    /**
     * Checks if business name is available for list of search results.
     *
     *
     * @return bool
     */
    public function availableForListOfSearchResult()
    {
        return Nocowboys_Elastic::getBusinessFromElastic($this->ID) ? true : false;
    }

    /**
     * Checks if jobs is available.
     *
     *
     * @return bool
     */
    public function isAvailabilityJobs()
    {
        return $this->availability_jobs == Model_DbTableRow_Business::AVAILABILITY_JOBS_ON;
    }

    /**
     * Set Social Profile URL in Meta Table - TR-003
     * @param type $socialKey
     * @param type $SocialValue
     */
    public function setSocialdata($socialKey, $SocialValue)
    {
        if (trim($SocialValue) != '') {
            $this->setMetaData($socialKey, $SocialValue);
        } else {
            $this->deleteMetaData($socialKey);
        }

        $this->updateLastUpdatedTime();
    }

    /**
     * Gets the video key for the business, but only if the business is registered.
     *  At this point, assume the video is a YouTube key, the video string without
     *  the YouTube URL
     * @return string|NULL The YouTube key if it exists, or NULL otherwise
     */
    public function getVideoKey()
    {
        if ($this->isRegistered()) {
            return $this->getMetaData('video');
        }

        return NULL;
    }

    /**
     * Gets the website URL for this business if it exists. Adds a protocol if
     *  none exist
     * @return string|NULL The URL if this business has one, or NULL otherwise
     */
    public function getWebsite()
    {
        if ((!is_null($this->Website)) and ( trim($this->Website) != '')) {
            return Nocowboys_Tools::confirmUrlHasProtocol($this->Website);
        } else {
            return NULL;
        }
    }

    /**
     * Gets the url with the protocol and subdomain removed. Don't use
     *  this as an actual link - it's intended for display
     *
     * @return string Cleaned URL
     */
    public function getWebsiteShortened()
    {
        $website = $this->getWebsite();

        if (is_null($website)) {
            return NULL;
        }

        $website = trim($website, '/');

        // If scheme not included, prepend it
        if (!preg_match('#^http(s)?://#', $website)) {
            $website = 'http://' . $website;
        }

        $urlParts = parse_url($website);

        // Remove www
        return preg_replace('/^www\./', '', $urlParts['host']);
    }

    /**
     * Gets the meta description for use in the meta keyword tag
     * @return sgring Description to use in the meta tag. Aim for 155 characters
     */
    public function getMetaDescription()
    {
        $area = $this->getLocationArea();
        $mostPopularCategory = $this->getPrimaryCategory(true);

        // Use the business description if possible
        $returnString = trim($this->CompanyName);

        if ((!is_null($area)) and (!is_null($mostPopularCategory))) {
            $returnString .= ' are ' . strtolower($mostPopularCategory->Name) . ' in ' . $area->Name . ', New Zealand';
        } else
        if (!is_null($area)) {
            $returnString .= ' are in ' . $area->Name . ', New Zealand';
        } else
        if (!is_null($mostPopularCategory)) {
            $returnString .= ' are ' . strtolower($mostPopularCategory->Name);
        } else {
            $returnString .= ' are on NoCowboys';
        }

        $returnString .= '. ';

        $aboutUs = trim(htmlspecialchars(strip_tags(stripslashes($this->AboutUs))));
        $hasPersonalisedContent = false;

        if ($aboutUs != '') {
            $returnString .= str_replace(array("\r", "\n"), '', $aboutUs);
            $hasPersonalisedContent = true;
        } else {
            $whatWeDo = trim(htmlspecialchars(strip_tags(stripslashes($this->WhatWeDo))));

            if ($whatWeDo != '') {
                $returnString .= '. ' . str_replace(array("\r", "\n"), '', $whatWeDo);
                $hasPersonalisedContent = true;
            }
        }

        if (!$hasPersonalisedContent) {
            $returnString .= 'Read ratings from other kiwis on NoCowboys.co.nz.';
        }

        $tools = new Nocowboys_Tools();
        $returnString = $tools->trimText($returnString, 300, false);

        return trim($returnString);
    }

    /**
     * Gets the title for the business. Use this to improve SEO
     *
     * @return string String to use as the title
     */
    public function getTitle()
    {
        $title = $this->CompanyName;
        $area = $this->getLocationArea();
        $mostPopularCategory = $this->getPrimaryCategory(true);

        if ((!is_null($area)) or (!is_null($mostPopularCategory))) {
            $title .= ' | ';

            if (!is_null($mostPopularCategory)) {
                $title .= $mostPopularCategory->Name . ' ';
            }

            if (!is_null($area)) {
                $title .= $area->Name;
            }
        }

        return trim($title);
    }

    /**
     * Gets suitable copy for an H2 tag
     *
     * @return string H2 tag
     */
    public function getH2()
    {
        $h2Content = '';
        $primaryCategory = $this->getPrimaryCategory(true);
        $area = $this->getLocationArea();

        if ((!is_null($area)) and (!is_null($primaryCategory))) {
            $h2Content = $primaryCategory->Name . ' in ' . $area->Name;
        } else
        if (!is_null($primaryCategory)) {
            $h2Content = $primaryCategory->Name;
        } else
        if (!is_null($area)) {
            $h2Content = $area->Name;
        }

        return $h2Content;
    }

    /**
     * Gets the primary category for this business. If the user hasn't
     *  selected one, then we select the most popular category out of their
     *  set categories
     *
     * @param boolean $getAlternative If no primary category is set by
     *  the business, pick the most popular category from their other
     *  selected categories
     * @return Model_DbTableRow_Category
     */
    public function getPrimaryCategory($getAlternative = false)
    {
        if (!is_null($this->primaryCategoryId)) {
            return Model_DbTable_Category::fetchRowByValueStatic('ID', $this->primaryCategoryId);
        } else {
            if ($getAlternative) {
                return $this->getMostPopularCategory();
            } else {
                return NULL;
            }
        }
    }

    /**
     * Gets the most popular category set for this business
     *
     * @param integer $popularPosition How far down the list of popular
     *  categories we want to go before we return that category. Position 1
     *  is the most popular category. Position 2 is the second and so on.
     *  If there's no popular category for the number given, the most
     *  popular category is returned
     * @return NULL|Model_DbTableRow_Category The most popular category
     *  this business has, or NULL if no categories are set up
     */
    public function getMostPopularCategory($popularPosition = 1)
    {
        $businessCategories = $this->getCategoriesCorrect();
        $mostPopularCategories = [];

        foreach (Model_DbTable_Category::$mostPopularCategoryIds as $popularId) {
            foreach ($businessCategories as $category) {
                if ($popularId == $category->ID) {
                    $mostPopularCategories[$category->ID] = $category;
                }
            }
        }

        $popularCategoryIds = array_keys($mostPopularCategories);

        // If there is enough categories to cover the position we want, 
        //  grab it
        if (count($mostPopularCategories) >= $popularPosition) {
            return $mostPopularCategories[$popularCategoryIds[$popularPosition - 1]];
        } else
        // Otherwise, default to the top category
        if (count($mostPopularCategories) > 0) {
            return $mostPopularCategories[$popularCategoryIds[0]];
        } else {
            return NULL;
        }
    }

    /**
     * Get the area closest to the business
     *
     * @param int $closestAreaPosition
     * @return Model_DbTableRow_Area|NULL
     */
    public function getClosestArea($closestAreaPosition = 1)
    {
        $location = $this->getLocationArea();
        if ($location) {
            $areas = $this->getAreas();
            if ($areas) {
                $distances = array();
                foreach ($areas as $area) {
                    $distance = (
                        (acos(
                            cos($location->lat * pi() / 180) * cos($area->lat * pi() / 180) * cos(($location->long - $area->long) * pi() / 180) + sin($location->lat * pi() / 180) * sin($area->lat * pi() / 180)
                        ) * 180 / pi()
                        ) * 60 * 1.1515
                        ) * 1.609344;
                    $distances[$distance] = $area;
                }

                if (count($distances)) {
                    ksort($distances);
                    $closestAreas = array_values($distances);

                    if ($closestAreaPosition > 0) {
                        $areaPosition = $closestAreaPosition % count($closestAreas);
                        return $closestAreas[$areaPosition];
                    }

                    return $closestAreas[0];
                }
            }
        }

        return $location;
    }

    /**
     * Deletes the metadata for this business if it exists
     * @param string $metaKey The key of the value for this business to delete
     * @return boolean
     */
    public function deleteMetaData($metaKey)
    {
        $existingMetaDataObject = $this->getMetaDataObject();

        if (is_null($existingMetaDataObject)) {
            return true;
        }

        $metaData = json_decode($existingMetaDataObject->metaValue, true);

        if (isset($metaData[$metaKey])) {
            unset($metaData[$metaKey]);

            if (empty($metaData)) {
                $existingMetaDataObject->delete();
            } else {
                $existingMetaDataObject->metaValue = json_encode($metaData);
                $existingMetaDataObject->save();
            }
        }

        return true;
    }

    /**
     * Generates a unique ID used to log the user in automatically if they asked for it
     * @return string
     */
    public function generateSessionId()
    {
        $metaTable = new Model_DbTable_BusinessMeta();

        // Create an ID and check if its being used elsewhere
        $sessionID = $metaTable->createRandomString(20);

        $sessionIdCheck = $metaTable
            ->select()
            ->where('metaKey = "' . Model_DbTable_Business::sessionIdName . '"')
            ->where($metaTable->quoteInto('metaValue = ?', $sessionID));

        // If it is being used, keep looping until we find one that isn't
        while (!is_null($metaTable->fetchRow($sessionIdCheck))) {
            $sessionID = $metaTable->createRandomString(15);

            $sessionIdCheck = $metaTable
                ->select()
                ->where('metaKey = "' . Model_DbTable_Business::sessionIdName . '"')
                ->where($metaTable->quoteInto('metaValue = ?', $sessionID));
        }

        // Now save the ID for the business
        // Encrypt it first though
        $this->setMetaData(Model_DbTable_Business::sessionIdName, Nocowboys_User_Business::encryptPassword($sessionID));

        return $sessionID;
    }

    /**
     * Removes the unique ID for this user if there is one
     * @return string
     */
    public function removeSessionId()
    {
        $this->deleteMetaData(Model_DbTable_Business::sessionIdName);
    }

    /**
     * Checks if the user is close to having their package expire. If so, send
     *  the reminders
     */
    public function sendPackageExpiryReminder()
    {
        $registrationExpiry = $this->getRegistrationExpiryDate();
        $currentPackage = $this->getCurrentPackage();
        $config = Zend_Registry::get('config');

        // If the current package is empty, then default
        if (is_null($currentPackage)) {
            $defaultProductId = $config->website->defaultProductId;
            $packageTable = new Model_DbTable_Product();
            $currentPackage = $packageTable->fetchRowByValue('ID', $defaultProductId);

            if (is_null($currentPackage)) {
                throw new Exception('Default package does not exist');
            }
        }

        // Check if the package has passed, or is near, expiry. This really should
        //  be done in a more OO way, but the additional work and setup isn't worth
        //  it at this point. Any additional product-specific code really should be
        //  put into different product objects and make use of the factory pattern
        //  to load
    }

    /**
     * Gets all the child businesses for this business, assuming its a Head
     *  Office. It also sets up the colours for the businesses and stores them
     *  in the session for future use
     * @return Zend_Db_Table_Rowset_Abstract Businesses that are the child
     *  businesses
     */
    public function getHeadOfficeBusinesses()
    {
        if ($this->isHeadOffice != 1) {
            return NULL;
        }

        if (is_null($this->headOfficeBusinesses)) {
            $headOfficeSession = new Zend_Session_Namespace(Nocowboys_Tools::SESSIONIDHEADOFFICE);
            $headOfficeSession->businessColours = array();

            $businessTable = new Model_DbTable_Business();

            $childBusinessSelect = $businessTable->select()
                ->setIntegrityCheck(false)
                ->from('Business')
                ->joinInner('headOfficeBusiness', 'headOfficeBusiness.childBusinessId = Business.ID', array())
                ->where('headOfficeBusiness.headOfficeBusinessId = ' . $this->ID)
                ->order('CompanyName ASC');

            $this->headOfficeBusinesses = $businessTable->fetchAll($childBusinessSelect);
            $colourCounter = 0;
            $colourCount = count(Nocowboys_Tools::$colours);

            // Loop through the businesses and assign colours
            foreach ($this->headOfficeBusinesses as $business) {
                $headOfficeSession->businessColours[$business->ID] = Nocowboys_Tools::$colours[$colourCounter];

                if ($colourCounter >= $colourCount - 1) {
                    $colourCounter = 0;
                } else {
                    $colourCounter++;
                }
            }
        }

        return $this->headOfficeBusinesses;
    }

    /**
     * Gets all the child business IDs for this business, assuming its a Head
     *  Office
     * @return array Array of the child business IDs
     */
    public function getHeadOfficeBusinessIDs()
    {
        if (is_null($this->headOfficeBusinessIDs)) {
            $childBusinesses = $this->getHeadOfficeBusinesses();
            $businessIds = array();

            foreach ($childBusinesses as $childBusiness) {
                $businessIds[] = $childBusiness->ID;
            }

            $this->headOfficeBusinessIDs = $businessIds;
        }

        return $this->headOfficeBusinessIDs;
    }

    /**
     * Gets any businesses (Head Offices) that have this business as a child
     *  business
     * @return Zend_Db_Table_Rowset_Abstract Head Office business objects
     */
    public function getHeadOffices()
    {
        if (is_null($this->headOffices)) {
            $businessTable = new Model_DbTable_Business();
            $headOfficeBusinessSelect = $businessTable->select()
                ->setIntegrityCheck(false)
                ->from('Business')
                ->joinInner('headOfficeBusiness', 'headOfficeBusiness.headOfficeBusinessId = Business.ID', array())
                ->where('headOfficeBusiness.childBusinessId = ' . $this->ID);

            $this->headOffices = $businessTable->fetchAll($headOfficeBusinessSelect);
        }

        return $this->headOffices;
    }

    /**
     * Sets the businesses assigned to this HO
     * @param array $businessIds Array of IDs for the businesses assigned to
     *  this HO
     * @throws Exception If an array is not passed in
     */
    public function setHeadOfficeBusinesses($businessIds)
    {
        if (!is_array($businessIds)) {
            throw new Exception('Array expected when setting home office businesses');
        }

        $headOfficeTable = new Model_DbTable_HeadOfficeBusiness();
        $existingLinks = $headOfficeTable->fetchAllByValue('headOfficeBusinessId', $this->ID);

        foreach ($existingLinks as $businessToDelete) {
            $businessToDelete->delete();
        }

        foreach ($businessIds as $newBusinessId) {
            $newLink = $headOfficeTable->createRow();
            $newLink->headOfficeBusinessId = $this->ID;
            $newLink->childBusinessId = $newBusinessId;
            $newLink->save();
        }
    }

    /**
     * Sets the categories for this business using the given array of category
     *  IDs
     * @param array $categoryIds Array of category IDs to set for this business
     * @throws Exception If an array is not passed in
     */
    public function setCategoryIds($categoryIds)
    {
        if (!is_array($categoryIds)) {
            throw new Exception('Array expected when setting business categories');
        }

        // Load the categories that exist and then delete them
        $businessCategoryTable = new Model_DbTable_BusinessCategory();
        $allLinks = $businessCategoryTable->fetchAll('BusinessID = ' . $this->ID);

        foreach ($allLinks as $linkToDelete) {
            $linkToDelete->delete();
        }

        // Add the new categories
        foreach ($categoryIds as $categoryID) {
            $newLink = $businessCategoryTable->createRow();
            $newLink->BusinessID = $this->ID;
            $newLink->CategoryID = $categoryID;
            $newLink->save();
        }

        // Delete any cache relating to the categories
        Nocowboys_Db_Table_Cache::deleteCacheKey(self::CACHE_KEY_PREFIX_CATEGORIES . $this->ID);

        $this->denormaliseCategoryIds();
        $this->save(true, true);

        return true;
    }

    /**
     * Goes and gets the category IDs for this business and sets them in
     *  the DB. It gets the parent category IDs for those categories too
     *  so we don't need to do additional queries
     *
     */
    public function denormaliseCategoryIds()
    {
        $businessCategories = $this->getCategoriesCorrect();
        $allCategoryIds = [];

        $primaryCategory = $this->getPrimaryCategory(true);
        if (!is_null($primaryCategory)) {
            $allCategoryIds[] = $primaryCategory->ID;
        }

        foreach ($businessCategories as $category) {
            if (!in_array($category->ID, $allCategoryIds)) {
                $allCategoryIds[] = $category->ID;
            }

            $allChildCategories = explode(',', $category->parentCategoryIds);
            $allCategoryIds += $allChildCategories;
        }

        $this->categoryIds = implode(',', $allCategoryIds);
    }

    /**
     * Sets the associations for this business using the given array of association
     *  IDs
     * @param array $associationIds Array of association IDs to set for this business
     * @throws Exception If an array is not passed in
     */
    public function setAssociationIds($associationIds)
    {
        if (!is_array($associationIds)) {
            throw new Exception('Array expected when setting business categories');
        }

        // Load the associations that exist and then delete them
        $associationTable = new Model_DbTable_BusinessAssociation();
        $allLinks = $associationTable->fetchAll('businessId = ' . $this->ID);

        foreach ($allLinks as $linkToDelete) {
            $linkToDelete->delete();
        }

        // Add the new associations
        foreach ($associationIds as $associationId) {
            $newLink = $associationTable->createRow();
            $newLink->businessId = $this->ID;
            $newLink->associationId = $associationId;
            $newLink->save();
        }

        $this->save(true, true);

        return true;
    }

    /**
     * Gets the associations for this business
     * @return Zend_Db_Table_Rowset_Abstract Associations for this business
     */
    public function getAssociations()
    {
        $associationTable = new Model_DbTable_Association();
        $associationSelect = $associationTable->select()
            ->from('association')
            ->setIntegrityCheck(false)
            ->joinInner('businessAssociation', 'association.ID = businessAssociation.associationId', array())
            ->where('businessAssociation.businessId = ?', $this->ID);

        $result = $associationTable->fetchAll($associationSelect);

        unset($associationTable);
        return $result;
    }

    /**
     * Sets the areas for this business using the given array of area
     *  IDs
     * @param array $areaIds Array of area IDs to set for this business
     * @throws Exception If an array is not passed in
     */
    public function setAreaIds($areaIds)
    {
        if (!is_array($areaIds)) {
            throw new Exception('Array expected when setting business areas');
        }

        // Load the areas that exist and then delete them
        $businessAreaTable = new Model_DbTable_BusinessArea();
        $allLinks = $businessAreaTable->fetchAll('BusinessID = ' . $this->ID);

        $areaTable = new Model_DbTable_Area();
        $areaLinkTable = new Model_DbTable_AreaLink();

        $areaTableSelect = $areaTable->select()
            ->setIntegrityCheck(false)
            ->from(['a' => $areaTable->getTableName()], ['ID', 'Name'])
            ->joinLeft(['al' => $areaLinkTable->getTableName()], 'al.ChildAreaID = a.ID', ['ParentAreaID'])
            ->joinLeft(
                ['alp' => $areaTable->getTableName()],
                'alp.ID = al.ParentAreaID',
                ['Name AS ParentAreaName']
            )
            ->where('al.ParentAreaID IN (?)',
                new Zend_Db_Expr('SELECT ID FROM nocowboys.Area WHERE IsParentArea = 1')
            )
            ->orWhere('a.IsParentArea = ?', 1)
            ->group('a.ID')
            ->order('ParentAreaName ASC')
            ->order('a.Name ASC');
        $majorAreas = $areaTable->fetchAll($areaTableSelect);

        $majorAreasIds = [];
        foreach ($majorAreas as $majorArea) {
            $majorAreasIds[] = $majorArea->ID;
        }

        foreach ($allLinks as $linkToDelete) {
            if (!in_array($linkToDelete->AreaID, $majorAreasIds)) {
                $linkToDelete->delete();
            }
        }
        // Add the new categories
        foreach ($areaIds as $areaID) {
            $link = $businessAreaTable->fetchAll('BusinessID = ' . $this->ID . ' AND AreaID = ' . $areaID);

            if (!$link->count()) {
                $newLink = $businessAreaTable->createRow();
                $newLink->BusinessID = $this->ID;
                $newLink->AreaID = $areaID;
                $newLink->save();
            }
        }

        // Delete any cache relating to the categories
        Nocowboys_Db_Table_Cache::deleteCacheKey(self::CACHE_KEY_PREFIX_AREAS . $this->ID);

        $this->save(true, true);

        return true;
    }

    /**
     * Sends an email to the given user using the pre-populated email content
     *  the business has set up
     * @param string $recipientName Name of the recipient
     * @param string $recipientEmailAddress Email address of the recipient
     * @param $ratingValue Value of the rating (between 0 and 100)
     * @param boolean $testEmail Set to true to ignore the active check
     */
    public function sendAutomatedRatingEmail($recipientName, $recipientEmailAddress, $ratingValue, $testEmail = false)
    {
        // Make sure we have all the settings we need
        $emailContent = $this->getMetaData(self::METAKEYAUTOMATEDEMAILSCONTENT);
        $emailSubject = $this->getMetaData(self::METAKEYAUTOMATEDEMAILSSUBJECT);
        $canSend = $this->getMetaData(self::METAKEYAUTOMATEDEMAILSACTIVE);
        $ratingCutoff = $this->getMetaData(self::METAKEYAUTOMATEDEMAILSCUTOFF);

        // Current business gets priority
        if ((($canSend == 1) and ( $ratingValue >= $ratingCutoff)) or ( $testEmail)) {
            if ((is_null($emailContent)) or ( is_null($emailSubject))) {
                throw new Exception('No content or subject set up for the business automated email');
            }

            $emailContent = str_replace('##email##', $recipientEmailAddress, $emailContent);
            $emailContent = str_replace('##name##', $recipientName, $emailContent);

            return Nocowboys_Email::sendEmail($recipientEmailAddress, $emailSubject, 'businesses/automatedRating', array('content' => $emailContent));
        }
        // And backup is for the head office, if there is one
        else {
            $headOffices = $this->getHeadOffices();

            foreach ($headOffices as $headOffice) {
                return $headOffice->sendAutomatedRatingEmail($recipientName, $recipientEmailAddress, $ratingValue, $testEmail);
            }
        }

        return false;
    }

    /**
     * Sends an email to the business alerting them of a new rating
     * @param Model_DbTableRow_Rating $rating Rating object to send the email for
     * @param $receivingBusinessId The ID of the business that ACTUALLY received
     *  the rating
     */
    public function sendRatingNotificationEmail(Model_DbTableRow_Rating $rating, $receivingBusinessId = NULL)
    {
        $ratingValue = $rating->calculateRating();
        $raterName = $rating->RaterFirstName;

        if ($this->RatingNotification == 1) {
            // Check if there's a rating limit set
            $notificationLevel = $this->getMetaData(self::METAKEYRATINGNOTIFICATIONLEVEL);
            $send = false;

            // Send all ratings
            if (is_null($notificationLevel) or ( $notificationLevel == 1)) {
                $send = true;
            } else {
                // Check to make sure the rating is below the threshold
                $negativeCutoff = $this->getMetaData(self::METAKEYNEGATIVERATINGCUTOFF);

                if ($ratingValue <= $negativeCutoff) {
                    $send = true;
                }
            }

            // If we should send the email, do so
            if ($send) {
                $config = Zend_Registry::get('config');

                if (!is_null($receivingBusinessId)) {
                    $businessTable = new Model_DbTable_Business();
                    $receivingBusiness = $businessTable->fetchRowByValue('ID', $receivingBusinessId);

                    $emailParameters = array();
                    $emailParameters['businessName'] = $receivingBusiness->CompanyName;
                    $emailParameters['ratingValue'] = $ratingValue;
                    $emailParameters['raterName'] = $raterName;
                    $emailParameters['tradesPerson'] = $rating->TradesmanName;
                    $emailParameters['comment'] = $rating->Comment;
                    $emailParameters['unsubscribeLink'] = TEMP_DOMAIN . $this->ID . '/rating-opt-out';
                    $emailParameters['viewBusinessLink'] = TEMP_DOMAIN . $receivingBusiness->getURI(false) . '#ratings';

                    $this->sendEmail('New rating for one of your businesses',
                        'businesses/newRatingNotificationHeadOffice', $emailParameters, $layout = 'email',
                        $overrideFromAddress = NULL, $overrideFromName = NULL
                    );
                } else {
                    $emailParameters = array();
                    $emailParameters['businessName'] = $this->CompanyName;
                    $emailParameters['ratingValue'] = $ratingValue;
                    $emailParameters['raterName'] = $raterName;
                    $emailParameters['unsubscribeLink'] = TEMP_DOMAIN . $this->ID . '/rating-opt-out';
                    $emailParameters['viewBusinessLink'] = TEMP_DOMAIN . $this->getURI(false) . '#ratings';
                    $emailParameters['nocowboysSiteLink'] = $config->domain;

                    $this->sendEmail('You have received a new review', 'businesses/newRatingNotification',
                        $emailParameters, $layout = 'email',
                        $overrideFromAddress = NULL, $overrideFromName = NULL
                    );
                }
            }
        }

        // Now do the same for all the head offices, if there are any
        $headOffices = $this->getHeadOffices();

        foreach ($headOffices as $headOffice) {
            $headOffice->sendRatingNotificationEmail($rating, $this->ID);
        }
    }

    /**
     * Returns the URI for this business' dashboard
     * @return string URI for the current business' dashboard
     */
    public function getDashboardUri()
    {
        if ($this->isHeadOffice == 1) {
            $homeUrl = '/business/head-office';
        } else {
            $homeUrl = '/business';
        }

        return $homeUrl;
    }

    /**
     * Gets the rating values for each month
     * @return Zend_Db_Table_Rowset_Abstract A collection of months with the
     *  associated number of ratings and their score
     */
    public function getMonthlyRatings()
    {
        $ratingTable = new Model_DbTable_Rating();
        $ratingSelect = $ratingTable->select()
            ->from('Rating', array('monthlyTotal' => '(SUM(`RankCommunication`+`RankValue`+`RankReliability`+`RankQuality`)/4)',
                'monthDate' => 'CONCAT_WS("-", DATE_FORMAT(`RankDate`, "%m"), DATE_FORMAT(`RankDate`, "%Y"))',
                'ratingCount' => 'COUNT("ID")',
                'RankDate'))
            ->where('Rating.Locked <> 1')
            ->where('Rating.Hidden = 0')
            ->where('Rating.isSpam = 0')
            ->having('ratingCount > 0')
            ->group('monthDate')
            ->order('RankDate DESC');

        if ($this->isHeadOffice == 1) {
            $ratingSelect->where('BusinessID IN (' . implode(',', $this->getHeadOfficeBusinessIDs()) . ')');
        } else {
            $ratingSelect->where('BusinessID = ' . $this->ID);
        }

        return $ratingTable->fetchAll($ratingSelect);
    }

    /**
     * Gets the jobs this business is interested in for all the available
     *  months
     * @return Zend_Db_Table_Rowset_Abstract A collection of months with the
     *  associated number of jobs the business has shown interest in
     */
    public function getMonthlyJobInterests()
    {
        $interestTable = new Model_DbTable_BusinessInterestedInJob();
        $interestSelect = $interestTable->select()
            ->from('BusinessInterestedinJob', array('jobCount' => 'COUNT(BusinessInterestedinJob.ID)',
                'monthDate' => 'CONCAT_WS("-", DATE_FORMAT(DateAdded, "%m"), DATE_FORMAT(DateAdded, "%Y"))'))
            //->where('BusinessID = '.$this->ID)
            ->where('IsQuoteSent = 1 OR IsQuestionAsked = 1')
            ->having('jobCount > 0')
            ->group('monthDate')
            ->order('DateAdded DESC');

        if ($this->isHeadOffice == 1) {
            $interestSelect->where('BusinessID IN (' . implode(',', $this->getHeadOfficeBusinessIDs()) . ')');
        } else {
            $interestSelect->where('BusinessID = ' . $this->ID);
        }

        return $interestTable->fetchAll($interestSelect);
    }

    /**
     * Gets the positive ratings for all the businesses this head office manages
     * @return integer
     */
    public function headOfficeGetPositiveRatings()
    {
        $businessIds = $this->getHeadOfficeBusinessIDs();
        $cutoff = $this->getMetaData(self::METAKEYPOSITIVERATINGCUTOFF);

        if (is_null($cutoff)) {
            $cutoff = Nocowboys_Tools::$ratingPositiveCutoff;
        }

        $ratingTable = new Model_DbTable_Rating();
        $ratingSelect = $ratingTable->select()
            ->setIntegrityCheck(false)
            ->from('Rating')
            ->where('Rating.BusinessID IN (' . implode(',', $businessIds) . ')')
            ->where('((`RankCommunication`+`RankValue`+`RankReliability`+`RankQuality`)/4) >= ' . $cutoff)
            ->where('Rating.Locked <> 1')
            ->where('Rating.Hidden = 0')
            ->where('Rating.isSpam = 0')
            ->where('Rating.NotUsedBefore = 0');

        return $ratingTable->fetchAll($ratingSelect);
    }

    /**
     * Gets the negative ratings for all the businesses this head office manages
     * @return integer
     */
    public function headOfficeGetNegativeRatings()
    {
        $businessIds = $this->getHeadOfficeBusinessIDs();
        $cutoff = $this->getMetaData(self::METAKEYNEGATIVERATINGCUTOFF);

        if (is_null($cutoff)) {
            $cutoff = Nocowboys_Tools::$ratingNegativeCutoff;
        }

        $ratingTable = new Model_DbTable_Rating();
        $ratingSelect = $ratingTable->select()
            ->setIntegrityCheck(false)
            ->from('Rating')
            ->where('Rating.BusinessID IN (' . implode(',', $businessIds) . ')')
            ->where('((`RankCommunication`+`RankValue`+`RankReliability`+`RankQuality`)/4) <= ' . $cutoff)
            ->order('RankDate DESC')
            ->where('Rating.Locked <> 1')
            ->where('Rating.Hidden = 0')
            ->where('Rating.isSpam = 0')
            ->where('Rating.NotUsedBefore = 0');

        return $ratingTable->fetchAll($ratingSelect);
    }

    /**
     * Gets the average of ratings for all the Home Office businesses
     * @return float
     */
    public function headOfficeGetRatingAverage()
    {
        $businessIds = $this->getHeadOfficeBusinessIDs();

        $ratingTable = new Model_DbTable_Rating();
        $ratingSelect = $ratingTable->select()
            ->from('Rating', array('ratingAverage' => '(SUM(`RankCommunication`+`RankValue`+`RankReliability`+`RankQuality`)/4) / COUNT("ID")'))
            ->where('Rating.BusinessID IN (' . implode(',', $businessIds) . ')')
            ->where('Rating.Locked <> 1')
            ->where('Rating.Hidden = 0')
            ->where('Rating.isSpam = 0')
            ->where('Rating.NotUsedBefore = 0');

        $result = $ratingTable->fetchRow($ratingSelect);

        return $result->ratingAverage;
    }

    /**
     * Gets the number of ratings left in the past month
     * @return integer
     */
    public function getPastMonthsRatings($numberOfMonths = 1)
    {
        $ratingTable = new Model_DbTable_Rating();
        $ratingSelect = $ratingTable->select()
            ->from('Rating')
            ->where('Rating.RankDate >= "' . Nocowboys_Tools::databaseSafeDateTime(strtotime('-' . $numberOfMonths . ' months')) . '"')
            ->where('Rating.Locked <> 1')
            ->where('Rating.Hidden = 0')
            ->where('Rating.isSpam = 0')
            ->where('Rating.NotUsedBefore = 0');

        if ($this->isHeadOffice == 1) {
            $ratingSelect->where('Rating.BusinessID IN (' . implode(',', $this->getHeadOfficeBusinessIDs()) . ')');
        } else {
            $ratingSelect->where('BusinessID = ' . $this->ID);
        }

        return $ratingTable->fetchAll($ratingSelect);
    }

    /**
     * Gets all the ratings for this head office
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function headOfficeGetAllRatings()
    {
        if (is_null($this->headOfficeRatings)) {
            $businessIds = $this->getHeadOfficeBusinessIDs();

            $ratingTable = new Model_DbTable_Rating();
            $ratingSelect = $ratingTable->select()
                ->setIntegrityCheck(false)
                ->from('Rating')
                ->joinInner('Business', 'Rating.BusinessID = Business.ID', array('businessName' => 'CompanyName'))
                ->where('Rating.BusinessID IN (' . implode(',', $businessIds) . ')')
                ->where('Rating.Hidden = 0')
                //->where('Rating.Locked = 0')// OR Rating.RankDate <= "'.Nocowboys_Tools::databaseSafeDateTime(Model_DbTable_Rating::getUnauthenticatedRatingCutoff()).'"')
                ->where('Rating.isSpam = 0')
                ->order('Rating.RankDate DESC');

            $this->headOfficeRatings = $ratingTable->fetchAll($ratingSelect);
        }

        return $this->headOfficeRatings;
    }

    /**
     * Gets the jobs this business has shown interest in
     * @return Zend_Db_Table_Rowset_Abstract Rowet of jobs the business has
     *  shown interest in
     */
    public function headOfficeGetJobsInterestedIn()
    {
        if (is_null($this->headOfficeJobsInterestedIn)) {
            $businessIds = $this->getHeadOfficeBusinessIDs();

            $jobTable = new Model_DbTable_Job();
            $jobSelect = $jobTable->select()
                ->setIntegrityCheck(false)
                ->from('Job')
                ->joinInner('BusinessInterestedinJob', 'BusinessInterestedinJob.JobID = Job.ID', array('quoteDate' => 'DateAdded'))
                ->where('BusinessId IN (' . implode(',', $businessIds) . ')')
                ->where('IsQuoteSent = 1 OR IsQuestionAsked = 1')
                ->order('DateAdded DESC');

            $this->headOfficeJobsInterestedIn = $jobTable->fetchAllCache($jobSelect);
        }

        return $this->headOfficeJobsInterestedIn;
    }

    /**
     * Get the relavent jobs for this HO
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function headOfficeGetRelevantJobs()
    {
        $childBusinesses = $this->getHeadOfficeBusinesses();
        $businessIds = array();
        $allAreas = array();
        $allCategories = array();

        // Get the categories and areas for all businesses
        foreach ($childBusinesses as $childBusiness) {
            $businessAreas = $childBusiness->getAreas();
            foreach ($businessAreas as $area) {
                $allAreas[] = $area->ID;
            }

            $businessCategories = $childBusiness->getCategories();
            foreach ($businessCategories as $category) {
                $allCategories[] = $category->ID;
            }

            $businessIds[] = $childBusiness->ID;
        }

        $allCategories = array_unique($allCategories);
        $allAreas = array_unique($allAreas);

        // If there's no categories or areas, then we can't find suggested jobs
        if ((count($allCategories) == 0) or ( count($allAreas) == 0)) {
            return array();
        }

        $jobObject = new Model_DbTable_Job();

        $select = $jobObject->select()
            ->distinct()
            ->setIntegrityCheck(false)
            ->from('Job')
            ->where('Job.ParentCategoryID IN (' . implode(',', $allCategories) . ') OR Job.SubCategoryID IN (' . implode(',', $allCategories) . ') OR Job.SubSubCategoryID IN (' . implode(',', $allCategories) . ')')
            ->where('Job.RegionID IN (' . implode(',', $allAreas) . ') OR Job.AreaID IN (' . implode(',', $allAreas) . ') OR Job.SuburbAreaID IN (' . implode(',', $allAreas) . ')')
            ->where('Job.IsOnline = 1')
            ->order(array('Job.DateAdded DESC'));

        return $jobObject->fetchAll($select);
    }

    /**
     * Gets the positive ratings for this business
     * @return array Array of ratings
     */
    public function getPositiveRatings($positiveRatingCutoff = NULL)
    {
        // See if this business has a personal cutoff
        $positiveRatingCutoff = $this->getPositiveRatingCutoff($positiveRatingCutoff);

        $ratings = $this->getAuthenticatedRatings();
        $results = array();

        foreach ($ratings as $rating) {
            if ($rating->calculateRating() >= $positiveRatingCutoff) {
                $results[] = $rating;
            }
        }

        return $results;
    }

    /**
     * Gets the negative ratings for this business
     * @return array Array of ratings
     */
    public function getNegativeRatings($negativeRatingCutoff = NULL)
    {
        $negativeRatingCutoff = $this->getNegativeRatingCutoff($negativeRatingCutoff);

        $ratings = $this->getAuthenticatedRatings();
        $results = array();

        foreach ($ratings as $rating) {
            if ($rating->calculateRating() <= $negativeRatingCutoff) {
                $results[] = $rating;
            }
        }

        return $results;
    }

    /**
     * Gets the negative rating cutoff for any ratings, based on the business'
     *  peferences (if they have any). Defaults to the overall system cutoff
     * @param integer $negativeRatingCutoff Override the negative value by
     *  passing in a value
     * @return integer Percentge below which ratings are
     *  considered negative
     */
    public function getNegativeRatingCutoff($overrideCutoff = NULL)
    {
        // See if this business cutoff is getting overridden
        if (is_null($overrideCutoff)) {
            $negativeRatingCutoff = $this->getMetaData(self::METAKEYNEGATIVERATINGCUTOFF);

            // Default to the overall website cutoff
            if (is_null($negativeRatingCutoff)) {
                $negativeRatingCutoff = Nocowboys_Tools::$ratingNegativeCutoff;
            }
        } else {
            $negativeRatingCutoff = $overrideCutoff;
        }

        return $negativeRatingCutoff;
    }

    /**
     * Gets the positive rating cutoff for any ratings, based on the business'
     *  peferences (if they have any). Defaults to the overall system cutoff
     * @param integer $positiveRatingCutoff Override the negative value by
     *  passing in a value
     * @return integer Percentge above which ratings are
     *  considered positive
     */
    public function getPositiveRatingCutoff($overrideCutoff = NULL)
    {
        // See if this business cutoff is getting overridden
        if (is_null($overrideCutoff)) {
            $positiveRatingCutoff = $this->getMetaData(self::METAKEYPOSITIVERATINGCUTOFF);

            // Default to the overall website cutoff
            if (is_null($positiveRatingCutoff)) {
                $positiveRatingCutoff = Nocowboys_Tools::$ratingPositiveCutoff;
            }
        } else {
            $positiveRatingCutoff = $overrideCutoff;
        }

        return $positiveRatingCutoff;
    }

    /**
     * Checks if the given business ID is allowed access by this head office
     * @param integer $businessId ID of the business the current business wants
     *  access to
     * @return boolean True if this business is allowed access to the business
     *  with the given ID
     */
    public function accessToBusiness($businessId)
    {
        if ($businessId == $this->ID) {
            return true;
        }

        $childBusinesses = $this->getHeadOfficeBusinesses();
        $ratingBusinessFound = false;

        foreach ($childBusinesses as $childBusiness) {
            if ($childBusiness->ID == $businessId) {
                $ratingBusinessFound = true;
                break;
            }
        }

        return $ratingBusinessFound;
    }

    /**
     * Increases the viewcount for this business
     */
    public function profilePageViewed()
    {
        // Add to the history of viewed businesses
        $userSession = new Zend_Session_Namespace('userBehavior');

        if (!is_array($userSession->previouslyViewedBusinesses)) {
            $userSession->previouslyViewedBusinesses = array();
        }

        if (isset($userSession->previouslyViewedBusinesses[$this->ID])) {
            unset($userSession->previouslyViewedBusinesses[$this->ID]);
        }

        if (count($userSession->previouslyViewedBusinesses) >= 5) {
            $userSession->previouslyViewedBusinesses = array_slice($userSession->previouslyViewedBusinesses, 0, 4, true);
        }

        $userSession->previouslyViewedBusinesses = array($this->ID => array('name' => $this->CompanyName, 'uri' => $this->getURI())) + $userSession->previouslyViewedBusinesses;

        $this->ViewCount++;
        $this->save();
    }

    /**
     * Increases the times found in a search for this business
     * Where this method is called the object isn't a complete business object
     *  so we can't modify it. Load it again and increase the number of views
     */
    public function appearedInSearch()
    {
        $businessTable = new Model_DbTable_Business();
        $business = $businessTable->fetchRowByValue('ID', $this->ID);
        $business->SearchCount++;
        $business->save();
    }

    /**
     * Sets the rating classification settings for this business
     * @param array $classificationSettings
     */
    public function setRatingClassificationSettingsFromArray($classificationSettings)
    {
        $positiveRatingCutoff = $classificationSettings['positiveRatingCutoff'] ?: + 1;
        $negativeRatingCutoff = $positiveRatingCutoff - 1;

        $this->setMetaData(Model_DbTableRow_Business::METAKEYPOSITIVERATINGCUTOFF, $positiveRatingCutoff);
        $this->setMetaData(Model_DbTableRow_Business::METAKEYNEGATIVERATINGCUTOFF, $negativeRatingCutoff);
    }

    /**
     * Sets the notification settings for this business
     * @param array $notificationSettings
     */
    public function setNotificationSettingsFromArray($notificationSettings)
    {
        if ($notificationSettings['notificationChoice'] == 0) {
            $this->RatingNotification = 0;
        } else {
            $this->RatingNotification = 1;
        }

        $this->setMetaData(Model_DbTableRow_Business::METAKEYRATINGNOTIFICATIONLEVEL, $notificationSettings['notificationChoice']);
    }

    /**
     * Sets the meta information for automated emails
     * @param type $automatedEmailSettings
     * @param type $testing Set to true to set up the variables for a test email
     */
    public function setAutomatedEmailSettingsFromArray($automatedEmailSettings, $testing = false)
    {
        $this->setMetaData(Model_DbTableRow_Business::METAKEYAUTOMATEDEMAILSCONTENT, $automatedEmailSettings['automatedEmailContent']);

        $this->setMetaData(Model_DbTableRow_Business::METAKEYAUTOMATEDEMAILSSUBJECT, $automatedEmailSettings['automatedEmailSubject']);

        if ($testing) {
            $this->setMetaData(Model_DbTableRow_Business::METAKEYAUTOMATEDEMAILSACTIVE, 1);
        } else {
            $this->setMetaData(Model_DbTableRow_Business::METAKEYAUTOMATEDEMAILSACTIVE, $automatedEmailSettings['sendAutomatedEmails']);
            $this->setMetaData(Model_DbTableRow_Business::METAKEYAUTOMATEDEMAILSCUTOFF, $automatedEmailSettings['emailCutoff']);
        }
    }

    /**
     * Sets the logo for this business
     * @param Zend_Form_Element_File $image Image to upload
     */
    public function setLogo($existingFilename, $rotateValue)
    {
        if (!file_exists($existingFilename)) {
            throw new Exception('Uploaded file does not exist');
        }

        $this->moveUploadedImage($existingFilename);

        if ($rotateValue <> 0) {
            $this->rotateImg($rotateValue);
        }
    }

    /**
     * Returns name of image file
     * @return string file name to use
     */
    protected function _getExistingImage()
    {
        return Zend_Registry::get('config')->images->businessLogo->originalLocation . '/' . $this->LogoFileName;
    }

    /**
     * Moves an uploaded image to its final resting place, gives it a name
     *  and scales it if it's too big
     * @param string $existingImage The location of the existing image to move
     */
    public function moveUploadedImage($existingImage)
    {
        if (!file_exists($existingImage)) {
            throw new Exception('Uploaded file does not exist');
        }

        // Get a suggested name for the image
        $imageConfig = Zend_Registry::get('config')->images->businessLogo;
        $newFileName = $this->_suggestLogoFileName();
        $newFullFileName = $imageConfig->originalLocation . '/' . $newFileName;

        // Scale the image down
        $options = array(
            'resolution-units' => Imagine\Image\ImageInterface::RESOLUTION_PIXELSPERINCH,
            'resolution-x' => 72,
            'resolution-y' => 72,
            'quality' => 100
        );

        $imagineInstance = new Imagine\Gd\Imagine();
        $result = $imagineInstance->open($existingImage)
            ->save($newFullFileName, $options);

        //remove old business logo
        if ($result and $imageConfig->originalLocation . '/' . $this->LogoFileName != $existingImage) {
            Model_DbTableRow_BusinessPic::deleteFile($imageConfig->originalLocation . '/' . $this->LogoFileName);
        }

        $this->LogoFileName = $newFileName;
        $this->save(true);

        // Remove the old image now we've saved our new one
        Model_DbTableRow_BusinessPic::deleteFile($existingImage);

        return $result;
    }

    /**
     * Suggests a logo filename
     * @return string Suggested logo filename
     */
    private function _suggestLogoFileName()
    {
        $suggestedName = $this->CompanyName . ' logo';
        $this->logoVersion++;

        if ($this->logoVersion > 1) {
            $suggestedName .= ' v' . $this->logoVersion;
        }

        return Nocowboys_Db_Table_Abstract::cleanUrl($suggestedName) . '.jpg';
    }

    /**
     * Deletes the logo if it exists
     */
    public function deleteLogo()
    {
        $imageConfig = Zend_Registry::get('config')->images->businessLogo;

        // Delete the existing logo if there is one
        $existingLogo = $imageConfig->originalLocation . '/' . $this->LogoFileName;

        if ((isset($existingLogo)) and ( is_file($existingLogo))) {
            Model_DbTableRow_BusinessPic::deleteFile($existingLogo);
        }

        $this->LogoFileName = NULL;
        $this->save(true);
    }

    /**
     * Overrides the save method so we can update the last date changed value
     * @param boolean $updateLastUpdated Set to true to update the last-updated
     *  time
     * @param boolean $updateSolr Set to true to update SOLR. We don't
     *  need to do this every time the object is saved, but we need to keep
     *  the index up-to-date
     * @param advanced params. Such as save business changing in history
     * @return mixed
     */
    public function save($updateLastUpdated = false, $updateSolr = false, $params = ['history' => false])
    {

        $action = $this->_checkBusinessAction();

        if ($updateLastUpdated) {
            $this->updateLastUpdatedTime(false);
        }

        $businessID = parent::save();

        if ($updateSolr) {
            $this->updateToSolr();
        }

        if (!empty($params['history'])) {
            $this->_saveBusinessHistory($action);
        }

        return $businessID;
    }

    protected function _setHistoryUserInfo(&$historyBusiness)
    {

        if (Nocowboys_User_Administrator::loggedIn()) {
            $userInfo = Nocowboys_User_Administrator::getUserObject();
            $historyBusiness->user_type = Model_DbTable_BusinessHistory::USER_TYPE_ADMIN;
            $historyBusiness->user_id = $userInfo->id;
        } elseif (Nocowboys_User_Business::loggedIn()) {
            $userInfo = Nocowboys_User_Business::getUserObject();
            $historyBusiness->user_type = Model_DbTable_BusinessHistory::USER_TYPE_BUSINESS;
            $historyBusiness->user_id = $userInfo->ID;
        }

        return $historyBusiness;
    }

    protected function _checkBusinessAction()
    {
        if (!empty($this->ID)) {
            $action = Model_DbTable_BusinessHistory::$action['edit'];
        } else {
            $action = Model_DbTable_BusinessHistory::$action['add'];
        }

        return $action;
    }

    protected function _saveBusinessHistory($action)
    {
        $businessHistory = new Model_DbTable_BusinessHistory();
        $newBusinessHistory = $businessHistory->createRow();
        $newBusinessHistory->action = $action;
        $newBusinessHistory->business_id = $this->ID;
        $newBusinessHistory->datetime = date(Model_DbTable_BusinessHistory::DATE_TIME_FORMAT_MYSQL);
        $newBusinessHistory->data = json_encode($this->toArray());
        $this->_setHistoryUserInfo($newBusinessHistory);

        return $newBusinessHistory->save();
    }

    public function delete()
    {
        $this->_saveBusinessHistory(Model_DbTable_BusinessHistory::$action['delete']);

        $this->removeSalesEmailMailchimpBusinessesRegistered();

        $mc = new Nocowboys_MailChimp();
        $mc->removeMemberFromList(Nocowboys_MailChimp::getListIdBusinesses(), $this->EmailAddress);
        $mc->removeMemberFromList(Nocowboys_MailChimp::getListIdBusinessesRegistered(), $this->EmailAddress);

        return parent::delete();
    }

    /**
     * Updates the last-updated time
     * @param boolean $saveAfterwards Set to false to not save the business
     *  object after updating the last-updated time
     */
    public function updateLastUpdatedTime($saveAfterwards = true)
    {
        $this->lastUpdated = Nocowboys_Tools::databaseSafeDateTime();

        if ($saveAfterwards) {
            $this->save();
        }
    }

    /**
     * Fetches all the correspondence for this business
     * @param integer $limit Limits the number of items to return
     * @param array|NULL $excludeTypes Set this to a number or array of numbers
     *  of correspondence types to exclude from the query
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getCorrespondence($limit = 200, $excludeTypes = NULL)
    {
        $correspondenceTable = new Model_DbTable_BusinessCorrespondence();
        $correspondenceSelect = $correspondenceTable->select()
            ->where('BusinessID = ?', $this->ID)
            ->order('DateSent DESC')
            ->limit($limit);

        if (!is_null($excludeTypes)) {
            if (is_array($excludeTypes)) {
                $correspondenceSelect->where('type NOT IN (' . implode(',', $excludeTypes) . ')');
            } else
            if (is_numeric($excludeTypes)) {
                $correspondenceSelect->where('type != ?', $excludeTypes);
            }
        }

        return $correspondenceTable->fetchAll($correspondenceSelect);
    }

    /**
     * Gets all the texts for this business
     * @param integer $limit Limits the number of items to return
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getTexts($limit = 200)
    {
        $textTable = new Model_DbTable_Nctext();
        return $textTable->fetchAll('BusinessID = ' . $this->ID, 'DateSent DESC', $limit);
    }

    /**
     * Gets all the correspondence for the business, including emails and texts.
     * Returns them in order of the date they were sent
     * @param array|NULL $excludeTypes Set this to a number or array of numbers
     *  of correspondence types to exclude from the query
     */
    public function getAllCorrespondence($limit = 200, $excludeTypes = NULL)
    {
        $correspondence = $this->getCorrespondence($limit, $excludeTypes);
        $correspondenceCount = count($correspondence);
        $correspondence = $correspondence->toArray();

        for ($i = 0; $i < $correspondenceCount; $i++) {
            $correspondence[$i]['correspondenceType'] = Model_DbTable_BusinessCorrespondence::CORRESPONDENCETYPE;
        }

        $texts = $this->getTexts($limit);
        $textCount = count($texts);
        $texts = $texts->toArray();

        for ($i = 0; $i < $textCount; $i++) {
            $texts[$i]['correspondenceType'] = Model_DbTable_Nctext::CORRESPONDENCETYPE;
        }

        // Combine and sort all by the date they were sent
        $allCorrespondence = array_merge($correspondence, $texts);
        usort($allCorrespondence, 'correspondenceSort');

        return array_slice($allCorrespondence, 0, $limit);
    }

    /**
     * Sets the video for this business
     * @param string $video YouTube video code to display
     */
    public function setVideo($video)
    {
        if (trim($video) != '') {
            $this->setMetaData(self::METAKEYVIDEO, $video);
        } else {
            $this->deleteMetaData(self::METAKEYVIDEO);
        }

        $this->updateLastUpdatedTime();
    }

    /**
     * Gets the number of times the pages have been viewed for the past
     *  month
     * @param integer $numberOfMonths Number of months to search profile
     *  views for
     * @return integer Number of views for the given time frame
     */
    public function getProfilePageViews()
    {
        return $this->getPastMonthAnalytics(Model_DbTable_Analytic::PROFILE_VIEW);
    }

    /**
     * Gets the number of times the business has appeared in search results
     * @return integer Number of views for the given time frame
     */
    public function getSearchResultViews()
    {
        $searchResults = 0;
        $searchResults += $this->getPastMonthAnalytics(Model_DbTable_Analytic::SEARCH_RESULTS);
        $searchResults += $this->getPastMonthAnalytics(Model_DbTable_Analytic::ADVERTISMENTS);

        return $searchResults;
    }

    /**
     * Generic method to get all the analytics for the past month
     * @param string $type Type of analytics to fetch
     * @return int Aggregated number of analytics for the past month
     */
    public function getPastMonthAnalytics($type)
    {
        $startDate = new DateTime();
        $endDate = new DateTime('-1 MONTH');

        $analyticsTable = new Model_DbTable_AnalyticSummary();
        $select = $analyticsTable->select()
            ->where('businessId = ?', $this->ID)
            ->where('type = ?', $type);

        $analyticsResult = $analyticsTable->fetchRow($select);

        if (isset($analyticsResult->analytics)) {
            $analytics = json_decode($analyticsResult->analytics, true);

            if (!isset($analytics[Model_DbTable_AnalyticSummary::DURATION_DAYS])) {
                return 0;
            } else {
                $analyticsCount = 0;

                while ($startDate > $endDate) {
                    $stringToFind = $startDate->format('Ymd');

                    if (isset($analytics[Model_DbTable_AnalyticSummary::DURATION_DAYS][$stringToFind])) {
                        $analyticsCount += $analytics[Model_DbTable_AnalyticSummary::DURATION_DAYS][$stringToFind];
                    }

                    $startDate->modify('-1 DAY');
                }

                return $analyticsCount;
            }
        } else {
            return 0;
        }
    }

    /**
     * Gets the number of times the business has appeared in high profile
     *  positions
     * @return integer Number of views for the given time frame
     */
    public function getHighProfileImpressions()
    {
        $searchResults = 0;
        $searchResults += $this->getPastMonthAnalytics(Model_DbTable_Analytic::ADVERTISMENTS);
        $searchResults += $this->getPastMonthAnalytics(Model_DbTable_Analytic::FRONT_PAGE);
        $searchResults += $this->getPastMonthAnalytics(Model_DbTable_Analytic::RECENT_RATINGS);

        return $searchResults;
    }

    /**
     * Gets the number of times the business' website has been viewed
     * @return integer Number of views for the given time frame
     */
    public function getWebsiteViews()
    {
        return $this->getPastMonthAnalytics(Model_DbTable_Analytic::WEBSITE_VIEW);
    }

    /**
     * Gets the number of times the business' phone number has been viewed
     * @return integer Number of views for the given time frame
     */
    public function getPhoneNumberViews()
    {
        return $this->getPastMonthAnalytics(Model_DbTable_Analytic::PHONE_NUMBER_VIEW);
    }

    /**
     * Gets the last time the business registered from a non-registered
     *  position
     * @return integer|false Date first registered or NULL if the business
     *  has not registered
     */
    public function getLastRegisteredDate()
    {
        $payments = $this->getAllPayments(false);
        $registered = $this->isRegistered();

        if (!$registered) {
            return NULL;
        }

        $lastRegisteredExpiry = NULL;
        $lastPaid = NULL;
        $paymentsArray = array();

        foreach ($payments as $payment) {
            $daysPaid = $payment->DaysPaid;
            $datePaid = strtotime($payment->DatePaid);
            $paymentExpiryDate = strtotime('+' . $daysPaid . ' days', $datePaid);

            $paymentsArray[] = array('daysPaid' => $daysPaid, 'datePaid' => $datePaid, 'expiryDate' => $paymentExpiryDate,
                'datePaidActual' => date('r', $datePaid), 'dateExpiredActual' => date('r', $paymentExpiryDate));
        }

        foreach ($paymentsArray as $paymentItem) {

            if (is_null($lastRegisteredExpiry)) {
                $lastRegisteredExpiry = $paymentItem['expiryDate'];
                $lastPaid = $paymentItem['datePaid'];
            } else {
                if ($lastPaid <= $paymentItem['expiryDate']) {
                    $lastRegisteredExpiry = $paymentItem['expiryDate'];
                    $lastPaid = $paymentItem['datePaid'];
                } else {
                    break;
                }
            }
        }

        return $lastPaid;
    }

    /**
     * Returns the physical address for the business
     * @return string
     */
    public function getPhysicalAddress()
    {
        $return = '';
        $suburb = $this->getLocationArea();

        if (($this->LocationAreaID > 0) and ( trim($this->AddressStreetName) != '')) {
            if (trim($this->AddressStreetNumber) != '') {
                $return .= trim($this->AddressStreetNumber) . ' ' . trim($this->AddressStreetName);
            } else {
                $return .= trim($this->AddressStreetName);
            }

            if (!is_null($suburb)) {
                $return .= Nocowboys_Tools::lineBreakIfNotEmpty($suburb->Name, NULL, ',');
            }

            $return .= Nocowboys_Tools::lineBreakIfNotEmpty($this->addressPostcode, NULL, ',');
        }

        if (trim($return) == '') {
            $return = nl2br($this->PhysicalAddress);
        }

        if (trim($return) == '') {
            $return = nl2br($this->AddressDetails);
        }

        return $return;
    }

    /**
     * Returns a list of badges that are attibuted to this business
     *
     * @return array Array of badges
     */
    public function getBadges($fromCache = true, $emptyCache = false)
    {
        $cacheKey = self::CACHE_KEY_BADGES . $this->ID;

        // Empty the cache if that's what the user wants to do
        if ($emptyCache) {
            return Nocowboys_Db_Table_Cache::deleteCacheKey($cacheKey);
        }

        // Load from cache if we want to and it exists
        if (( $fromCache) and ( Nocowboys_Db_Table_Cache::test($cacheKey))) {
            return Nocowboys_Db_Table_Cache::load($cacheKey);
        }

        $badges = [];

        if ($this->isRegistered()) {
            $badges[] = self::BADGE_REGISTERED;
        }

        if ($this->isCustomerPreferred()) {
            $badges[] = self::BADGE_CUSTOMER_PREFERRED;
        }

        $ratings = $this->getAuthenticatedRatings();

        if (count($ratings) >= 1000) {
            $badges[] = self::BADGE_PLATINUM_BUSINESS;
        }
//			else
        if (count($ratings) >= 500) {
            $badges[] = self::BADGE_GOLD_BUSINESS;
        }
//			else
        if (count($ratings) >= 100) {
            $badges[] = self::BADGE_SILVER_BUSINESS;
        }
//			else
        if (count($ratings) >= 50) {
            $badges[] = self::BADGE_BRONZE_BUSINESS;
        }

        if ($fromCache) {
            Nocowboys_Db_Table_Cache::save($badges, $cacheKey);
        }

        return $badges;
    }

    /**
     * Returns the billing address for the business
     * @return string
     */
    public function getBillingAddress($fallbackToPhysicalAddress = true)
    {
        $return = '';
        $suburb = $this->getPostalLocationArea();

        if (($this->PostalSuburbID > 0) and ( ( trim($this->PostalStreetName) != '') or ( trim($this->PostalUnitNumber) != ''))) {
            if (trim($this->PostalStreetNumber) != '') {
                $postalNumberAndStreet = trim($this->PostalStreetNumber) . ' ' . trim($this->PostalStreetName);
            } else {
                $postalNumberAndStreet = trim($this->PostalStreetName);
            }

            if (trim($this->PostalUnitNumber) != '') {
                $return .= 'PO box ' . $this->PostalUnitNumber;
                $return .= Nocowboys_Tools::lineBreakIfNotEmpty($postalNumberAndStreet, NULL, ',');
            } else {
                $return .= $postalNumberAndStreet;
            }


            if (!is_null($suburb)) {
                $return .= Nocowboys_Tools::lineBreakIfNotEmpty($suburb->Name, NULL, ',');
            }

            $return .= Nocowboys_Tools::lineBreakIfNotEmpty($this->postalPostcode, NULL, ',');
        }

        if (trim($return) == '') {
            $return = nl2br($this->PostageAddress);
        }

        if (trim($return) == '') {
            $return = nl2br($this->AddressDetails);
        }

        if ((trim($return) == '') and ( $fallbackToPhysicalAddress)) {
            $return = $this->getPhysicalAddress();
        }

        return $return;
    }

    /**
     * Unsubscribes from any mailing lists the user may be subscribed to
     * @return boolean True if the unsubscribe was successful
     */
    public function unsubscribeFromMailingList()
    {
        if (trim($this->EmailAddress) == '') {
            return false;
        }

        $mailChimp = new Nocowboys_MailChimp();
        return $mailChimp->unsubscribe(Nocowboys_MailChimp::getListIdBusinesses(), $this->EmailAddress);
    }

    /**
     * Returns data used for the external flair. Cache as much of this
     *  as possible
     * @return array Information for the flair
     */
    public function getFlairData()
    {
        $authenticatedRatings = $this->getAuthenticatedRatings();
        $overallRating = $this->TempOverall;
        $config = Zend_Registry::get('config');
        $domain = $config->domain;

        $resultArray = array();
        $resultArray['ratingCount'] = count($authenticatedRatings);
        $resultArray['name'] = $this->CompanyName;
        $resultArray['url'] = $domain . $this->getURI();
        $resultArray['overall'] = $overallRating;
        $resultArray['registered'] = $this->isRegistered() ? 1 : 0;

        return $resultArray;
    }

    /**
     * Returns the most recent rating for this business
     * @return Model_DbTableRow_Rating
     */
    public function getMostRecentRating()
    {
        $ratingTable = new Model_DbTable_Rating();
        $ratingSelect = $ratingTable->select(array('Comment'))
            ->where('BusinessID = ?', $this->ID)
            ->order('RankDate DESC')
            ->limit(1);

        $result = $ratingTable->fetchRow($ratingSelect);
        unset($ratingTable);

        if (!is_null($result)) {
            return $result->Comment;
        } else {
            return NULL;
        }
    }

    /**
     * Returns an array of the properties of this business, but to be given
     *  to the search listing view helper
     *
     * @return array
     */
    public function toArraySearch()
    {
        $primaryCategory = $this->getPrimaryCategory(true);

        $returnArray = $this->toArray();
        $returnArray['uri'] = $this->getUri();
        $returnArray['businessName'] = $this->CompanyName;
        $returnArray['ratingCount'] = count($this->getRatings());
        $returnArray['overallRating'] = round($this->TempOverall);
        $returnArray['assocations'] = $this->getAssociations()->toArray();
        $returnArray['badges'] = $this->getBadges();
        $returnArray['registered'] = $this->isRegistered();
        $returnArray['customerPreferred'] = $this->isCustomerPreferred();
        $returnArray['primaryCategoryId'] = $primaryCategory->ID;
        $returnArray['primaryCategoryName'] = $primaryCategory->Name;

        return $returnArray;
    }

    /**
     * Gets the breadcrumb for this business
     *
     * @return array Array of items to give to the breadcrumb helper
     */
    public function getBreadcrumbArray($additionalCrumb = '')
    {
        $primaryBusinessCategory = $this->getPrimaryCategory(true);
        $breadcrumbArray = [];

        if (!is_null($primaryBusinessCategory)) {
            // Get the area for the current user
            $area = Nocowboys_Controller_V3_Action::getUserLocationData();
            $businessArea = $this->getLocationArea();

            if ((!is_null($area)) and ( isset($area->suburbSlug))) {
                $areaSlug = $area->suburbSlug;
            } else
            if (!is_null($businessArea)) {
                $areaSlug = $businessArea->URLName;
            } else {
                $areaSlug = 'new-zealand';
            }

            $breadcrumb = $primaryBusinessCategory->getBreadcrumb();

            foreach ($breadcrumb as $crumb) {
                $breadcrumbArray['/search/' . $areaSlug . '/' . $crumb->URLName] = $crumb->Name;
            }

            $breadcrumbArray = array_reverse($breadcrumbArray);
            $breadcrumbArray['/search/' . $areaSlug . '/' . $primaryBusinessCategory->URLName] = $primaryBusinessCategory->Name;
        }

        if ($additionalCrumb == '') {
            $breadcrumbArray[] = Nocowboys_Tools::escape($this->CompanyName);
        } else {
            $breadcrumbArray[$this->getURI()] = Nocowboys_Tools::escape($this->CompanyName);
            $breadcrumbArray[] = $additionalCrumb;
        }

        return $breadcrumbArray;
    }

    /**
     * Gets the monthly payment date for this business
     *
     * @return DateTime|NULL
     */
    public function getMonthlyPaymentDate()
    {
        if (!is_null($this->monthlyPaymentDate)) {
            return new DateTime($this->monthlyPaymentDate);
        }

        return NULL;
    }

    /**
     * Sets the monthly payment date if it isn't already set (or overrides
     *  it if one is passed in)
     *
     * @param DateTime $date Date to set it to
     * @return DateTime The set date
     */
    public function setMonthlyPaymentDate(DateTime $date = NULL)
    {
        if (!is_null($date)) {
            $this->monthlyPaymentDate = $date->format(Nocowboys_Db_Table_Abstract::DATE_TIME_FORMAT_MYSQL);
            $this->save();
        } else {
            if ((!isset($this->monthlyPaymentDate)) or ( is_null($this->monthlyPaymentDate))) {
                // If we have an expiry date for the business, then set that
                //  as the next payment date
                $expiryDate = $this->getRegistrationExpiryDateFromTable();

                if ((is_null($expiryDate)) or ( $expiryDate < time())) {
                    $now = new DateTime();
                    $this->monthlyPaymentDate = $now->format(Nocowboys_Db_Table_Abstract::DATE_TIME_FORMAT_MYSQL);
                } else {
                    $this->monthlyPaymentDate = date(Nocowboys_Db_Table_Abstract::DATE_TIME_FORMAT_MYSQL, $expiryDate);
                }

                $this->save();
            }
        }

        return $this->monthlyPaymentDate;
    }

    /**
     * Increments the monthly payment date by a month
     *
     */
    public function incrementMonthlyPaymentDate($numberOfMonths = 1)
    {
        // If no date is already set, then this one will set it to right
        //  now before incrementing it
        $this->setMonthlyPaymentDate();

        $monthlyPaymentDate = new DateTime($this->monthlyPaymentDate);
        $monthlyPaymentDate->modify('+' . $numberOfMonths . ' MONTH');

        $this->setMonthlyPaymentDate($monthlyPaymentDate);
    }

    /**
     * Checks if the business' credit card is expiring soon
     *
     * @param NULL|DateTime $currentDateTime Used for testing
     * @return boolean True if the card is expiring in the next two months
     */
    public function isCreditCardExpiring($currentDateTime = NULL)
    {
        if (is_null($this->monthlyPaymentCardExpiryDate)) {
            return false;
        }

        if (is_null($currentDateTime)) {
            $currentDateTime = new DateTime();
        }

        $businessCardExpiryDate = $this->getCreditCardExpiryDate();

        $interval = $currentDateTime->diff($businessCardExpiryDate);
        $dayDifference = $interval->days;
        $inTheFuture = $interval->invert == 0;

        return (($dayDifference < 60) and ( $dayDifference > 0) and ( $inTheFuture));
    }

    /**
     * Checks if the business' credit card has expired
     *
     * @param NULL|DateTime $currentDateTime Used for testing
     * @return boolean True if the card has expired
     */
    public function creditCardHasExpired($currentDateTime = NULL)
    {
        if (is_null($this->monthlyPaymentCardExpiryDate)) {
            return false;
        }

        if (is_null($currentDateTime)) {
            $currentDateTime = new DateTime();
        }

        $businessCardExpiryDate = $this->getCreditCardExpiryDate();

        $interval = $currentDateTime->diff($businessCardExpiryDate);
        $inTheFuture = $interval->invert == 0;

        return (!$inTheFuture);
    }

    /**
     * Returns the date the business' credit card will expire
     *
     * @return NULL|DateTime
     */
    public function getCreditCardExpiryDate()
    {
        if (is_null($this->monthlyPaymentCardExpiryDate)) {
            return NULL;
        }

        return new DateTime($this->monthlyPaymentCardExpiryDate);
    }

    /**
     * Checks to see if there's a saved card we can use to take purchases
     *
     * @return boolean True if there is a saved card we can use for future
     *  purchases
     */
    public function hasSavedCard()
    {
        $cardExpiryDate = $this->getCreditCardExpiryDate();

        if (!is_null($cardExpiryDate)) {
            $now = new DateTime();
            return ((!is_null($this->paymentExpressToken)) and ( trim($this->paymentExpressToken) != '') and ( $cardExpiryDate > $now));
        }

        return false;
    }

    public function hasCardDataSaved()
    {
        return ((!is_null($this->paymentExpressToken)) and ( trim($this->paymentExpressToken) != '')and!empty($this->monthlyPaymentCardholderName) and!empty($this->monthlyPaymentCardExpiryDate) and!empty($this->monthlyPaymentCardExpiryDate));
    }

    /**
     * Gets a summary of the saved card information for display
     *
     * @return array
     */
    public function getSavedCardInformation()
    {
        return [
            'cardNumber' => $this->monthlyPaymentCardNumber,
            'cardExpiryDate' => $this->getCreditCardExpiryDate(),
            'cardholderName' => $this->monthlyPaymentCardholderName
        ];
    }

    /**
     * Gets the cache key for the business marker
     *
     * @return string
     */
    public function getCacheKeyForMarker()
    {
        return 'marker_fetch_row_business_' . $this->ID;
    }

    /**
     * Resets business payment information to null or empty and stops monthly payment schedule.
     * Reason being if monthly payment date is null further payment will not be processed by
     *  the cron job monthlyRegistrationRunRenewals.php
     *
     * @return boolean Returns true if the business object is saved successfully otherwise false
     */
    public function stopMonthlyPayment()
    {
        $this->monthlyPaymentDate = NULL;
        $this->monthlyPaymentCardholderName = '';
        $this->monthlyPaymentCardNumber = '';
        $this->monthlyPaymentCardExpiryDate = NULL;
        $this->paymentExpressToken = '';
        $this->allow_auto_monthly_payment = 0;
        return $this->save();
    }

    /**
     * Pause any active monthly payments and updates monthly payment date with future date from user
     *
     * @param string $paymentDate payment date till that the monthly payment is paused
     * @return boolean Returns true if the payment date is saved successfully, false
     *  if the payment date is invalid
     */
    public function pauseMonthlyPayment($paymentDate)
    {
        $monthlyPaymentDate = date('Y-m-d', strtotime($paymentDate));

        if ((isset($monthlyPaymentDate)) and ($monthlyPaymentDate >= date('Y-m-d'))) {
            $this->monthlyPaymentDate = $monthlyPaymentDate;
            return $this->save();
        } else {
            return false;
        }
    }

    /**
     * Deregisters a business
     *
     */
    public function deregister($notify = true)
    {
        $config = Zend_Registry::get('config');
        $urlHelper = new Zend_View_Helper_Url();

        $this->Registered = 0;
        $this->SubscribedProductId = 0;
        $this->save(true, true);

        // Unsubscribe from
        $this->removeSalesEmailMailchimpBusinessesRegistered();

        // Email the account manager
        $emailParameters = [
            'contactName' => $this->Name,
            'companyName' => $this->CompanyName,
            'companyId' => $this->ID,
            'businessId' => $this->ID,
            'companyCRMLink' => $config->domain . $urlHelper->url(['id' => $this->ID], 'crm-view-business'),
            'phoneNumber' => $config->email->template->alternatePhoneNumber,
            'registrationEmail' => $config->email->template->registrationEmail
        ];

        if ($notify) {
            if (Nocowboys_Email::sendEmail(
                    $config->website->operationsEmail,
                    'Business registration lapsed',
                    'internal/businessDeregistrationNotification',
                    $emailParameters
                )) {
                Nocowboys_Email::sendEmail(
                    $this->EmailAddress,
                    'Your NoCowboys Registration has lapsed',
                    'product-renewals/registration-lapsed',
                    $emailParameters
                );
            }
        }
    }

    /**
     * Gets default product ID for this business.
     *
     *
     * @return Model_DbTableRow_Product
     */
    public function getDefaultProductId()
    {
        $config = Zend_Registry::get('config')->website;

        if (!empty($this->default_package_id)) {
            return $this->default_package_id;
        }

        return $config->defaultProductId;
    }

    public function getDefaultProduct()
    {
        if ($defaultProductId = $this->getDefaultProductId()) {
            $productTable = new Model_DbTable_Product();

            return $productTable->fetchRow($productTable->select()->where('ID = ?', $defaultProductId));
        }

        return false;
    }

    /**
     * Gets default monthly product ID for this business.
     *
     *
     * @return mixed
     */
    public function getDefaultMonthlyProductId()
    {
        if ($this->is_facebook_lead == self::FACEBOOK_LEAD_TYPE_1) {
            return Zend_Registry::get('config')->website->defaultMonthlyProductIdForFacebookLeads;
        }

        return Zend_Registry::get('config')->website->defaultMonthlyProductId;
    }

    /**
     * Gets renewal product row.
     *
     *
     * @return Zend_Db_Table_Row_Abstract
     */
    public function getRenewalPackage()
    {
        $currentPackage = $this->getCurrentPackage();

        $productTable = new Model_DbTable_Product();

        return $currentPackage ? $productTable->fetchRowByValue(
                'ID', Model_DbTable_Product::getProductIdForRenewal($currentPackage->ID)
            ) : $productTable->fetchRowByValue('ID', $this->getDefaultProductId());
    }

    public function getRecentRating()
    {
        $ratingTable = new Model_DbTable_Rating();
        $ratingSelect = $ratingTable->select(array('Comment'))
            ->where('BusinessID = ? AND Hidden = 0 AND Locked = 0', $this->ID)
            ->where('need_approve = ?', Model_DbTable_Rating::RATING_STATUS_APPROVED)
            ->order('RankDate DESC')
            ->limit(1);

        return $ratingTable->fetchRow($ratingSelect);
    }

    /**
     * Returns business sales email
     *
     * @return string
     */
    public function getSalesEmail()
    {
        return $this->SalesEmail;
    }

    /**
     * Sets new product for business.
     *
     *
     * @return bool
     */
    public function setProduct($productID)
    {
        return $this->SubscribedProductId = $productID;
    }

    /**
     * Sets new default product for business.
     *
     *
     * @return bool
     */
    public function setDefaultProduct($productID)
    {
        return $this->default_package_id = $productID;
    }

    /**
     * Adds SalesEmail to list of Registered Businesses.
     *
     *
     * @return bool
     *
     * @throws Exception
     */
    public function addSalesEmailToListOfBusinessesRegistered($oldEmail = '')
    {
        !empty($oldEmail) ? $email = $oldEmail : $email = $this->SalesEmail;

        $businessMailchimp = new Model_DbTable_BusinessMailchimp($this->ID);

        if ($email !== $this->EmailAddress) {
            $this->removeSalesEmailMailchimpBusinessesRegistered($oldEmail);
        }

        if (empty($this->SalesEmail) or!$this->isRegistered()) {
            return false;
        }

        $businessMailchimp->createAddSalesTask();
    }

    public function removeSalesEmailMailchimpBusinessesRegistered($email = '')
    {
        if (empty($email) and empty($this->SalesEmail)) {
            return false;
        } elseif (empty($email) and!empty($this->SalesEmail)) {
            $email = $this->SalesEmail;
        }
        $businessMailchimp = new Model_DbTable_BusinessMailchimp($this->ID);

        $businessMailchimp->createDeleteSalesTask($email);
    }

    public function updateSubscriptionForBusiness($oldEmail = '')
    {
        if (empty($oldEmail)) {
            $oldEmail = $this->EmailAddress;
        }


        if (empty($this->EmailAddress)) {
            return false;
        }


        $businessMailchimp = new Model_DbTable_BusinessMailchimp($this->ID);
        $businessMailchimp->createDeleteTask($oldEmail);

        $businessMailchimp->createAddTask();

        return true;
    }

    /**
     * Checks if businesses should show in search result
     *
     *
     * @return bool
     *
     */
    public function showInSearchResult()
    {
        return ($this->availableForListOfSearchResult() and intval($this->search_result_show) === 1);
    }

    /**
     * Add business to search result list
     *
     * @return bool
     *
     */
    public function addToSearchResult()
    {
        Nocowboys_Elastic::addBusiness($this->ID);
        $this->search_result_show = 1;
        return $this->save(true);
    }

    /**
     * Remove business from search result list
     *
     * @return bool
     *
     */
    public function removeFromSearchResult()
    {
        Nocowboys_Elastic::deleteBusiness($this->ID);
        $this->search_result_show = 0;
        return $this->save(true);
    }

    /**
     * Get last payment
     *
     * @return Model_DbTableRow_Purchase
     *
     */
    public function getLastPayment()
    {
        $purchaseTable = new Model_DbTable_Purchase();
        return $purchaseTable->fetchRow($purchaseTable->select()->where('Successful = 1 AND BusinessID = ?', $this->ID)->order('PurchaseDate DESC'));
    }

    /**
     * Set review underway status for business
     * @return mixed
     */
    public function setReviewUnderway()
    {
        $this->is_review_underway = 1;
        return $this->save();
    }

    /**
     * Check if business is on review audit
     * @return bool
     */
    public function isReviewUnderway()
    {
        return (int) $this->is_review_underway === 1;
    }
    
}


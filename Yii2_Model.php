<?php

class Order extends CommonOrder
{
    const GUEST_CHECKOUT       = 'guest';
    const GUEST_CHECKOUT_VALUE = 1;
    const TRANSPORTATION_ON = 'on';
    
    public $terms = false;

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public static function loadOrderFromCart(Cart $cart, $guestCheckout = false)
    {
        /** @var $order Order */

        if ($guestCheckout) {
            $order = new Order(self::GUEST_CHECKOUT);
            $order->guest = self::GUEST_CHECKOUT_VALUE;
        } else {
            $order = new Order();
        }

        if($cart->hasDiscount()) {
            $orderDiscount = $cart->getOrderDiscount();
            if(null != $orderDiscount) {
                $order->setOrderDiscount($orderDiscount);
                if($order->isDeferredPayment()) {
                    $order->payment_option = app()->settings->get('order', 'retailerInstantPayment');
                }
            }
        }
        
        $order->order_experience_giftpackage_code_id = $cart->order_experience_giftpackage_code_id;

        /* Load Customer Info  */
        if (null == $order->customer) {
            if (Yii::app()->user->getState('scope') == User::SCOPE_EMPLOYEE) {
                $driverBuyer = $cart->findDriverBuyerExperience();
                if ($driverBuyer) {
                     $customer = Customer::model()->find('email = :email', array(
                        ':email' => $driverBuyer->getEmail()
                    ));
                    if ($customer) {
                        $order->customer = $customer;
                        $order->customer_id = $customer->id; 
                        $retailerRepresentative = RetailerRepresentative::model()->getRepresentativeByCustomerId($customer->id);
                        if ($retailerRepresentative) {
                            $order->orderoccasion_id = app()->settings->get('order', 'retailerOccasionDefault');
                           $order->orderhearaboutus_id = $retailerRepresentative->retailer->orderhearaboutus_id;
                        } else {
                            $order->orderhearaboutus_id = $order->getLastOrderHearAboutUs($customer->id);
                            $order->orderoccasion_id = $order->getLastOrderOccasion($customer->id);
                        }
                    }
                    
                }
            } else {
                $order->loadCustomer();
            }
        }
        
        /* Load OrderExperiences with OrderDrivingExperiences */
        $orderExperience = array();
        foreach ($cart->cartExperiences as $cartExperience) {
            $orderExperience         = OrderExperience::createFromCartExperience($cartExperience, $order);
            $order->orderExperiences = array_merge($order->orderExperiences, $orderExperience);
            if ($orderExperience->parent_id) {
                $order->parent_id = $orderExperience->parent->order_id;
            }
        }
        
        return $order;
    }

    public function init()
    {
        if ($this->isNewRecord) {
            $this->purchased_date = date('Y-m-d');
            $this->purchased_time = date('H:i:s');
            $this->commercial_type_id = CommercialType::COMMERCIAL_WEBSITE;
            if (Yii::app()->user->getState('scope') == User::SCOPE_EMPLOYEE) {
                $this->orderbookinglocation_id = OrderBookingLocation::LOCATION_CSR_ID;
                $employee = Employee::model()->find('user_id = :user_id', array(
                    ':user_id' => Yii::app()->user->getId()
                ));
                $this->commercial_id = $employee ? $employee->id : null;
                $this->commercial_type_id = $employee ? $employee->commercial_type_id : null;
            } else if(app()->browser->isMobile()) {
                $this->orderbookinglocation_id = OrderBookingLocation::LOCATION_MOBILE_ID;
            } else {
                $this->orderbookinglocation_id = app()->getModule('order')->getConfig('bookingLocationDefault');
            }
            
            $this->orderstatus_id          = app()->getModule('order')->getConfig('orderStatusDefault');
            $this->ip_address              = app()->request->getUserHostAddress();
            $this->ordercustomertype_id    = OrderCustomerType::TYPE_INDIVIDUAL;

            /* Totals defaults */
            $this->subtotal = 0;
            $this->discount = 0;
            $this->tax      = 0;
            $this->total    = 0;

            if (!app()->user->getIsGuest()) {
                if (Yii::app()->user->getState('scope') != User::SCOPE_EMPLOYEE) {
                    $customer = $this->getCurrentCustomer();
                    if (null != $customer) {
                        $this->orderhearaboutus_id = $this->getLastOrderHearAboutUs($customer->id);
                        $this->orderoccasion_id = $this->getLastOrderOccasion($customer->id);
                    }
                }         
                
            }
        }
    }

    public function rules()
    {
        return array(
            array('orderstatus_id, purchased_date, purchased_time, payment_option, ordercustomertype_id, subtotal, total, commercial_type_id', 'required'),
            array('orderstatus_id, orderbookinglocation_id, ordercustomertype_id, commercial_id, customer_id, shippingmethodoption_id, orderdiscount_id', 'numerical', 'integerOnly' => true, 'message' => t('Please select a value for {attribute}.')),
            array('shippingmethodoption_id', 'shippingOption'),
            array('discount_code', 'length', 'max' => 50),
            array('shipping_method, shipping_option_name', 'length', 'max' => 255),
            array('shipping_option_days, payment_option', 'length', 'max' => 100),
            array('shipping_option_price, ip_address, subtotal, discount, tax, total', 'length', 'max' => 15),
            array('guest', 'length', 'max' => 1),
            array('comments, purchasing_gift, shipping_address_different, orderhearaboutus_id, orderoccasion_id', 'safe'),
            array('id, orderstatus_id, orderbookinglocation_id, orderhearaboutus_id, orderoccasion_id, purchased_date, purchased_time,  commercial_id, commercial_type_id, customer_id, shippingmethodoption_id, shipping_method, shipping_option_name, shipping_option_days, shipping_option_price, comments, ip_address, guest, payment_option, orderdiscount_id, subtotal, discount, tax, total', 'safe', 'on' => 'search'),
            array('commercial_type_id', 'validCommercialType'),
            array('commercial_id', 'validCommercialId'),
            array('orderdiscount_id', 'validOrderDiscount'),
            array('orderdiscount_id', 'discountApplied'),
            array('terms', 'required', 'requiredValue' => 1, 'message' => 'You should agree to Terms & Conditions')
        );
    }

    public function discountApplied($attribute, $params)
    {
        /** @var $orderDiscount OrderDiscount */
        /** @var $retailerRepresentative RetailerRepresentative */

        $orderDiscount = OrderDiscount::model()->findByPk($this->$attribute);

        if($orderDiscount == null){
            $retailerRepresentative = RetailerRepresentative::model()->findByAttributes(array('customer_id' => $this->customer_id));

            if( ($retailerRepresentative != null) && !$retailerRepresentative->isFromCompanyRetailer() ){

                $this->addError($attribute, Yii::t('Orders and carts','Please apply your retailer code'));

            }
        }

    }

    /**
     * @return array of behaviors
     */
    public function behaviors()
    {
        return CMap::mergeArray(parent::behaviors(), array(
            'ExLinkableBehavior' => array(
                'class' => 'ExLinkableBehavior',
                'baseRoute' => '/order/order',
                'viewRoute' => '/order/order/view/id',
            ),
            'ExWhenWhoBehavior' => array(
                'class' => 'ExWhenWhoBehavior',
                'setUpdateOnCreate' => true,
            )
        ));
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return CMap::mergeArray(parent::attributeLabels(), array(
            'orderstatus_id' => Yii::t('Orders and carts','Status'),
            'orderhearaboutus_id' => Yii::t('Orders and carts','How did you find us?'),
            'orderoccasion_id' => Yii::t('Orders and carts','What\'s the Occasion?'),
            'orderbookinglocation_id' => Yii::t('Orders and carts','Booking from'),
            'shippingmethodoption_id' => Yii::t('Orders and carts','Delivery Option'),
            'purchasing_gift' => Yii::t('Orders and carts','Are you purchasing this as a gift?'),
            'shipping_address_different' => Yii::t('Orders and carts','Click here if delivery address is different '),
            'id' => Yii::t('Orders and carts','Reference'),
        ));
    }

    public function checkout()
    {

        $this->setShippingOptions();

        /* Save the order */
        return $this->save(false);
    }
    
    public function getContentIds()
    {
        $ids = array();
        foreach ($this->orderExperiences as $experience) {
            if ($experience->giftpackage_id) {
                $ids[] = 'giftpackage-' . $experience->giftpackage_id;
            } else {
                foreach ($experience->orderDrivingExperiences as $drivingExperience) {
                   $ids[] = 'vehiclemodel-' . $drivingExperience->getVehicleModel()->id;
                }
            }
            foreach ($experience->orderExperienceAddons as $experienceAddon) {
                $ids[] = 'addon-' . $experienceAddon->addon_id;
            }
        }
        return json_encode($ids);
    }
    
    public static function geOrderInfo($id)
    {
        $model = self::model()->findByPk($id);
        if ($model) {
            $data = $model->getAttributes();
            $data['customer'] = $model->customer->getAttributes();
            $data['billing'] = $model->orderContacts[1]->getAttributes();
            $data['orderExperiences'] = array();
            $data['saved'] = $model->getSavedAmount();
            $data['reference'] = $model->getReference();
            foreach ($model->orderExperiences as $key => $experience) {
                $data['orderExperiences'][$key] = $experience->getAttributes();
                $data['orderExperiences'][$key]['student'] = $experience->student;
                $data['orderExperiences'][$key]['track'] = $experience->track->name;
                $data['orderExperiences'][$key]['date'] = $experience->trackdatesession_id ? $experience->trackDate->date : null;
                $data['orderExperiences'][$key]['session'] = $experience->trackdatesession_id ? $experience->trackDateSession->from : null;
                $data['orderExperiences'][$key]['location'] = $experience->pickuplocation_id ? $experience->pickupLocation->name : null;
                $data['orderExperiences'][$key]['transportationTime'] = $experience->location_time_entry_id ? $experience->transportationTime->title : null;
                if ($experience->giftpackage_id) {
                    $data['orderExperiences'][$key]['giftPackage'] = $experience->giftPackage->getAttributes();
                    $data['orderExperiences'][$key]['giftPackage']['image'] = $experience->giftPackage->getImagePath('main');
                } else {
                    $data['orderExperiences'][$key]['giftPackage'] = null;
                }
                $data['orderExperiences'][$key]['experienceAddons'] = array();
                foreach ($experience->getOrderExperienceAddons() as $i => $addonExperience) {
                    $data['orderExperiences'][$key]['experienceAddons'][$i] = $addonExperience->getAttributes();
                    $data['orderExperiences'][$key]['experienceAddons'][$i]['addon'] = $addonExperience->addon->getAttributes();
                    $data['orderExperiences'][$key]['experienceAddons'][$i]['price'] = $addonExperience->getPrice();
                    $data['orderExperiences'][$key]['experienceAddons'][$i]['addon']['image'] = $addonExperience->addon->getImagePath('thumbnail');
                }
                $data['orderExperiences'][$key]['drivingExperiences'] = array();
                foreach ($experience->drivingExperiences as $j => $drivingExperience) {
                    $data['orderExperiences'][$key]['drivingExperiences'][$j] = $drivingExperience->getAttributes();
                    $data['orderExperiences'][$key]['drivingExperiences'][$j]['trackVehicleCategory'] = $drivingExperience->trackvehicle->trackvehiclecategory->name;
                    $data['orderExperiences'][$key]['drivingExperiences'][$j]['freeLaps'] = $drivingExperience->getFreeLaps();
                    $vehicleModel = $drivingExperience->getVehicleModel();
                    $data['orderExperiences'][$key]['drivingExperiences'][$j]['vehicleModel'] = array(
                        'name' => $vehicleModel->vehiclemake->name . ' ' . ($vehicleModel->vs_name ? $vehicleModel->vs_name : $vehicleModel->name),
                        'image' => $vehicleModel->getImagePath('vegassupercars_main')
                    );
                    $data['orderExperiences'][$key]['drivingExperiences'][$j]['total'] = $drivingExperience->getTotal();
                    $data['orderExperiences'][$key]['drivingExperiences'][$j]['unit'] = $drivingExperience->trackvehicle->getBookingUnit($drivingExperience->laps);
                }
            }
            return $data;
        }
        return null;
    }

}
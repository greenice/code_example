<?php

use Dompdf\Dompdf;

Yii::import('application.modules.order.utils.OrderViewHelper');
Yii::import('application.modules.order.models._manager.OrderHistoriesManager');
Yii::import('application.modules.order.models.payment.*');
Yii::import('application.modules.customer.CustomerModule');
Yii::import('application.modules.order.utils.OrderViewHelper');
Yii::import('common.extensions.dompdf.autoload', true);

class OrderController extends EController
{

    /**
     * Specifies active menu and submenu in Sidebar Menu
     */
    public function init()
    {
        $this->modelName = 'Order';
        $this->layout = '//layouts/inner_wide';
        return parent::init();
    }

    public function filters()
    {
        return CMap::mergeArray(parent::filters(), array(
                array(
                    'common.extensions.filters.HttpsFilter + paynow',
                ),
                )
        );
    }

    public function actionView($id)
    {
        if (app()->user->getIsGuest()) {
            Yii::app()->user->returnUrl = Yii::app()->createUrl('/order/order/view', array(
                'id' => $id
            ));

            $this->redirect(Yii::app()->getModule('user')->loginUrl);
        }

        $order = $this->loadModel($id);

        if ($order->customer->user_id != app()->user->getId() && $order->cr_uid != app()->user->getId()) {
            throw new CHttpException(400, 'Invalid request. Please do not repeat this request again.');
        }

        $notices = array();

        if ($order->isNotCompletedPaid()) {
            $notices[] = $this->module->getConfig('orderNotPaidText');
        }

        $orderExperiencesNotScheduled = $order->getNotScheduledExperiences();

        if (count($orderExperiencesNotScheduled) > 0) {
            $notices[] = $this->module->getConfig('orderNotDateText');
        }

        if (count($notices) > 0) {
            $notices = '<strong>Notices: </strong><br>' . implode('<br>', $notices);
            app()->user->setFlash('error', $notices);
        }

        $this->render('single_order', array(
            'model' => $order,
            'message' => false
        ));
    }

    public function actionGenerated()
    {
        $this->layout = '//layouts/inner_wide';

        $orderId = app()->user->getState('generatedOrderId', null);

        if ($orderId == null) {
            $this->redirect(array('/customer/profile'));
        } else {
//            app()->user->setState('generatedOrderId', null);
        }

        $order = $this->loadModel($orderId);

        $notices = array();

        if ($order->ordercustomertype_id != OrderCustomerType::TYPE_CORPORATE) {
            if ($order->isNotCompletedPaid()) {
                $notices[] = $this->module->getConfig('orderNotPaidText');
            }

            $orderExperiencesNotScheduled = $order->getNotScheduledExperiences();

            if (count($orderExperiencesNotScheduled) > 0) {
                $notices[] = $this->module->getConfig('orderNotDateText');
            }
        }


        if (count($notices) > 0) {
            $notices = implode('<br>', $notices);
            app()->user->setFlash('notice', $notices);
        }

        $this->render('single_order', array(
            'model' => $order,
            'message' => Yii::t('Thank you page', 'Thank you for the order.'),
            'notify' => $order->ordercustomertype_id != OrderCustomerType::TYPE_CORPORATE,
            'trackingCodes' => $order->commercial_type_id == CommercialType::COMMERCIAL_WEBSITE
        ));
    }

    public function actionPayNow($id)
    {
        $order = $this->loadModel($id);

        if (!$order->isNotCompletedPaid()) {
            throw new CHttpException(400, 'Invalid request. Please do not repeat this request again.');
        }

        $paymentMethod = null;

        if (isset($_POST['Order']['payment_option'])) {

            try {
                $paymentOption = $_POST['Order']['payment_option'];
                $order->payment_option = $paymentOption;
                $paymentMethod = PaymentMethod::PaymentMethodFactory($paymentOption, $order);
                $paymentMethod->attributes = isset($_POST[get_class($paymentMethod)]) ? $_POST[get_class($paymentMethod)] : array();

                if ($paymentMethod->validate()) {

                    /* Process payment and Save to Order */
                    $paymentResponse = $paymentMethod->process();
                    if ($paymentResponse->hasError() === true) {
                        user()->setFlash('error', t('<b>Payment Error</b> ' . $paymentResponse->getErrorMessage()));
                    } else {

                        $order = $this->loadModel($id);
                        $order->setOrderPaidStatus(true);

                        // send order confirmation email
                        if ($order->isNotCompletedPaid()) {
                            app()->notifier->send(array(
                                'code' => Notification::CODE_PARTIAL_PAYMENT,
                                'to' => $order->customer->email,
                                'type' => 'email',
                                'data' => array(
                                    'order' => $order,
                                )
                            ));
                        } else {
                            app()->notifier->send(array(
                                'code' => Notification::CODE_ORDER_CONFIRMATION,
                                'to' => $order->customer->email,
                                'type' => 'email',
                                'data' => array(
                                    'order' => $order,
                                    'user' => null,
                                    'password' => null,
                                    'payment' => $paymentMethod
                                )
                            ));
                        }

                        user()->setFlash('success', t('<b>Successfully:</b> Payment for Order: #{modelID} received successfully.', array('{modelID}' => $order->id)));
                        $this->redirect('/order/order/view/id/' . $order->id);
                    }
                }
            } catch (Exception $e) {
                user()->setFlash('error', t('<b>Payment Error</b> ' . $e->getMessage()));
            }
        }


        if (null == $paymentMethod) {
            $order->payment_option = PaymentMethod::getDefaultInstantlyPaymentCode();
        }

        $this->render('paynow', array(
            'model' => $order,
            'paymentmodel' => $paymentMethod,
        ));
    }

    /**
     * Lists all order for current customer.
     */
    public function actionIndex()
    {
        if (app()->user->getIsGuest()) {
            $this->redirect(Yii::app()->getModule('user')->loginUrl);
        }

        $model = new $this->modelName('search');
        $customer = $model->getCurrentCustomer();
        if (Yii::app()->user->getState('scope') == User::SCOPE_EMPLOYEE) {
            $model->cr_uid = Yii::app()->user->id;
        } else {
            $model->customer_id = $customer->id;
        }

        $this->render('index', array(
            'model' => $model,
        ));
    }

    /**
     * Lists all order experiences for current customer.
     */
    public function actionRides()
    {
        if (app()->user->getIsGuest()) {
            $this->redirect(Yii::app()->getModule('user')->loginUrl);
        }

        $model = new OrderExperience('search');
        $customer = Order::model()->getCurrentCustomer();
        $model->customer_id = $customer->id;

        $this->render('rides', array(
            'model' => $model,
        ));
    }

    public function actionRideView($id)
    {
        if (app()->user->getIsGuest()) {
            throw new CHttpException(400, 'Invalid request. Please do not repeat this request again.');
        }

        $orderExperience = OrderExperience::model()->findByPk($id);

        if ($orderExperience == null) {
            throw new CHttpException(400, 'Invalid request. The Order Experience does not exists.');
        }

        $this->render('rides_view', array(
            'model' => $orderExperience
        ));
    }

    public function actionPrintCertificate($id)
    {
        if (app()->user->getIsGuest()) {
            $orderId = app()->user->getState('generatedOrderId', null);
            $order = $this->loadModel($orderId);

            $exists = false;
            foreach ($order->experiences as $experience) {
                if ($experience->id == $id) {
                    $exists = true;
                    break;
                }
            }

            if (!$exists) {
                throw new CHttpException(400, 'Invalid request. Please do not repeat this request again.');
            }

            $orderExperience = OrderExperience::model()->findByPk($id);

            if ($orderExperience === null) {
                throw new CHttpException(400, 'Invalid request. Please do not repeat this request again.');
            }
        } else {
            $orderExperience = OrderExperience::model()->findByPk($id);

            if ($orderExperience === null) {
                throw new CHttpException(400, 'Invalid request. Please do not repeat this request again.');
            }

            if ($orderExperience->order->customer->user_id != app()->user->getId()) {
                throw new CHttpException(400, 'Invalid request. Please do not repeat this request again.');
            }
        }

        $experience = null;
        if ($giftpackage = $orderExperience->getGiftPackage()) {
            $experience = $giftpackage->name;
            if ($giftpackage->price_type == 'custom') {
                $experience .= ' - ' . Yii::app()->format->formatCurrency($orderExperience->giftpackage_amount);
            }
        } else {
            $experiences = array();
            foreach ($orderExperience->drivingExperiences as $dE) {
                $trackVehicleModel = $dE->trackVehicle->vehicle->vehiclemodel;
                $title = $dE->discount_laps == 0 ? $dE->laps : $dE->laps . ' + ' . $dE->discount_laps;
                $title .= ' ' . Yii::t('Booking page', $dE->discount_laps == 0 ? $dE->trackVehicle->units : $dE->trackVehicle->free_units, $dE->discount_laps == 0 ? $dE->laps : $dE->discount_laps);
                $experiences[] = $trackVehicleModel->getMake()->name . ' ' . $trackVehicleModel->name . ' - ' . $title;
            }
            $experience = implode('<br>', $experiences);
        }

        $from = null;
        $customer = $orderExperience->order->customer;
        if (!RetailerRepresentative::model()->getRepresentativeByCustomerId($customer->id)) {
            $from = $customer->getCustomerFullName();
        }
        $code = OrderExperienceGiftPackageCode::findByExperience($orderExperience->id);

        $dompdf = new Dompdf();
        $dompdf->loadHtml($this->renderPartial('gift_certificate', array(
                'reference' => $orderExperience->order->getReference(),
                'track' => $orderExperience->track->name,
                'code' => $code ? $code->code : null,
                'to' => $orderExperience->getStudent()->getCustomerFullName(),
                'from' => $from,
                'date' => $orderExperience->trackdatesession ? $orderExperience->trackdatesession->trackdate->date . ' ' . $orderExperience->trackdatesession->from : null,
                'experience' => $experience,
                'filesBaseUrl' => Yii::getPathOfAlias('webroot')
                ), true));
        $dompdf->setPaper('A4');
        $dompdf->render();
        $dompdf->stream("gift_certificate.pdf", array("Attachment" => false));
        exit;
    }

    public function actionCreate()
    {
        throw new CHttpException(400, 'Invalid request. Please do not repeat this request again.');
    }

    /**
     * Updates a particular model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id the ID of the model to be updated
     */
    public function actionUpdate($id)
    {
        throw new CHttpException(400, 'Invalid request. Please do not repeat this request again.');
    }

    /**
     *
     */
    public function actionSendAfterOrderNotifications()
    {
        $orderId = Yii::app()->request->getParam('order_id');

        /* @var Order $order */
        $order = Order::model()->findByPk($orderId);

        $result = $order->sendAfterOrderNotifications();
        die($result);
    }

}

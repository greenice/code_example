<?php

$this->hideSlider(false);
$this->headerClass = ((app()->settings->get('cms', 'displaySearchOnAccount') == 1) ? Controller::SEARCH_BOX_HEADER : Controller::NO_HEADER);

$this->layout = '//layouts/inner_wide_white';

$this->actionTitle = Yii::t('Orders and carts', 'MY ORDERS');

$this->widget('bootstrap.widgets.TbGridView', array(
    'id' => 'order-grid',
    'dataProvider' => $model->ordered()->search(),
    'template' => '<header><span class="num">&nbsp;&nbsp;&nbsp;</span>' . Yii::t('Orders and carts', 'My Orders') . '</header>{items}',
    'htmlOptions' => array(
        'class' => 'grid-view box-page'
    ),
    'columns' => array(
        array(
            'name' => 'id',
            'type' => 'html',
            'value' => '$data->generateLink($data->getReference())',
            'htmlOptions' => array('class' => 'model-reference')
        ),
        array('name' => 'purchased_date',
            'value' => 'app()->format->formatBookDate($data->purchased_date)'
        ),
        array(
            'header' => Yii::t('Orders and carts', 'Experiences'),
            'value' => '$data->getNumExperiences()',
        ),
        array(
            'header' => Yii::t('Orders and carts', 'Add-ons'),
            'value' => '$data->getNumExtras()',
        ),
        array(
            'name' => 'orderbookinglocation_id',
            'value' => '$data->orderbookinglocation->name',
        ),
        array(
            'header' => Yii::t('Orders and carts', 'Subtotal'),
            'value' => 'app()->numberFormatter->formatCurrency($data->subtotal, app()->settings->get(\'settings\', \'defaultCurrency\'))',
        ),
        array(
            'header' => Yii::t('Orders and carts', 'Discount'),
            'value' => 'app()->numberFormatter->formatCurrency($data->discount, app()->settings->get(\'settings\', \'defaultCurrency\'))',
        ),
        array(
            'header' => Yii::t('Orders and carts', 'Tax'),
            'value' => 'app()->numberFormatter->formatCurrency($data->getTax(), app()->settings->get(\'settings\', \'defaultCurrency\'))',
        ),
        array(
            'header' => Yii::t('Orders and carts', 'Total'),
            'value' => 'app()->numberFormatter->formatCurrency($data->getTotal(), app()->settings->get(\'settings\', \'defaultCurrency\'))',
        ),
        array(
            'name' => 'paid',
            'type' => 'html',
            'filter' => '',
            'value' => 'OrderViewHelper::getPaidLabel($data->isNotCompletedPaid())',
        ),
        array(
            'class' => 'ExButtonColumn',
            'template' => '{view}',
        ),
    ),
));
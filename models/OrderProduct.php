<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "order_product".
 *
 * @property integer $id
 * @property integer $order_id
 * @property integer $id_product
 * @property double  $cost
 * @property integer $quantity_available
 * @property integer $quantity_no_available
 *
 * @property Order   $order
 */
class OrderProduct extends ActiveRecord {
    /**
     * @inheritdoc
     */
    public static function tableName() {
        return '{{%order_product}}';
    }

    /**
     * @param $id_product
     * @param $order_id
     *
     * @return OrderProduct
     */
    public static function create($id_product, $order_id): OrderProduct
    {
        $orderProduct = new OrderProduct();
        $orderProduct->id_product = $id_product;
        $orderProduct->order_id = $order_id;
        $orderProduct->quantity_available = 0;
        $orderProduct->quantity_no_available = 0;
        
        return $orderProduct;
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [
                [
                    'order_id',
                    'id_product',
                    'quantity_available',
                    'quantity_no_available',
                ],
                'integer',
            ],
            [['cost'], 'number'],
            [
                ['order_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Order::class,
                'targetAttribute' => ['order_id' => 'id'],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'order_id' => 'ID заказа',
            'id_product' => 'Id продукта',
            'cost' => 'Стоимость',
            'quantity_available' => 'Товар в наличии',
            'quantity_no_available' => 'Товар не в наличии',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrder() {
        return $this->hasOne(Order::class, ['id' => 'order_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProduct() {
        return $this->hasOne(Product::class, ['id' => 'id_product']);
    }
}

<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\db\ActiveQuery;

/**
 * This is the model class for table "order".
 *
 * @property integer        $id
 * @property integer        $quantity
 * @property double         $cost
 * @property integer        $status
 * @property integer        $id_dealer
 *
 * @property OrderProduct[] $orderProducts
 */
class Order extends ActiveRecord {

    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return '{{%order}}';
    }

    /**
     * @param int $dealer_id
     *
     * @return Order
     */
    public static function findActiveByDealer(int $dealer_id): ?Order
    {
        return self::findOne([
            'status' => self::STATUS_ACTIVE,
            'id_dealer' => $dealer_id,
        ]);
    }

    /**
     * @param int $dealer_id
     *
     * @return Order
     */
    public static function createByDealer(int $dealer_id): Order
    {
        $order = new self();
        $order->id_dealer = $dealer_id;
        $order->save();
        return $order;
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['quantity', 'status','id_dealer'], 'integer'],
            [['cost'], 'number'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'quantity' => 'Quantity',
            'cost' => 'Cost',
            'status' => 'Status',
            'id_dealer' => 'Id Dealer',
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getOrderProducts() {
        return $this->hasMany(OrderProduct::class, ['order_id' => 'id']);
    }
}

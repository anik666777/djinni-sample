<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "product".
 *
 * @property integer $id
 * @property integer $category_id
 * @property string $article
 * @property string $promo_status
 * @property string $image_url
 * @property string $name
 * @property string $about
 * @property double $sum_in_box
 * @property double $price_retail
 * @property double $price_trade
 * @property double $price_trade_promotion
 * @property double $quantity_in_stock
 *
 * @property ProductCategory $category
 */
class Product extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%product}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['category_id'], 'integer'],
            [['article', 'name', 'about', 'price_retail', 'price_trade'], 'required'],
            [['about'], 'string'],
            [['sum_in_box', 'price_retail', 'price_trade', 'price_trade_promotion', 'quantity_in_stock'], 'number'],
            [['article', 'promo_status', 'image_url', 'name'], 'string', 'max' => 255],
            [['category_id'], 'exist', 'skipOnError' => true, 'targetClass' => ProductCategory::className(), 'targetAttribute' => ['category_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'category_id' => 'Категория',
            'article' => 'Артикул',
            'promo_status' => 'Статус товару',
            'image_url' => 'Фото',
            'name' => 'Назва',
            'about' => 'Опис',
            'sum_in_box' => 'Упаковка',
            'price_retail' => 'РРЦ Євро',
            'price_trade' => 'РРЦ Акційна',
            'price_trade_promotion' => 'Гурт Акційна',
            'quantity_in_stock' => 'Кількість на складі',
        ];
    }


    public function getCategory()
    {
        return $this->hasOne(ProductCategory::className(), ['id' => 'category_id']);
    }

    public function gerOrderProduct(){
        return $this->hasOne(OrderProduct::className(), ['id_product' => 'id']);
    }

    public function getOrderProductWithOrder(){
        return OrderProduct::find()
            ->andWhere([
                'id_product' => $this->id,
                'order_id' => $this->getOrder()->id,
            ])
            ->one();
    }

    public function getCountInOrder(){
        $order_products = $this->getCountAvailableInOrder();
        $order_products_no_available = $this->getCountNOAvailableInOrder();
        return $order_products+$order_products_no_available;
    }


    public function getCountAvailableInOrder(){
        $order_products = $this->getOrderProductWithOrder();
        if (isset($order_products)) {
            return $order_products->quantity_available;
        }
        return 0;
    }
    public function getCountNOAvailableInOrder(){
        $order_products = $this->getOrderProductWithOrder();
        if(isset($order_products)){
            return $order_products->quantity_no_available;
        }
        return 0;
    }

    public function getSumInOrder(){
        $order_products = $this->getOrderProductWithOrder();
        if (isset($order_products)) {
            return $order_products->cost;
        }
        return 0;
    }
    public function getSumAvailableInOrder(){
        $order_products = $this->getOrderProductWithOrder();
        if (isset($order_products)) {
            return $order_products->cost/($order_products->quantity_available+$order_products->quantity_no_available)*$order_products->quantity_available;
        }
        return 0;
    }
    public function getSumNoAvailableInOrder(){
        $order_products = $this->getOrderProductWithOrder();
        if (isset($order_products)) {
            return $order_products->cost/($order_products->quantity_available+$order_products->quantity_no_available)*$order_products->quantity_no_available;
        }
        return 0;
    }

    // цена товара
    public function getDealerSale(){
        // получить скидку диллера
        return 0.7;
    }

    public  function getPriceRetailPromotion(){
        return number_format(($this->price_retail * $this->getDealerSale()), 2,'.','');
    }

    public function getPriceTradePromotion(){
        if ($this->price_trade_promotion == 0) {
            return number_format(($this->price_trade * $this->getDealerSale()), 2,'.','');
        } else {
            return $this->price_trade_promotion;
        }
    }

    /**
     * @return float
     */
    public function getRealPrice(){
        if($this->getPriceTradePromotion()==0){
            return $this->getPriceRetailPromotion();
        }else{
            return $this->getPriceTradePromotion();
        }
    }

    private function getOrder(){
        $dealer_id = Yii::$app->user->id;
        return  Order::find()->andWhere(['status' => Order::STATUS_ACTIVE,'id_dealer' => $dealer_id,])->one();
    }
}

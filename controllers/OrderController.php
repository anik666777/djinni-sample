<?php

namespace frontend\controllers;

use Yii;
use app\models\Order;
use app\models\OrderProduct;
use app\models\Product;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\HttpException;

class OrderController extends Controller {

    public function behaviors() {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'denyCallback' => function ($rule, $action) {
                            if (Yii::$app->user->isGuesеt
                                || ! Dealer::isDealerActive(Yii::$app->user->id)
                            ) {
                                return Yii::$app->response->redirect(['site/index']);
                            }
                        },
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * @param $product_id
     *
     * @return array
     * @throws HttpException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionAdd($product_id)
    {
        Yii::$app->response->format = 'json';

        $dealer_id = Yii::$app->user->id;

        $count = intval(Yii::$app->request->post('count', 1));

        $order = Order::findActiveByDealer($dealer_id);
        if(null === $order) {
            $order = Order::createByDealer($dealer_id);
        }

        /** @var Product $product */
        $product = Product::findOne($product_id);
        if(null === $product) {
            throw new HttpException(400, 'Товар не найден');
        }

        $orderProduct = $this->getOrderProduct($product_id, $order->id);
        if ($count == 0) {
            if(!$orderProduct->isNewRecord) {
                $orderProduct->delete();
            }
        } else {
            $orderProduct->quantity_available = $count;
            $orderProduct->cost = $product->getRealPrice();
            $orderProduct->save();
        }
        $product->save();

        return ['quantity_in_stock' => $orderProduct->quantity_available];
    }

    /**
     * @param $id_product
     * @param $order_id
     *
     * @return OrderProduct|null
     */
    public function getOrderProduct($id_product, $order_id): ?OrderProduct
    {
        $orderProduct = OrderProduct::findOne([
            'id_product' => $id_product,
            'order_id' => $order_id,
        ]);
        if (null === $orderProduct) {
            $orderProduct = OrderProduct::create($id_product, $order_id);
        }
        return $orderProduct;
    }
}
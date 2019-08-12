<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\db\Query;

/**
 * SearchProduct represents the model behind the search form about `common\models\Product`.
 */
class SearchProduct extends Product
{
    public $categoryName;
    public $inStock;
    public $sale;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'category_id'], 'integer'],
            [
                [
                    'article',
                    'promo_status',
                    'image_url',
                    'name',
                    'about',
                    'categoryName',
                    'sale',
                    'inStock',
                ],
                'safe',
            ],
            [
                [
                    'sum_in_box',
                    'price_retail',
                    'price_trade',
                    'price_trade_promotion',
                    'quantity_in_stock',
                ],
                'number',
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Product::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if ( ! $this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'category_id' => $this->category_id,
            'sum_in_box' => $this->sum_in_box,
            'price_retail' => $this->price_retail,
            'price_trade' => $this->price_trade,
            'price_trade_promotion' => $this->price_trade_promotion,
            'quantity_in_stock' => $this->quantity_in_stock,
        ]);
        $query->andFilterWhere(['like', 'article', $this->article])
            ->andFilterWhere(['like', 'promo_status', $this->promo_status])
            ->andFilterWhere(['like', 'image_url', $this->image_url]);

        if ($this->about) {
            $query->andWhere(new Expression('MATCH (name, about) AGAINST (:about_name)', ['about_name' => $this->about,]));
        }
        if($this->sale=='true'){
            $query->andWhere(['not in','price_trade',0]);
        }
        if($this->inStock=='true'){
            $query->andWhere(['not in','quantity_in_stock',0]);
        }
        return $dataProvider;
    }
}


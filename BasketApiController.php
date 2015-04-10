<?php
/**
 * Created by PhpStorm.
 * User: drxwat
 * Date: 16.02.15
 * Time: 14:21
 */

namespace frankyball\basket;


use frankyball\basket\BasketComponent;
use yii\web\Controller;

class BasketApiController extends Controller{

    public function actionGetTotals(){
        if(\Yii::$app->request->isAjax){
            /**
             * @var BasketComponent $basket
             */
            $basket = \Yii::$app->basket;
            $response = [
                'totalAmount' => $basket->getTotalAmount(),
                'totalPrice' => $basket->getTotalPrice(),
                'totalItemsAmount' => $basket->getTotalItemsAmount()
            ];
            \Yii::$app->response->format = 'json';
            return $response;
        }
    }

    public function actionIncrementItem(){
        if(\Yii::$app->request->isAjax && \Yii::$app->request->isPost){
            /**
             * @var BasketComponent $basket
             */
            $basket = \Yii::$app->basket;
            $product_id = \Yii::$app->request->post('id');

            \Yii::$app->response->format = 'json';
            if(is_numeric($product_id)){
                $basket->incrementItemAmount($product_id);
            }

            $item = $basket->getItemById($product_id);
            return [
                'amount' => $item->getAmount(),
                'price' => $item->getAmount() * $item->getPrice()
            ];
        }
    }

    public function actionDecrementItem(){
        if(\Yii::$app->request->isAjax && \Yii::$app->request->isPost){
            /**
             * @var BasketComponent $basket
             */
            $basket = \Yii::$app->basket;
            $product_id = \Yii::$app->request->post('id');

            \Yii::$app->response->format = 'json';
            if(is_numeric($product_id)){
                $basket->decrementItemAmount($product_id);
            }

            $item = $basket->getItemById($product_id);
            return [
                'amount' => $item->getAmount(),
                'price' => $item->getAmount() * $item->getPrice()
            ];
        }
    }

    public function actionChangeItemAmount(){
        if(\Yii::$app->request->isAjax && \Yii::$app->request->isPost){
            /**
             * @var BasketComponent $basket
             */
            $basket = \Yii::$app->basket;
            $product_id = (int)\Yii::$app->request->post('id');
            $new_value = (int)\Yii::$app->request->post('value');

            if(is_numeric($product_id) && is_numeric($new_value) && $basket->hasItemById($product_id) !== false){
                $basket->setItemAmount($product_id, $new_value);
            }

            \Yii::$app->response->format = 'json';

            $item = $basket->getItemById($product_id);
            return [
                'amount' => $item->getAmount(),
                'price' => $item->getAmount() * $item->getPrice()
            ];

        }
    }

}
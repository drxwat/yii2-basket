<?php
/**
 * Created by PhpStorm.
 * User: drxwat
 * Date: 11.02.15
 * Time: 10:45
 */

namespace frankyball\basket;

use yii\base\Object;
use yii\web\Cookie;

/**
 * TODO: Реализовать работу с БД через ORM / в конфиг указать какой компонент БД использовать
 * TODO: Запилить миграцию
 * Class BasketComponent
 * @package drxwat\basket
 */
class BasketComponent extends Object {

    /**
     * @var float
     */
    private $_total_price = 0.00;
    /**
     * @var int
     */
    private $_total_amount = 0;
    /**
     * @var array
     */
    private $_items = [];

    /**
     * @var array
     */
    private $_basket = [];


    public function init(){
        $request = \Yii::$app->request;
        if($request->cookies->has('basket')){
            $basket = \Yii::$app->request->cookies->get('basket')->value;

            $total_price = (isset($basket['total_price']) && !empty($basket['total_price'])) ? $basket['total_price'] : 0.00;
            $total_amount = (isset($basket['total_amount']) && !empty($basket['total_amount'])) ? $basket['total_amount'] : 0;
            $items = (isset($basket['items']) && !empty($basket['items'])) ? $basket['items'] : [];

            $this->setTotalAmount($total_amount);
            $this->setTotalPrice($total_price);
            $this->setItems($items);

        }
    }


    /**
     * @return int
     */
    public function getTotalPrice(){
        return $this->_total_price;
    }

    /**
     * @param int $total_price
     */
    private function setTotalPrice($total_price){
        if(is_numeric($total_price)){
            $this->_total_price = floatval($total_price);
        }
        //$this->fixChanges();
    }

    /**
     * @return int
     */
    public function getTotalAmount(){
        return $this->_total_amount;
    }

    /**
     * @param int $total_amount
     */
    private function setTotalAmount($total_amount){
        if(is_numeric($total_amount)){
            $this->_total_amount = (int)$total_amount;
        }
        //$this->fixChanges();
    }

    /**
     * @return BasketItem[]
     */
    public function getItems(){
        return $this->_items;
    }

    /**
     * @param $key
     * @return BasketItem|bool
     */
    private function getItemByKey($key){
        /**
         * @var BasketItem[] $items
         */
        $items = $this->getItems();
        if(isset($items[$key])){
            return $items[$key];
        }else{
            return false;
        }
    }

    /**
     * @param array $items
     */
    private function setItems(array $items){
        $this->_items = $items;
        $this->fixChanges();
    }


    /**
     *
     */
    private function fixChanges(){
        $total_price = 0.00;
        $total_amount = 0;
        foreach ($this->getItems() as $key => $item) {
            /**
             * @var BasketItem $item
             */
            $total_price += $item->getPrice() * $item->getAmount();
            $total_amount++;
        }

        $this->setTotalAmount($total_amount);
        $this->setTotalPrice($total_price);

        /**
         * TODO: Убрать total_price, total_amount | В конструкторе расчитывать их по уму
         */
        $this->_basket = [
                'total_price' => $this->getTotalPrice(),
                'total_amount' => $this->getTotalAmount(),
                'items' => $this->getItems()
        ];

        $cookie = [
            'name' => 'basket',
            'value' => $this->_basket,
        ];
        $basket_cookie = new Cookie($cookie);
        \Yii::$app->response->cookies->remove('basket');
        \Yii::$app->response->cookies->add($basket_cookie);
    }

    /**
     * Обязательно сделай проверку на соответствие false (===)
     * Может вернуть 0 как индекс массива
     * @param BasketItem $item
     * @return bool|int
     */
    private function hasItem(BasketItem $item){

        /**
         * @var BasketItem $basket_item
         */
        foreach($this->getItems() as $key => $basket_item){
            if($item->getId() === $basket_item->getId()){
                return (int)$key;
            }
        }
        return false;
    }

    /**
     * @param $id
     * @return bool|int
     */
    public function hasItemById($id){
        foreach ($this->getItems() as $key => $basket_item) {
            if((int)$id === $basket_item->getId()){
                return (int)$key;
            }
        }
        return false;

    }


    /**
     * @param $id
     * @param $price
     * @param $amount
     * Добавить элемент в корзину. Если элемент с id уже существует засеняем его
     */
    public function addItem($id, $price, $amount){
        $item = new BasketItem($id, $price, $amount);
        $item_key = $this->hasItem($item);
        $items = $this->getItems();
        // Добавляем
        if($item_key === false){
            $items[] = $item;
            $this->setItems($items);
        }else{ // Заменяем
            // Сравниваем объекты, если разные подменяем на тоже место
            $current_basket_item = $this->getItemByKey($item_key);
            if($current_basket_item instanceof BasketItem && !$item->equalsTo($current_basket_item)){
                $items[$item_key] = $item;
                $this->setItems($items);
            }
        }
    }


    /**
     * @param $id
     * Удаляем элемент из корзины по его id
     */
    public function removeItem($id){
        // Вот такие вот костыли из-за отсутствия перегрузки методов и конструкторов в языке
        $item_key = $this->hasItemById($id);
        if($item_key !== false){
            $items = $this->getItems();
            unset($items[$item_key]);
            $this->setItems($items);
        }
    }

    /**
     * @param $id
     * Увеличиваем "количество" элемента корзины на 1, но не более чем до макксимально возможного
     * Максимально возможное хранит объект элемента
     */
    public function incrementItemAmount($id){
        $item_key = $this->hasItemById($id);
        if($item_key !== false){
            $item = $this->getItemByKey($item_key);
            $new_amount = is_numeric($item->getAmount()) ? $item->getAmount() + 1 : 0;
            $item->setAmount($new_amount);
            // Заменяем
            $items = $this->getItems();
            $items[$item_key] = $item;
            $this->setItems($items);
        }
    }

    /**
     * @param $id
     * Уменьшаем "количество" элемента корзины на 1 но не менее 1
     *
     */
    public function decrementItemAmount($id){
        $item_key = $this->hasItemById($id);
        if($item_key !== false){
            $item = $this->getItemByKey($item_key);
            $new_amount = $item->getAmount() - 1 > 0 ? $item->getAmount() - 1 : 1;
            $item->setAmount($new_amount);
            // Заменяем
            $items = $this->getItems();
            $items[$item_key] = $item;
            $this->setItems($items);
        }
    }

    /**
     * @param $id
     * @param $amount
     * Устанавливаем определенное "количество" элемента
     */
    public function setItemAmount($id, $amount){
        $item_key = $this->hasItemById($id);
        if($item_key !== false){
            $item = $this->getItemByKey($item_key);
            $item->setAmount((int)$amount);

            $items = $this->getItems();
            $items[$item_key] = $item;
            $this->setItems($items);

        }
    }

    /**
     * @param $id
     * @return BasketItem|null
     */
    public function getItemById($id){
        $item_key = $this->hasItemById($id);
        if($item_key !== false){
            /**
             * @var BasketItem $item
             */
            $item = $this->_items[$item_key];
            return $item;
        }else{
            return NULL;
        }
    }

    public function cleanOut(){
        \Yii::$app->response->cookies->remove('basket');
        $this->setItems([]);
        $this->setTotalPrice(0.00);
        $this->setTotalAmount(0);
        \Yii::$app->response->cookies->add(new Cookie(
            [
                'name' => 'basket',
                'value' => $this->_basket
            ]
        ));
    }

    public function isEmpty(){
        if(!\Yii::$app->request->cookies->has('basket')){
            return true;
        }elseif(count($this->getItems()) === 0){
            return true;
        } else{
            return false;
        }
    }

    public function getIds(){
        $ids = [];
        if(!$this->isEmpty()){
            foreach($this->getItems() as $item){
                $ids[] = $item->getId();
            }
        }
        return $ids;
    }

    public function getTotalItemsAmount(){
        $total = 0;
        foreach($this->getItems() as $item){
            $total += $item->getAmount();
        }
        return $total;
    }

}
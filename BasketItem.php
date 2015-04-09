<?php
/**
 * Created by PhpStorm.
 * User: drxwat
 * Date: 11.02.15
 * Time: 11:18
 */

namespace drxwat\basket;

/**
 * TODO: Добавить указания типа объекта className например и выборки по типу
 * Class BasketItem
 * @package app\components\util
 */
class BasketItem {

    /**
     * @param $id
     * @param $price
     * @param $amount
     */
    public function __construct($id, $price, $amount){
        $this->setId((int)$id);
        $this->setPrice(floatval($price));
        $this->setAmount((int)$amount);
    }

    /**
     * @var
     */
    private $id;

    /**
     * @var float
     */
    private $price = 0.00;

    /**
     * @var int
     */
    private $amount = 0;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param float $price
     */
    public function setPrice($price)
    {
        $this->price = floatval($price);
    }

    /**
     * @return int
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param int $amount
     */
    public function setAmount($amount)
    {
        if($this->getMaxAmount() !== NULL && $this->getMaxAmount() > 0 && $amount > $this->getMaxAmount()){
            $this->amount = (int)$this->getMaxAmount();
        }else{
            $this->amount = (int)$amount;
        }

    }

    /**
     * @param BasketItem $item
     * @return bool
     */
    public function equalsTo(BasketItem $item){
        if(
            $this->getId() === $item->getId() &&
            $this->getAmount() === $item->getAmount() &&
            $this->getPrice() === $item->getPrice()
        ){
            return true;
        }else{
            return false;
        }
    }
}
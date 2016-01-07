<?php
namespace PhalconRest\Models;
class Inquiries extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     */
    public $id;

    /**
     *
     * @var integer
     */
    public $heart_rate;

    /**
     *
     * @var double
     */
    public $body_temp;

    /**
     *
     * @var integer
     */
    public $respiration_rate;

    /**
     *
     * @var string
     */
    public $description;

    /**
     *
     * @var string
     */
    public $video;

    /**
     *
     * @var integer
     */
    public $dr_id;

    /**
     *
     * @var integer
     */
    public $farmer_id;


    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->belongsTo('dr_id', 'Users', 'id', array('alias' => 'Users'));
        $this->belongsTo('farmer_id', 'Users', 'id', array('alias' => 'Users'));
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'inquiries';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Inquiries[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Inquiries
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}

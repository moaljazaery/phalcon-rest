<?php
namespace PhalconRest\Models;
class Comments extends \Phalcon\Mvc\Model
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
    public $inquiry_id;

    /**
     *
     * @var integer
     */
    public $user_id;


    /**
     *
     * @var string
     */
    public $text;

    /**
     *
     * @var integer
     */
    public $unread_flag;


    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->belongsTo('user_id', 'Users', 'id', array('alias' => 'Users'));
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'comments';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Comments[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Comments
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}

<?php
namespace PhalconRest\Models;
use Phalcon\Mvc\Model\Validator\Email as Email;
use Phalcon\Mvc\Model\Validator\Uniqueness as UniquenessValidator;

class Users extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     */
    public $id;

    /**
     *
     * @var string
     */
    public $full_name;

    /**
     *
     * @var string
     */
    public $email;

    /**
     *
     * @var string
     */
    public $address;

    /**
     *
     * @var string
     */
    public $user_type;

    /**
     *
     * @var string
     */
    public $phone;

    /**
     *
     * @var string
     */
    public $password;

    /**
     *
     * @var string
     */
    public $created_at;

    /**
     *
     * @var string
     */
    public $modified_in;

    public function beforeCreate()
    {
        // Set the creation date
        $this->created_at = date('Y-m-d H:i:s');

    }

    public function beforeUpdate()
    {
        // Set the modification date
        $this->modified_in = date('Y-m-d H:i:s');
        if($this->hasChanged('password'))
            $this->password = md5($this->password);
    }

    /**
     * Validations and business logic
     *
     * @return boolean
     */
    public function validation()
    {
        $this->validate(
            new Email(
                array(
                    'field'    => 'email',
                    'required' => true,
                )
            )
        );
        if(!isset($this->id) || $this->hasChanged('email') )
        {
            $this->validate(new UniquenessValidator(array(
                'field' => 'email',
                'message' => 'Sorry, The email was registered by another user'
            )));
        }


        $this->validate(
            new \Phalcon\Mvc\Model\Validator\Regex(
                array(
                    'field'=>'phone',
                    'message' => 'Check phone formula',
                    'pattern' => '/[0-9]+/'
                )
            )
        );
        $this->validate(
            new \Phalcon\Mvc\Model\Validator\StringLength(
                array(
                    'field'=>'phone',
                    'minimumMessage' => 'The telephone is too short',
                    'min' => 8
                )
            )
        );


        if ($this->validationHasFailed() == true) {
            return false;
        }

        return true;
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->keepSnapshots(true);
        $this->hasMany('id', 'Comments', 'user_id', array('alias' => 'Comments'));
        $this->hasMany('id', 'Inquiries', 'dr_id', array('alias' => 'Inquiries'));
        $this->hasMany('id', 'Inquiries', 'farmer_id', array('alias' => 'Inquiries'));
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'users';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Users[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Users
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}

<?php
namespace App\Helpers;
use Validator;
use App\Helpers\Response as Response;
use Hash;
/* 
 * @author eddymens <eddymens@devless.io>
*composed of most common used classes and functions 
*/

class Helper
{
    /**
     * application error heap
     * @var type 
     */
    public static  $ERROR_HEAP = 
    [
        #JSON HEAP
        400 => 'Sorry something went wrong with payload(check json format)',
        #SCHEMA HEAP
        500 => 'first schema error',
        # error code for custom messages 
        600 => 'Data type does not exist',
        601 => 'reference column column name does not exist',
        602 => 'database schema could not be created',
        603 => 'table could not be created',
        604  =>'service resource does not exist or is not active',
        605 => 'no such service type try (script or db)',
        606 => 'created database Schema successfully',
        607 => 'could not find the right DB method',
        608 => 'request method not supported',
        609 => 'data has been added to table successfully',
        610 => 'query paramter does not exist',
        611 => 'table name is not set',
        612 => 'query parameters not set',
        613 => 'dropped table succefully',
        614 => 'parameters where or data  not set',
        615 => 'delete action not set',   
        616 => 'caught unknown data type',
        617 =>  'no such table belongs to the service',
        618 =>  'validator type does not exist',
        619 =>  'table was updated successfully',
        700 => 'internal system error',
    ];
    
    /**
     * convert soft types to validator rules 
     * @var string 
     */
    private static $validator_type = 
    [
        'text'      => 'string',
        'textarea'   => 'string',
        'integer'    => 'integer',
        'money'      => 'numeric',
        'password'   => 'alphanum',
        'percentage' => 'integer',
        'url'        => 'url',
        'timestamp'  => 'timestamp',
        'boolean'    => 'boolean',
        'email'      => 'email',
        'reference'  => 'integer',    
    ];
    /**
     * fetch message based on error code 
    * @param  stack  $stack
    * @return string or null  
    **/
    public static function error_message($stack)
    {
        if(isset(self::$ERROR_HEAP[$stack]))
            return self::$ERROR_HEAP[$stack];
        else
            {
              return null;
            }
    }
    
    /**
     * stops request processing and returns error payload 
     *
     * @param  error code  $stack
     * @return json 
     */
    public static function  interrupt($stack, $message=null){
        if($message !==null){
            $msg = $message;
        }
        else
        {
            $msg = self::error_message($stack);
        }
        $response = Response::respond($stack, $msg, []); 
         
         die($response);
    }
    
     /**
     * check the validility of a field type
     * uses laravel validator 
     * @param string   $field_value
     * @param string parameters to check against $check_against
     * @return boolean 
     */
    public static function field_check( $field_value, $check_against)
    {   
        //convert check against to field_name for err_msg
        $field_name = $check_against;
        
        //check if multiple rules are used 
        if(strpos($check_against, '|'))
        {
            $rules = explode("|", $check_against);

            foreach($rules as $rule)
            {
                //convert each rule and re-combine
                if(!isset(Helper::$validator_type[$rule]))
                {
                    Helper::interrupt(618,'validator type '.$rule.
                            ' does not exist');
                }
                $check_against = Helper::$validator_type[$rule]."|" ;
            }
        }
        else
        {
            
            //single validator rule  
            $check_against = Helper::$validator_type[$check_against] ;
        
        }
                
        
        $state = Validator::make(
            [$field_name => $field_value],
                [$field_name => $check_against]
        );
        if(!$state->fails()){
            return TRUE;
        }
        else
        {
            return $state->messages();
        }
    }
    
     /**
     * get url parameters 
     * @return array 
     **/
    public static function query_string()
    {
        if(isset( $_SERVER['QUERY_STRING'])){
         $query  = explode('&', $_SERVER['QUERY_STRING']);
         $params = array();
        
        foreach( $query as $param )
            {
             
              list($name, $value) = explode('=', $param, 2);
              $params[urldecode($name)][] = urldecode($value);
            }
            return $params;
        }
        else
        {
            $param = "";
            return $param;
        }
    }
    
    
    /**
     * Hash password
     * @param type $password
     * @param type $hash
     * @param array $rules
     * @return string
     */
    public static function password_hash($password)
    {
        return Hash::make($password);
    }
    
    /**
     * compare password hash
     * @param string $user_input
     * @param string $hash
     * @return boolean
     */
    public static function compare_hash($user_input, $hash)
    {
        (Hash::check($user_input, $hash))?  true :  false;
    }
}
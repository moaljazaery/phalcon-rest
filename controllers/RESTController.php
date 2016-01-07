<?php
namespace PhalconRest\Controllers;
use Phalcon\Paginator\Adapter\Model;
use \PhalconRest\Exceptions\HTTPException;

/**
 * Base RESTful Controller.
 * Supports queries with the following paramters:
 *   Searching:
 *     q=(searchField1:value1,searchField2:value2)
 *   Partial Responses:
 *     fields=(field1,field2,field3)
 *   Limits:
 *     limit=10
 *   Partials:
 *     offset=20
 *
 */
Abstract class RESTController extends \PhalconRest\Controllers\BaseController{

	/**
	 * If query string contains 'q' parameter.
	 * This indicates the request is searching an entity
	 * @var boolean
	 */
	protected $isSearch = false;

	/**
	 * If query contains 'fields' parameter.
	 * This indicates the request wants back only certain fields from a record
	 * @var boolean
	 */
	protected $isPartial = false;

	/**
	 * Set when there is a 'limit' query parameter
	 * @var integer
	 */
	protected $limit = null;

	/**
	 * Set when there is an 'offset' query parameter
	 * @var integer
	 */
	protected $offset = null;

	/**
	 * Array of fields requested to be searched against
	 * @var array
	 */
	protected $searchFields = null;

	/**
	 * Array of fields requested to be returned
	 * @var array
	 */
	protected $partialFields = null;

	/**
	 * Array of fields requested to be excluded
	 * @var array
	 */
	protected $excludeFields = null;

	/**
	 * Sets which fields may be searched against, and which fields are allowed to be returned in
	 * partial responses.  This will be overridden in child Controllers that support searching
	 * and partial responses.
	 * @var array
	 */
	protected $allowedFields = array(
		'search' => array(),
		'partials' => array()
	);

	/**
	 * Model Field
	 * @var array
	 */
	protected $modelFields = array('full_name','email','address','user_type','phone','created_at');

	/**
	 * @var \Phalcon\Mvc\Model
	 */
	protected $model;




	/**
	 * Constructor, calls the parse method for the query string by default.
	 * @param boolean $parseQueryString true Can be set to false if a controller needs to be called
	 *        from a different controller, bypassing the $allowedFields parse
	 * @return void
	 */
	public function __construct($parseQueryString = true){
		parent::__construct();
		if ($parseQueryString){
			$this->parseRequest($this->allowedFields);
		}
		$this->model=$this->getModel();
		$metaData = new \Phalcon\Mvc\Model\MetaData\Memory();
		$this->modelFields = $metaData->getAttributes($this->model);


		return;
	}

	abstract public function getModel();

	/**
	 * Parses out the search parameters from a request.
	 * Unparsed, they will look like this:
	 *    (name:Benjamin Framklin,location:Philadelphia)
	 * Parsed:
	 *     array('name'=>'Benjamin Franklin', 'location'=>'Philadelphia')
	 * @param  string $unparsed Unparsed search string
	 * @return array            An array of fieldname=>value search parameters
	 */
	protected function parseSearchParameters($unparsed){

		// Strip parens that come with the request string
		$unparsed = trim($unparsed, '()');

		// Now we have an array of "key:value" strings.
		$splitFields = explode(',', $unparsed);
		$mapped = array();

		// Split the strings at their colon, set left to key, and right to value.
		foreach ($splitFields as $field) {
			$splitField = explode(':', $field);
			$mapped[$splitField[0]] = $splitField[1];
		}

		return $mapped;
	}


	private function array_remove_keys($array, $keys = array()) {

		// If array is empty or not an array at all, don't bother
		// doing anything else.
		if(empty($array) || (! is_array($array))) {
			return $array;
		}

		// At this point if $keys is not an array, we can't do anything with it.
		if(! is_array($keys)) {
			return $array;
		}

		// array_diff_key() expected an associative array.
		$assocKeys = array();
		foreach($keys as $key) {
			$assocKeys[$key] = true;
		}

		return array_diff_key($array, $assocKeys);
	}


	/**
	 * Parses out partial fields to return in the response.
	 * Unparsed:
	 *     (id,name,location)
	 * Parsed:
	 *     array('id', 'name', 'location')
	 * @param  string $unparsed Unparsed string of fields to return in partial response
	 * @return array            Array of fields to return in partial response
	 */
	protected function parsePartialFields($unparsed){
		return explode(',', trim($unparsed, '()'));
	}

	/**
	 * Main method for parsing a query string.
	 * Finds search paramters, partial response fields, limits, and offsets.
	 * Sets Controller fields for these variables.
	 *
	 * @param  array $allowedFields Allowed fields array for search and partials
	 * @return boolean              Always true if no exception is thrown
	 */
	protected function parseRequest($allowedFields){
		$request = $this->di->get('request');
		$searchParams = $request->get('q', null, null);
		$fields = $request->get('fields', null, null);

		// Set limits and offset, elsewise allow them to have defaults set in the Controller
		$this->limit = ($request->get('limit', null, null)) ?: $this->limit;
		$this->offset = ($request->get('offset', null, null)) ?: $this->offset;

		// If there's a 'q' parameter, parse the fields, then determine that all the fields in the search
		// are allowed to be searched from $allowedFields['search']
		if($searchParams){
			$this->isSearch = true;
			$this->searchFields = $this->parseSearchParameters($searchParams);

			// This handly snippet determines if searchFields is a strict subset of allowedFields['search']
			if(array_diff(array_keys($this->searchFields), $this->allowedFields['search'])){
				throw new HTTPException(
					"The fields you specified cannot be searched.",
					401,
					array(
						'dev' => 'You requested to search fields that are not available to be searched.',
						'internalCode' => 'S1000',
						'more' => '' // Could have link to documentation here.
				));
			}
		}

		// If there's a 'fields' paramter, this is a partial request.  Ensures all the requested fields
		// are allowed in partial responses.
		if($fields){
			$this->isPartial = true;
			$this->partialFields = $this->parsePartialFields($fields);

			// Determines if fields is a strict subset of allowed fields
			if(array_diff($this->partialFields, $this->allowedFields['partials'])){
				throw new HTTPException(
					"The fields you asked for cannot be returned.",
					401,
					array(
						'dev' => 'You requested to return fields that are not available to be returned in partial responses.',
						'internalCode' => 'P1000',
						'more' => '' // Could have link to documentation here.
				));
			}
		}

		return true;
	}

	/**
	 * Provides a base CORS policy for routes like '/users' that represent a Resource's base url
	 * Origin is allowed from all urls.  Setting it here using the Origin header from the request
	 * allows multiple Origins to be served.  It is done this way instead of with a wildcard '*'
	 * because wildcard requests are not supported when a request needs credentials.
	 *
	 * @return true
	 */
	public function optionsBase(){
		$response = $this->di->get('response');
		$response->setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS, HEAD');
		$response->setHeader('Access-Control-Allow-Origin', $this->di->get('request')->header('Origin'));
		$response->setHeader('Access-Control-Allow-Credentials', 'true');
		$response->setHeader('Access-Control-Allow-Headers', "origin, x-requested-with, content-type");
		$response->setHeader('Access-Control-Max-Age', '86400');
		return true;
	}

	/**
	 * Provides a CORS policy for routes like '/users/123' that represent a specific resource
	 *
	 * @return true
	 */
	public function optionsOne(){
		$response = $this->di->get('response');
		$response->setHeader('Access-Control-Allow-Methods', 'GET, PUT, PATCH, DELETE, OPTIONS, HEAD');
		$response->setHeader('Access-Control-Allow-Origin', $this->di->get('request')->header('Origin'));
		$response->setHeader('Access-Control-Allow-Credentials', 'true');
		$response->setHeader('Access-Control-Allow-Headers', "origin, x-requested-with, content-type");
		$response->setHeader('Access-Control-Max-Age', '86400');
		return true;
	}

	/**
	 * Should be called by methods in the controllers that need to output results to the HTTP Response.
	 * Ensures that arrays conform to the patterns required by the Response objects.
	 *
	 * @param  array $recordsArray Array of records to format as return output
	 * @return array               Output array.  If there are records (even 1), every record will be an array ex: array(array('id'=>1),array('id'=>2))
	 */
	protected function respond($recordsArray){
		// No records returned, so return an empty array
		if(count($recordsArray) < 1){
			return array();
		}
		$results=array($recordsArray);
		$results=$this->filter($results);
		return $results;
	}


	public function get($id){
		$record=$this->model->findFirst($id);
		if($record)
			return $this->respond($record->toArray());
		else
			throw new HTTPException('Not Found',HTTPException::HTTP_NOT_FOUND);

	}

	public function getList(){
		if($this->isSearch){
			$results = $this->search($this->model->find());
		} else {
			$results = $this->model->find();
		}

		return $this->respond($results);
	}

	public function search($records){

		$results = array();
		if(count($records))
		{
			foreach($records as $record){
				$record=$record->toArray();
				$match = true;
				if(count($this->searchFields))
				{
					foreach ($this->searchFields as $field => $value) {
						if(!(strpos($record[$field], $value) !== FALSE)){
							$match = false;
						}
					}

				}
				if($match){
					$results[] = $record;
				}

			}

		}
		return $results;
	}

	public function filter($results){
		if($this->excludeFields){

			foreach($results as $record){
				$newResults[] = $this->array_remove_keys($record, $this->excludeFields);
			}
			$results = $newResults;
		}
		if($this->isPartial){
			$newResults = array();
			$remove = array_diff(array_keys($this->$results[0]), $this->partialFields);
			foreach($results as $record){
				$newResults[] = $this->array_remove_keys($record, $remove);
			}
			$results = $newResults;
		}
		if($this->offset){
			$results = array_slice($results, $this->offset);
		}
		if($this->limit){
			$results = array_slice($results, 0, $this->limit);
		}
		return $results;
	}


	/**
	 * Creates a new user
	 */
	public function create()
	{

		if($this->modelFields){
			foreach($this->modelFields as $field)
			{
				$this->model->$field=$this->request->getPost($field);
			}
		}

		if (!$this->model->save()) {
			foreach ($this->model->getMessages() as $message) {

				throw new HTTPException($message,HTTPException::HTTP_BAD_REQUEST);
			}
		}else{
			throw new HTTPException('Successfully Created',HTTPException::HTTP_CREATED);
		}

	}

	public function edit($id)
	{
			$modelRecord = $this->model->findFirst($id);
			if (!$modelRecord) {
				throw new HTTPException("Not found",HTTPException::HTTP_NOT_FOUND);
			}else
			{
				if($this->modelFields){
					foreach($this->modelFields as $field)
					{
						$requestParam=$this->request->getPut($field);
						if(isset($requestParam))
							$modelRecord->$field=$requestParam;
					}
				}
			}
			if (!$modelRecord->save()) {
				foreach ($modelRecord->getMessages() as $message) {

					throw new HTTPException($message,HTTPException::HTTP_BAD_REQUEST);
				}
			}else{
				throw new HTTPException('Successfully Updated',HTTPException::HTTP_OK);
			}
	}


	public function delete($id)
	{
		$modelRecord = $this->model->findFirst($id);
		if (!$modelRecord) {
			throw new HTTPException("Not found",HTTPException::HTTP_NOT_FOUND);
		}

		if (!$modelRecord->delete()) {

			foreach ($modelRecord->getMessages() as $message) {
				throw new HTTPException($message,HTTPException::HTTP_BAD_REQUEST);
			}
		}
		throw new HTTPException('Successfully deleted',HTTPException::HTTP_OK);
	}
}
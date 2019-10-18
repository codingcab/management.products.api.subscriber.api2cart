<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class Api2Cart extends Controller
{
    protected $params = [];
    protected $uri = '';
    protected $uri_part1 = '';
    protected $uri_part2 = '';
    protected $uri_part3 = 'json';
    protected $name = 'API2CART';

    public $lastResponse;
    public $lastResponse_body;


    private $guzzle;
    private $base_uri = 'https://api.api2cart.com/v1.1/';
    private $api_key = '';
    private $store_key = '';

    private $count = 10;


    public $dumpNow = false;

    const RESPONSE_CODE_OK = 0;
    const RESPONSE_CODE_ENTITY_ALREADY_EXISTS = 113;
    const RESPONSE_CODE_STORE_NOT_ASSIGNED = 109;

    public function dumpAll() {
        if($this->dumpNow) {

            dd($this->jsonResponse());
            dd($this);
            $this->dumpNow = false;
        }
    }

    public function __construct($api_key, $store_key)
    {

        $this->api_key = $api_key;
        $this->store_key = $store_key;

        $this->resetParams();

        $this->guzzle = new Client([
            'base_uri' =>  $this->base_uri,
            'timeout' => 60,
            'exceptions' => true,
        ]);
    }

    public function getParams()
    {
        return $this->params;
    }

    public function resetParams()
    {
        $this->params = [
            'count' => $this->count,
        ];

        return $this;
    }

    private function guzzle()
    {
        return $this->guzzle;
    }

    /**
     * @param $number
     * @param string $maskingCharacter
     * @return string
     */
    function ccMasking($number, $maskingCharacter = 'X')
    {
        if (strlen($number) < 9) {
            return $number;
        }

        return substr($number, 0, 4) . str_repeat($maskingCharacter, strlen($number) - 8) . substr($number, -4);
    }

    public function get()
    {
        $this->addApiKeysToParams();

        $this->logApiCallDetails();

        $this->lastResponse = $this->guzzle()->get($this->uri,[ 'query' => $this->getParams()]);

        $this->handleResponse();

        return $this->jsonResponse();
    }

    /**
     * @param $data
     * @return  boolean
     */
    public function post($data)
    {
        $this->addApiKeysToParams();

        $this->logPOSTCallDetails($data);

        $this->lastResponse = $this->guzzle()->post($this->uri, ['query' => $this->getParams(), 'json' => $data]);

        $this->handleResponse();

        if (($this->lastResponse->getStatusCode() == 200) AND ($this->return_code() == 0)) {
            return true;
        }

        return false;
    }

    public function jsonResponse()
    {
        // used casting because PSR-7
        // https://stackoverflow.com/questions/30549226/guzzlehttp-how-get-the-body-of-a-response-from-guzzle-6
        $content = $this->lastResponse_body;//->getBody()->getContents();

        $json_response =  json_decode($content, true);

        return $json_response;
    }

    public function return_code()
    {
        return $this->jsonResponse()['return_code'];
    }

    public function return_message()
    {
        return $this->jsonResponse()['return_message'];
    }

    public function setUri($newURI)
    {
        $this->uri = $newURI;

        return $this;
    }

    public function updateUri()
    {
        return $this->setUri($this->uri_part1.'.'.$this->uri_part2.'.'.$this->uri_part3);
    }

    public function setUriPart1($newPart1)
    {
        $this->uri_part1 = $newPart1;

        return $this->updateUri();
    }

    public function setUriPart2($newPart2)
    {
        $this->uri_part2 = $newPart2;

        return $this->updateUri();
    }



    public function orders()
    {
        return $this->setUriPart1('order');
    }

    public function product()
    {
        return $this->setUriPart1('product');
    }

    private function updateProductSimple($element)
    {
        $newElement = $element;
        $newElement["id"] = $this->findID("model", $element["sku"]);

        // early exit if simple product does not exists
        if ( isset($newElement["id"]) == false ) {
            return false;
        }

        // we are not sku, remove it from data passed
        unset($newElement["sku"]);

        // POST update
        if($this->product()->update($newElement)) {
            return true;
        };

        // handle exceptions
        switch ($this->return_code()) {

            case 109: // store X not assigned
                if($this->store_assign($newElement["id"], $newElement["store_id"])) {
                    return $this->updateProductSimple($element);
                }
                $this->tryToHandleLastResponseOrException();
                break;

            default:
                $this->tryToHandleLastResponseOrException();
                break;
        }

        return false;
    }

    private function tryToHandleLastResponseOrException(){

        switch ($this->return_code()) {

            case 105:
                // Store access denied or bridge not responding. Please, add this IP (144.76.201.51) into the white list
                // connection issue, no need to do anything, already logged in handleResponse() function
                break;

            default:
                throw new \Exception("api2cart: Error(".$this->return_code()."): ".$this->return_message());

        }

    }

    /**
     * @param $data
     * @return bool
     * @throws \Exception
     */
    public function updateProduct($data)
    {
        $newElement = [];
        $newElement = $this->array_add_if_exists($data, $newElement, "sku");
        $newElement = $this->array_add_if_exists($data, $newElement, "store_id");
        $newElement = $this->array_add_if_exists($data, $newElement, "price");

        if (Arr::has($data, "quantity")) {
            $newElement["quantity"] = intval($data["quantity"]);
        }

        $newElement = $this->array_add_if_exists($data, $newElement, "sale_price", "special_price");
        $newElement = $this->array_add_if_exists($data, $newElement, "sale_price_start_date","sprice_create");
        $newElement = $this->array_add_if_exists($data, $newElement, "sale_price_end_date", "sprice_expire");
        $newElement = $this->array_add_if_exists($data, $newElement, "in_stock");
        $newElement = $this->array_add_if_exists($data, $newElement, "weight");


        if ($this->shouldAddInStockField($data)) {
            $newElement["in_stock"] = true;
        }

        if($this->updateProductSimple($newElement)) {
            return true;
        }

        return $this->updateProductVariant($newElement);
    }


    public function productUpdateOrCreate($element)
    {
        if ($this->updateProduct($element)) {
            return true;
        }

        return $this->createProduct($element);
    }

    public function product_child_item()
    {
        return $this->setUriPart1('product.child_item');
    }

    public function variant()
    {
        return $this->setUriPart1('product.variant');
    }

    public function customers()
    {
        return $this->setUriPart1('customer');
    }
    public function info($id)
    {
        $this->params['id'] = $id;

        return $this->setUriPart2('info');
    }

    public function list()
    {
        return $this->setUriPart2('list');
    }

    public function add($data)
    {
        return $this->setUriPart2('add')->post($data);
    }

    public function update($data)
    {
        return $this->setUriPart2('update')->post($data);
    }

    public function store_assign($item_id, $store_id) {
        return $this->setUri('product.store.assign.json')->post([
            "product_id" => $item_id,
            "store_id" => $store_id
        ]);
    }

    public function addOrUpdate($data)
    {
        if ((!isset($data['id'])) or ($data['id']==0)) {
            return $this->add($data);
        } else {
            return $this->update($data);
        }
    }


    /**
     * @param $count integer
     * @return Api2Cart
     */
    public function setCount($count)
    {
        $this->count = $count;
        $this->params['count'] = $this->count;

        return $this;
    }

    /**
     * @param date
     * @return Api2Cart
     */
    public function setParam($key, $value)
    {
        $this->params[$key] = $value;

        return $this;
    }

    /**
     * @param date
     * @return Api2Cart
     */
    public function created_from($date)
    {
        return $this->setParam('created_from', $date);
    }

    /**
     * @param date
     * @return Api2Cart
     */
    public function modified_from($date)
    {
        return $this->setParam('modified_from', $date);
    }

    /**
     * @param $field_names string
     * @return Api2Cart
     */
    public function show($field_names)
    {
        return $this->setParam('params', $field_names);
    }


    public function sort_by($field_names, $desc = false)
    {
        $this->params['sort_by'] = $field_names;

        $this->params['sort_direction'] = 'asc';

        if($desc) {
            $this->params['sort_direction'] = 'desc';
        }

        return $this;
    }

    public function find_params_whole_words() {
        $this->params['find_params'] = 'whole_words';
    }

    public function find($field, $value)
    {
        $this->setUriPart2('find');

        $this->params['find_where'] = $field;
        $this->params['find_value'] = $value;

        $this->show('id,sku')->find_params_whole_words();

        return $this->get();
    }

    /**
     * @return integer
     */
    public function findID($field, $value)
    {
        $resultArray = $this->product()->find($field, $value);

        if ($this->return_code()>0) {
            return null;
        }

        return $resultArray['result']['product'][0]['id'];
    }

    /**
     * @return integer
     */
    public function findVariantID($field, $value)
    {
        $resultArray = $this->product_child_item()->find($field, $value);

        if ($this->return_code()>0) {
            return null;
        }
        if ( ! $resultArray['result']['children'][0] == $value ) {
            return null;
        }

        return $resultArray['result']['children'][0]['id'];

    }

    public function addApiKeysToParams()
    {
        $this->params['api_key'] = $this->api_key;
        $this->params['store_key'] = $this->store_key;
    }

    public function logApiCallDetails($call = "GET")
    {
        $query_params = $this->getParams();
        $query_params['api_key'] = $this->ccMasking($query_params['api_key']);
        $query_params['store_key'] = $this->ccMasking($query_params['store_key']);

        Log::info("api2cart $call", ["uri" => $this->uri, "query" => $query_params]);
    }

    public function logPOSTCallDetails($data)
    {
        $query_params = $this->getParams();
        $query_params['api_key'] = $this->ccMasking($query_params['api_key']);
        $query_params['store_key'] = $this->ccMasking($query_params['store_key']);

        Log::info("api2cart POST", ["uri" => $this->uri, "query" => $query_params, "json" => $data]);
    }

    private function updateProductVariant($element)
    {
        $newElement["id"] = $this->findVariantID("sku", $element["sku"]);

        if(isset($newElement["id"]) == false) {
            return false;
        }

        info("Updating variant", ["sku" => $element["sku"]]);

        $newElement = $this->array_add_if_exists($element, $newElement, "store_id");
        $newElement = $this->array_add_if_exists($element, $newElement, "price");
        $newElement = $this->array_add_if_exists($element, $newElement, "quantity");
        $newElement = $this->array_add_if_exists($element, $newElement, "special_price");
        $newElement = $this->array_add_if_exists($element, $newElement, "sprice_create");
        $newElement = $this->array_add_if_exists($element, $newElement, "sprice_expire");
        $newElement = $this->array_add_if_exists($element, $newElement, "in_stock");

        if($this->variant()->update($newElement)) {
            return true;
        };

        // handle exceptions
        switch ($this->return_code()) {

            case 109: // store X not assigned
                if($this->store_assign($newElement["id"], $newElement["store_id"])) {
                    return $this->updateProductVariant($element);
                }
                $this->tryToHandleLastResponseOrException();
                break;

            default:
                $this->tryToHandleLastResponseOrException();
                break;
        }

        return false;

    }

    /**
     * @param $array_source
     * @param $array_destination
     * @param $key
     * @param null $new_key_name
     * @return array
     */
    public function array_add_if_exists($array_source, $array_destination, $key, $new_key_name = null)
    {
        if (isset($new_key_name) == false) {
            $new_key_name = $key;
        }

        if (array_has($array_source, $key)) {
            return array_add($array_destination, $new_key_name, $array_source[$key]);
        }

        return $array_destination;
    }

    public function getProduct($sku, $store_id = null)
    {
        $product_id = $this->findID("model", $sku);

        if($product_id) {

            $this->setUri("product.info.json");
            $this->setParam("id", $product_id);
            $this->setParam("params", "force_all");

            if($store_id) {
                $this->setParam("store_id", $store_id);
            }

            return $this->get()["result"];
        }

        $variant_id = $this->findVariantID("sku", $sku);

        if($variant_id) {

            $this->setUri("product.variant.info.json");
            $this->setParam("id", $variant_id);
            $this->setParam("params", "force_all");

            if($store_id) {
                $this->setParam("store_id", $store_id);
            }

            return $this->get()["result"]["variant"];

        }

    }

    public function createProduct($element)
    {
        Log::info("Creating product", ["sku" => $element["sku"]]);

        $newProduct = [
            "sku"                => $element["sku"],
            "model"              => $element["sku"],
            "available_for_view" => false, // disable new products
	        "available_for_sale" => false, // disable new products
            "stores_ids"         => "1,2"
        ];

        $newProduct = $this->array_add_if_exists($element, $newProduct, "name");
        $newProduct = $this->array_add_if_exists($element, $newProduct, "name", "description");
        $newProduct = $this->array_add_if_exists($element, $newProduct, "price");

        if ($this->product()->add($newProduct)) {
            return $this->updateProduct($element);
        };

        // handle exceptions
        switch ($this->return_code()) {

//            case 109: // store X not assigned
//                return $this->assignStoreAndUpdate($data);
//                break;

            default:
                $this->tryToHandleLastResponseOrException();
                break;
        }
    }

    /**
     * @param $extraInfo
     */
    public function handleResponse()
    {
        $this->resetParams();

        $this->lastResponse_body = $this->lastResponse->getBody()->getContents();

        $extraInfo["HTTP Code"] = $this->lastResponse->getStatusCode();

        if ($this->lastResponse->getStatusCode() == 200) {
            $extraInfo["return_code"] = $this->return_code();
            $extraInfo["return_message"] = $this->return_message();
        }

        if (($this->lastResponse->getStatusCode() == 200) AND ($this->return_code() == 0)) {
            Log::info('api2cart RESPONSE', $extraInfo);
            return ;
        }

        Log::warning('api2cart RESPONSE', $extraInfo);

    }

    /**
     * @param $product
     * @return bool
     */
    private function shouldAddInStockField($product)
    {
        return (array_has($product, "quantity") && ($product["quantity"] > 0));
    }
}

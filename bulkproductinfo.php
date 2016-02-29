<?php

require_once __DIR__.DS.'polyfill.php';

/**
 * Edit products' info in one place
 */
class BulkProductInfo extends Module
{
    /**
     * @constructor
     */
    public function __construct() {
        $this->name = 'bulkproductinfo';
        $this->tab = 'administration';
        $this->version = '1.0.1';
        $this->author = 'Simone Salerno';

        parent::__construct();

        $this->displayName = $this->l('Bulk product info');
        $this->description = $this->l('Edit products\' info in one place');
    }
    
    /**
     * Show form
     * @return string
     */
    public function getContent()
    {
        if (Tools::isSubmit('bulkproductinfo_submit')) {
            $submit = Tools::getValue('bulkproductinfo_data');
            $update = $this->updateProductInfo(json_decode($submit));
        }
        
        Tools::addCSS(__DIR__.DS.'handsontable.full.css');
        Tools::addJS(__DIR__.DS.'handsontable.full.js');
        
        $productsinfo = $this->castIntToBool(
            $this->getBulkProductsInfo(),
            [
                'available_for_order',
                'indexed',
                'active',
                'show_price',
                'advanced_stock_management'
            ]
        );
        $this->context->smarty->assign('bulkproductsinfo', $productsinfo);
        
        return (isset($update) ? $update : '') . $this->display(__FILE__, 'bulkproductinfo.tpl');
    }
    
    /**
     * Get products info
     * @return array
     */
    private function getBulkProductsInfo()
    {
        $query = (new DbQuery)
                ->select('ps.id_product, pl.name')
                ->select('p.reference, p.ean13, p.upc, ps.minimal_quantity, ps.active')
                ->select('ps.price, ps.wholesale_price, ps.unity, ps.unit_price_ratio')
                ->select('p.width, p.height, p.depth, p.weight')
                ->select('ps.available_for_order, ps.show_price, ps.`condition`, ps.visibility, ps.indexed')
                ->select('ps.advanced_stock_management, ps.available_date')
                ->from('product_shop', 'ps')
                ->innerJoin('product', 'p', 'p.id_product = ps.id_product')
                ->innerJoin('product_lang', 'pl', 'pl.id_product = p.id_product')
                ->where(sprintf(
                        'pl.id_lang = %d AND pl.id_shop = %d', 
                        (int) $this->context->language->id,
                        (int) $this->context->shop->id
                ))
                ->orderBy('name');
        
        return Db::getInstance()->executeS($query);
    }
    
    /**
     * Update product info
     * @param array $records
     * @return string
     */
    private function updateProductInfo(stdclass $records)
    {
        $language = $this->context->language;
        $shop = $this->context->shop;
        $response = [];
        
        foreach ((array) $records as $record) {
            $message = $this->displayConfirmation(sprintf("Updated %s", $record->name));
            
            try {
                $product = new Product($record->id_product, false, $language->id, $shop->id);
                $this->fill($product, (array) $record);
                $product->save();
            }
            catch (Exception $ex) {
                $message = $this->displayError(sprintf("Error on %s: [%s]", $record->name, $ex->getMessage()));
            }
            
            array_push($response, $message);
        }
        
        return implode('', $response);
    }
    
    /**
     * Copy data from submit form to product object
     * @param Product $product
     * @param array $data
     */
    private function fill(Product $product, array $data)
    {
        foreach ($data as $key => $value) {
            if (property_exists($product, $key))
                $product->{$key} = $value;
        }
    }
    
    /**
     * Cast 0/1 attributes to true/false
     * @param array $records
     * @param array $attributes
     * @return array
     */
    private function castIntToBool($records, $attributes)
    {
        return array_map(function($record) use ($attributes) {
            foreach ($attributes as $attribute) {
                if (array_key_exists($attribute, $record)) {
                    $record[$attribute] = (bool) $record[$attribute];
                }
            }
                
            return $record;
        }, $records);
    }
}
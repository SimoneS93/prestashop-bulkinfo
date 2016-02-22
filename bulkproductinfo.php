<?php

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
        $this->version = '1.0';
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
        if (Tools::isSubmit('bulkproductinfo_submit'))
            $update = $this->updateProductInfo(json_decode(Tools::getValue('bulkproductinfo_data'), true));
        
        Tools::addCSS(__DIR__.DS.'handsontable.full.css');
        Tools::addJS(__DIR__.DS.'handsontable.full.js');
        
        $productsinfo = $this->getBulkProductsInfo();
        $this->temp($productsinfo);
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
                ->select('p.id_product, pl.name')
                ->select('reference, ean13, upc, minimal_quantity, active')
                ->select('price, wholesale_price, unity, unit_price_ratio')
                ->select('width, height, depth, weight')
                ->select('available_for_order, show_price, `condition`, visibility')
                ->from('product', 'p')
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
     * @param array $productinfo
     * @return string
     */
    private function updateProductInfo(array $productinfo)
    {
        $response = '';
        $temp = $this->temp();
        //index $temp by id_product, for easy lookup
        $keys = array_column($temp, 'id_product');
        $temp = array_combine($keys, $temp);
        
        //only update changed products
        foreach ($productinfo as $product) {
            $id_product = $product['id_product'];
            
            if ($this->diff($product, $temp[$id_product])) {
                try {
                    $product_object = new Product($id_product, false, $this->context->language->id);
                    $this->copy($product_object, $product);
                    $product_object->save();
                    $response .= $this->displayConfirmation(sprintf('Updated %s', $product_object->name));
                }
                catch (Exception $ex) {
                    $response .= $this->displayError(sprintf('Error on %s: %s', $product_object->name, $ex->getMessage()));
                }
            }
        }
        
        return $response;
    }
    
    /**
     * Check if two arrays differ in values
     * @param array $a
     * @param array $b
     */
    private function diff(array $a, array $b)
    {
        sort($a);
        sort($b);
        
        return array_diff($a, $b);
    }
    
    /**
     * Copy data from submit form to product object
     * @param Product $product
     * @param array $data
     */
    private function copy(Product $product, array $data)
    {
        foreach ($data as $key => $value) {
            if (property_exists($product, $key))
                $product->{$key} = $value;
        }
    }
    
    /**
     * Get / set temp data for incremental update
     * @param array|null $data
     * @return array|null
     */
    private function temp($data = null)
    {
        $filename = __DIR__.DS.'temp.json';
        
        if ($data !== null)
            file_put_contents($filename, json_encode($data));
        else
            return json_decode(file_get_contents($filename), true);
    }
}
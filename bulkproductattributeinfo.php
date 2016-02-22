<?php

/**
 * Edit products' info in one place
 */
class BulkProductAttributeInfo extends Module
{
    /**
     * @constructor
     */
    public function __construct() {
        $this->name = 'bulkproductattributeinfo';
        $this->tab = 'administration';
        $this->version = '1.0';
        $this->author = 'Simone Salerno';

        parent::__construct();

        $this->displayName = $this->l('Bulk product attribute info');
        $this->description = $this->l('Edit products\' attributes info in one place');
    }
    
    /**
     * Show form
     * @return string
     */
    public function getContent()
    {
        if (Tools::isSubmit('bulkproductattributeinfo_submit')) {
            $post = json_decode(Tools::getValue('bulkproductattributeinfo_data'), true);
            $update = $this->updateProductAttributeInfo($post);
        }
        
        Tools::addCSS(__DIR__.DS.'handsontable.full.css');
        Tools::addJS(__DIR__.DS.'handsontable.full.js');
        
        $combinations_info = $this->getBulkProductAttributesInfo();
        $this->temp($combinations_info);
        $this->context->smarty->assign('bulkproductattributesinfo', $combinations_info);
        
        return (isset($update) ? $update : '') . $this->display(__FILE__, 'bulkproductattributeinfo.tpl');
    }
    
    /**
     * Get products info
     * @return array
     */
    private function getBulkProductAttributesInfo()
    {
        $query = (new DbQuery)
                ->select('pa.id_product_attribute, pa.id_product, pl.name AS product_name')
                ->select('GROUP_CONCAT(CONCAT(agl.name, " ", al.name), " - ") AS combination_name')
                ->select('pa.reference, pa.ean13, pa.upc, pas.minimal_quantity')
                ->select('pas.price, pas.wholesale_price, pas.ecotax, pas.unit_price_impact')
                ->select('pas.weight, pas.default_on, pas.available_date')
                ->from('product_attribute_shop', 'pas')
                ->innerJoin('product_attribute', 'pa', 'pa.id_product_attribute = pas.id_product_attribute')
                ->innerJoin('product', 'p', 'p.id_product = pa.id_product')
                ->innerJoin('product_lang', 'pl', 'pl.id_product = p.id_product AND pl.id_shop = pas.id_shop')
                ->innerJoin('product_attribute_combination', 'pac', 'pac.id_product_attribute = pa.id_product_attribute')
                ->innerJoin('attribute', 'a', 'a.id_attribute = pac.id_attribute')
                ->innerJoin('attribute_lang', 'al', 'al.id_attribute = a.id_attribute')
                ->innerJoin('attribute_group', 'ag', 'ag.id_attribute_group = a.id_attribute_group')
                ->innerJoin('attribute_group_lang', 'agl', 'agl.id_attribute_group = ag.id_attribute_group')
                ->where(sprintf(
                        'pl.id_lang = %1$d AND agl.id_lang = %1$s AND al.id_lang = %1$s AND pas.id_shop = %2$d', 
                        (int) $this->context->language->id,
                        (int) $this->context->shop->id
                ))
                ->groupBy('pa.id_product_attribute')
                ->orderBy('product_name, combination_name');
        $rows = Db::getInstance()->executeS($query);
        
        return array_map(function($row) {
            $row['name'] = sprintf('%s :: %s', $row['product_name'], preg_replace('/ - $/', '', $row['combination_name']));
            unset($row['product_name']);
            unset($row['combination_name']);
            return $row;
        }, $rows);
    }
    
    /**
     * Update product info
     * @param array $combinations_info
     * @return string
     */
    private function updateProductAttributeInfo(array $combinations_info)
    {
        $response = '';
        $temp = $this->temp();
        //index $temp by id_product, for easy lookup
        $keys = array_column($temp, 'id_product_attribute');
        $temp = array_combine($keys, $temp);
        
        //only update changed products
        foreach ($combinations_info as $combination) {
            $id_combination = $combination['id_product_attribute'];
            
            if ($this->diff($combination, $temp[$id_combination])) {
                try {
                    $combination_object = new Combination($id_combination);
                    $this->copy($combination_object, $combination);
                    $combination_object->save();
                    $response .= $this->displayConfirmation(sprintf('Updated %s', $combination['name']));
                }
                catch (Exception $ex) {
                    $response .= $this->displayError(sprintf('Error on %s: %s', $combination['name'], $ex->getMessage()));
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
     * Copy data from submit form to combiantion object
     * @param Combination $combination
     * @param array $data
     */
    private function copy(Combination $combination, array $data)
    {
        foreach ($data as $key => $value) {
            if (property_exists($combination, $key))
                $combination->{$key} = $value;
        }
        
        //format date
        if (isset($data['available_date'])) {
            $time = str_replace('/', '-', $data['available_date']);
            $date = date('Y-m-d H:i:s', strtotime($time));
            $combination->available_date = $date;
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
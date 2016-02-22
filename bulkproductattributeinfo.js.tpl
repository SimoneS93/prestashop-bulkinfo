var data = {$bulkproductattributesinfo|json_encode};
var hotElement = document.getElementById('hot');
var hot = new Handsontable(hotElement, {
    data: data,
    columns: [
        {
            data: "id_product",
            type: "numeric",
            width: 40
        },
        {
            data: "id_product_attribute",
            type: "numeric",
            width: 40
        },
        {
            data: "name",
            type: "text"
        },
        {
            data: "reference",
            type: "text"
        },
        {
            data: "upc",
            type: "text"
        },
        {
            data: "ean13",
            type: "text"
        },
        {
            data: "minimal_quantity",
            type: "numeric",
            format: "0"
        },
        {
            data: "wholesale_price",
            type: "numeric",
            format: "0.000"
        },
        {
            data: "price",
            type: "numeric",
            format: "0.000"
        },
        {
            data: "unit_price_impact",
            type: "numeric",
            format: "0.000"
        },
        {
            data: "ecotax",
            type: "numeric",
            format: "0.000"
        },
        {
            data: "weight",
            type: "numeric",
            format: "0.000"
        },
        {
            data: "default_on",
            type: "numeric",
            format: "0"
        },
        {
            data: "available_date",
            type: "date",
            format: "DD/MM/YY"
        }
    ],
    stretchH: "all",
    width: '100%',
    height: 700,
    autoWrapRow: true,
    maxRows: 10000,
	columnSorting: true,
	sortIndicator: true,
	autoColumnSize: {    
	    "samplingRatio": 23
	},
	rowHeaders: true,
	colHeaders: [
            "#",
	    "##",
	    "{l s='Product'}",
            "{l s='Reference'}",
	    "{l s='UPC'}",
	    "{l s='EAN13'}",
	    "{l s='Minimal qty'}",
            "{l s='Wholesale price'}",
            "{l s='Price tax excl'}",
            "{l s='Unit price impact'}",
            "{l s='Ecotax'}",
            "{l s='Weight'}",
            "{l s='Default on'}",
            "{l s='Available date'}"
	],
	manualRowResize: true,
	manualColumnResize: true,
});


/**
 * Save updated values
 */
 $('form.bulkproductattributeinfo').on('submit', function() {
 	$(this).find('[name=bulkproductattributeinfo_data]').val(JSON.stringify(data));
 });
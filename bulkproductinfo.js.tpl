var data = {$bulkproductsinfo|json_encode};
var changed = { };
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
            data: "unity",
            type: "text"
        },
        {
            data: "unit_price_ratio",
            type: "numeric",
            format: "0.000"
        },
        {
            data: "width",
            type: "numeric",
            format: "0.000"
        },
        {
            data: "height",
            type: "numeric",
            format: "0.000"
        },
        {
            data: "depth",
            type: "numeric",
            format: "0.000"
        },
        {
            data: "weight",
            type: "numeric",
            format: "0.000"
        },
        {
            data: "available_for_order",
            type: "checkbox"
        },
        {
            data: "show_price",
            type: "checkbox"
        },
        {
            data: "active",
            type: "checkbox"
        },
        {
            data: "indexed",
            type: "checkbox"
        },
        {
            data: "advanced_stock_management",
            type: "checkbox"
        },
        {
            data: "visibility",
            type: "dropdown",
            source: ["both", "catalog", "search", "none"]
        },
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
        "{l s='Product'}",
        "{l s='Reference'}",
        "{l s='UPC'}",
        "{l s='EAN13'}",
        "{l s='Minimal qty'}",
        "{l s='Wholesale price'}",
        "{l s='Price'}",
        "{l s='Unity'}",
        "{l s='Unit price ratio'}",  
        "{l s='Width'}",
        "{l s='Height'}",
        "{l s='Depth'}",
        "{l s='Weight'}",
        "{l s='Available for order'}",
        "{l s='Show price'}",
        "{l s='Active'}",
        "{l s='Indexed'}",
        "{l s='Adv stock'}",
        "{l s='Visibility'}"
    ],
    manualRowResize: true,
    manualColumnResize: true,
});


/**
 * Only submit changed data
 */

hot.addHook('afterChange', function(changes) {
    //changes[0] is an array of [row index, attribute name, old value, new value]
    //get the row index and add the row to the changed array
    var row = data[changes[0][0]],
        index = row.id_product;
    changed[index] = row;
});

$('form.bulkproductinfo').on('submit', function() {
    $(this).find('[name=bulkproductinfo_data]').val(JSON.stringify(changed));
});